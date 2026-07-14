<?php

declare(strict_types=1);

namespace App\Feedback\Search;

use App\Authorization\Permissions;
use App\Feedback\Support\FeedbackBranchScope;
use App\Models\FeedbackItem;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Tenant- and branch-scoped feedback search. Tenant scoping is automatic (global scope). Branch
 * scoping is applied from the acting membership so a branch-restricted user never sees another
 * branch's items. Search is permission-aware: response free-text (`search_content`) is only matched
 * for actors with the content-view permission, and results never expose highlights/snippets that
 * could leak hidden content. Only allowlisted filters and sorts reach the query (rule 33; Step 8 §16).
 */
final class FeedbackSearchService
{
    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return LengthAwarePaginator<int, FeedbackItem>
     */
    public function search(FeedbackSearchCriteria $criteria, User $actor): LengthAwarePaginator
    {
        $query = FeedbackItem::query()->with(['assignee', 'survey', 'campaign', 'branch']);

        $this->applyBranchScope($query);
        $this->applyFilters($query, $criteria);
        $this->applySearch($query, $criteria, $actor);
        $this->applySort($query, $criteria);

        return $query->paginate($criteria->normalizedPerPage())->withQueryString();
    }

    /**
     * @param  Builder<FeedbackItem>  $query
     */
    private function applyBranchScope(Builder $query): void
    {
        FeedbackBranchScope::apply($query, $this->context);
    }

    /**
     * @param  Builder<FeedbackItem>  $query
     */
    private function applyFilters(Builder $query, FeedbackSearchCriteria $criteria): void
    {
        if ($criteria->statuses !== []) {
            $query->whereIn('status', $criteria->statuses);
        }
        if ($criteria->branchId !== null) {
            $query->where('branch_id', $criteria->branchId);
        }
        if ($criteria->surveyId !== null) {
            $query->where('survey_id', $criteria->surveyId);
        }
        if ($criteria->campaignId !== null) {
            $query->where('campaign_id', $criteria->campaignId);
        }
        if ($criteria->surveyVersionId !== null) {
            $query->where('survey_version_id', $criteria->surveyVersionId);
        }
        if ($criteria->assigneeId !== null) {
            $query->where('current_assignee_id', $criteria->assigneeId);
        }
        if ($criteria->tagId !== null) {
            $tagId = $criteria->tagId;
            $query->whereHas('tags', fn (Builder $tags) => $tags->where('feedback_tags.id', $tagId));
        }
        if ($criteria->dateFrom !== null) {
            $query->where('created_at', '>=', $criteria->dateFrom);
        }
        if ($criteria->dateTo !== null) {
            $query->where('created_at', '<=', $criteria->dateTo);
        }
        $metric = $criteria->normalizedMetric();
        if ($metric !== null && $criteria->metricValue !== null) {
            $query->where('metric_snapshot->'.$metric, $criteria->metricValue);
        }
    }

    /**
     * @param  Builder<FeedbackItem>  $query
     */
    private function applySearch(Builder $query, FeedbackSearchCriteria $criteria, User $actor): void
    {
        $term = $criteria->normalizedQuery();
        if ($term === null) {
            return;
        }

        $includeContent = $actor->can(Permissions::FEEDBACK_VIEW_CONTENT);
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        $query->where(function (Builder $search) use ($term, $includeContent, $isPostgres): void {
            if ($isPostgres) {
                $search->whereRaw("search_meta_vector @@ plainto_tsquery('simple', ?)", [$term]);
                if ($includeContent) {
                    $search->orWhereRaw("search_content_vector @@ plainto_tsquery('simple', ?)", [$term]);
                }

                return;
            }

            $like = '%'.$term.'%';
            $search->where('search_meta', 'like', $like);
            if ($includeContent) {
                $search->orWhere('search_content', 'like', $like);
            }
        });
    }

    /**
     * @param  Builder<FeedbackItem>  $query
     */
    private function applySort(Builder $query, FeedbackSearchCriteria $criteria): void
    {
        match ($criteria->normalizedSort()) {
            'oldest' => $query->orderBy('last_activity_at')->orderBy('id'),
            'created' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderByDesc('last_activity_at')->orderByDesc('id'),
        };
    }
}
