<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Audit\AuditRecorder;
use App\Enums\ResponseStatus;
use App\Feedback\FeedbackProjector;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Reconciles missing feedback projections: any completed survey response without a feedback item is
 * projected idempotently. Safe to rerun (the projector is idempotent), tenant-aware, cursor-based,
 * and audited. It never prints response content (rule 33; Step 8 §9.4).
 */
final class FeedbackReconcileCommand extends Command
{
    protected $signature = 'aish:feedback-reconcile {--tenant= : Limit to a single tenant id} {--limit=0 : Max responses to process per tenant (0 = all)}';

    protected $description = 'Project any completed survey responses that are missing an operational feedback item.';

    public function handle(FeedbackProjector $projector, TenantContext $context, AuditRecorder $audit): int
    {
        $tenantOption = $this->option('tenant');
        $limit = (int) $this->option('limit');

        $tenants = Tenant::query()
            ->when($tenantOption !== null, fn ($query) => $query->whereKey((int) $tenantOption))
            ->get();

        $projectedTotal = 0;

        foreach ($tenants as $tenant) {
            $context->forget();
            $context->establish($tenant);

            $projected = 0;
            try {
                $query = SurveyResponse::query()
                    ->where('status', ResponseStatus::Completed)
                    ->whereNotExists(function ($sub): void {
                        $sub->selectRaw('1')
                            ->from('feedback_items')
                            ->whereColumn('feedback_items.survey_response_id', 'survey_responses.id');
                    })
                    ->orderBy('id');

                if ($limit > 0) {
                    $query->limit($limit);
                }

                foreach ($query->cursor() as $response) {
                    $projector->projectFromSurveyResponse($response);
                    $projected++;
                }
            } finally {
                $context->forget();
                Log::flushSharedContext();
            }

            if ($projected > 0) {
                $context->establish($tenant);
                try {
                    $audit->record('feedback.projection.reconciled', [
                        'tenant_id' => $tenant->id,
                        'metadata' => ['projected' => $projected],
                    ]);
                } finally {
                    $context->forget();
                }

                $this->line("Tenant {$tenant->id}: projected {$projected} missing feedback item(s).");
            }

            $projectedTotal += $projected;
        }

        $this->info("Feedback projection reconciliation complete: {$projectedTotal} item(s) projected.");

        return self::SUCCESS;
    }
}
