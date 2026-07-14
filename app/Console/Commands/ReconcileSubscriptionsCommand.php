<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Subscriptions\SubscriptionReconciler;
use Illuminate\Console\Command;

/**
 * Reconciles subscription deadlines (trial/period/grace expiry). Idempotent and safe to rerun
 * (rule 31 §9.8). Scheduled with overlap protection in routes/console.php.
 */
final class ReconcileSubscriptionsCommand extends Command
{
    protected $signature = 'aish:subscription-reconcile';

    protected $description = 'Reconcile subscription trial/period/grace expiry (idempotent).';

    public function handle(SubscriptionReconciler $reconciler): int
    {
        $count = $reconciler->reconcileAll();

        $this->info("Reconciled {$count} subscription(s).");

        return self::SUCCESS;
    }
}
