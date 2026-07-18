<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Audit\AuditRecorder;
use App\Customers\CustomerFeedbackLinker;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Backfills the Customer 360 link on existing Step 8 feedback items.
 *
 * This is the additive, non-destructive backfill required by ADR 0068: it only ever fills a NULL
 * `customer_id`, never rewrites an existing link, and never alters a Step 8 record's own state.
 * Unlinked items remain perfectly valid — an item with no verifiable identity is genuinely
 * anonymous, not broken (rule 36; contract §6).
 *
 * Safe to rerun: the linker is idempotent, so a second pass is a no-op. Resumable by construction —
 * progress is the data itself (a filled `customer_id`), so an interrupted run simply continues
 * where it left off with no checkpoint table to corrupt.
 */
final class CustomerReconcileCommand extends Command
{
    protected $signature = 'aish:customer-reconcile
        {--tenant= : Limit to a single tenant id}
        {--limit=0 : Max feedback items to process per tenant (0 = all)}
        {--chunk=500 : Rows fetched per batch}
        {--dry-run : Report what would be linked without writing}';

    protected $description = 'Link existing feedback items to their canonical Customer 360 profile where a verified identity exists.';

    public function handle(CustomerFeedbackLinker $linker, TenantContext $context, AuditRecorder $audit): int
    {
        $tenantOption = $this->option('tenant');
        $limit = (int) $this->option('limit');
        $chunk = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $tenants = Tenant::query()
            ->when($tenantOption !== null, fn ($query) => $query->whereKey((int) $tenantOption))
            ->get();

        $linkedTotal = 0;
        $scannedTotal = 0;

        foreach ($tenants as $tenant) {
            $context->forget();
            $context->establish($tenant);

            $linked = 0;
            $scanned = 0;

            try {
                $query = FeedbackItem::query()
                    ->whereNull('customer_id')
                    // Only invitation-sourced items can carry a verified identity; skipping the
                    // rest keeps the scan bounded instead of walking every anonymous response.
                    ->whereNotNull('invitation_id')
                    ->orderBy('id');

                if ($limit > 0) {
                    $query->limit($limit);
                }

                foreach ($query->lazyById($chunk) as $item) {
                    $scanned++;

                    if ($dryRun) {
                        continue;
                    }

                    if ($linker->link($item)) {
                        $linked++;
                    }
                }
            } finally {
                $context->forget();
                Log::flushSharedContext();
            }

            $scannedTotal += $scanned;
            $linkedTotal += $linked;

            if ($scanned > 0) {
                $this->line(sprintf(
                    'Tenant %d: scanned %d candidate item(s), linked %d.',
                    $tenant->id,
                    $scanned,
                    $linked
                ));
            }

            if ($linked > 0) {
                // Re-establish context so the audit row is written under the right tenant.
                $context->establish($tenant);

                try {
                    $audit->record('customer.backfill.reconciled', [
                        'metadata' => [
                            // Counts only — never an identity value or a customer's contact detail.
                            'scanned' => $scanned,
                            'linked' => $linked,
                        ],
                    ]);
                } finally {
                    $context->forget();
                }
            }
        }

        $this->info(sprintf(
            '%sScanned %d candidate item(s) across %d tenant(s); linked %d.',
            $dryRun ? '[dry-run] ' : '',
            $scannedTotal,
            $tenants->count(),
            $linkedTotal
        ));

        return self::SUCCESS;
    }
}
