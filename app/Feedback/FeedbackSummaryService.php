<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Enums\FeedbackStatus;
use App\Feedback\Support\FeedbackBranchScope;
use App\Models\FeedbackItem;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Non-AI operational feedback summaries: simple tenant- and branch-scoped counts. These are NOT
 * analytics or AI insight — no sentiment, severity, root cause, benchmark, or SLA is produced or
 * implied (rule 33; Step 8 §19).
 */
final class FeedbackSummaryService
{
    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $recentSince = now()->subDays(7);

        $byStatus = [];
        foreach (FeedbackStatus::cases() as $status) {
            $byStatus[$status->value] = $this->base()->where('status', $status->value)->count();
        }

        return [
            'total' => $this->base()->count(),
            'by_status' => $byStatus,
            'unassigned' => $this->base()->whereNull('current_assignee_id')->count(),
            'recently_created' => $this->base()->where('created_at', '>=', $recentSince)->count(),
            'recently_resolved' => $this->base()->where('resolved_at', '>=', $recentSince)->count(),
        ];
    }

    /**
     * @return Builder<FeedbackItem>
     */
    private function base(): Builder
    {
        $query = FeedbackItem::query();
        FeedbackBranchScope::apply($query, $this->context);

        return $query;
    }
}
