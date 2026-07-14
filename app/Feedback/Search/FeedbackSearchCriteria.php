<?php

declare(strict_types=1);

namespace App\Feedback\Search;

/**
 * Immutable, allowlisted feedback search/filter criteria. Only these fields can influence the query;
 * free-form column names or sort expressions can never reach the builder (rule 33; Step 8 §16).
 */
final class FeedbackSearchCriteria
{
    public const MAX_QUERY_LENGTH = 100;

    public const ALLOWED_SORT = ['recent', 'oldest', 'created'];

    public const ALLOWED_METRICS = ['csat', 'nps', 'ces'];

    /**
     * @param  list<string>  $statuses
     */
    public function __construct(
        public readonly ?string $query = null,
        public readonly array $statuses = [],
        public readonly ?int $branchId = null,
        public readonly ?int $surveyId = null,
        public readonly ?int $campaignId = null,
        public readonly ?int $surveyVersionId = null,
        public readonly ?int $assigneeId = null,
        public readonly ?int $tagId = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $metric = null,
        public readonly ?int $metricValue = null,
        public readonly string $sort = 'recent',
        public readonly int $perPage = 20,
    ) {}

    public function normalizedQuery(): ?string
    {
        if ($this->query === null) {
            return null;
        }
        $trimmed = mb_substr(trim($this->query), 0, self::MAX_QUERY_LENGTH);

        return $trimmed === '' ? null : $trimmed;
    }

    public function normalizedSort(): string
    {
        return in_array($this->sort, self::ALLOWED_SORT, true) ? $this->sort : 'recent';
    }

    public function normalizedPerPage(): int
    {
        return max(1, min($this->perPage, 100));
    }

    public function normalizedMetric(): ?string
    {
        return in_array($this->metric, self::ALLOWED_METRICS, true) ? $this->metric : null;
    }
}
