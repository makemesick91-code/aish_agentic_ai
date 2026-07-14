<?php

declare(strict_types=1);

namespace App\Feedback\Export;

use App\Models\FeedbackExport;
use App\Models\FeedbackItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Streams a tenant/branch-scoped feedback export to a CSV on a PRIVATE disk. Every cell is guarded
 * against CSV formula injection (dangerous leading characters are prefixed with a quote), the output
 * is UTF-8 (BOM), and rows are written from a streaming cursor with a hard cap so a large export never
 * loads the whole result set into memory. Response free-text is included ONLY when the export was
 * requested with content permission; tokens, storage paths, and attachment binaries are never written
 * (rule 33; Step 8 §18).
 */
final class FeedbackExportWriter
{
    private const MAX_ROWS = 100_000;

    private const HEADERS = [
        'feedback_id', 'created_at', 'branch', 'survey', 'campaign', 'status',
        'assignee', 'tags', 'csat', 'nps', 'ces', 'response_completed_at',
    ];

    public function write(FeedbackExport $export): ExportResult
    {
        /** @var array<string, mixed> $filters */
        $filters = $export->filters ?? [];
        $includeContent = (bool) ($filters['includes_content'] ?? false);

        $query = FeedbackItem::query()->with(['survey', 'campaign', 'branch', 'assignee', 'tags', 'surveyResponse']);
        $this->applyScope($query, is_array($filters['scope'] ?? null) ? $filters['scope'] : []);
        $this->applyUserFilters($query, is_array($filters['user'] ?? null) ? $filters['user'] : []);

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Unable to open export buffer.');
        }

        fwrite($stream, "\xEF\xBB\xBF"); // UTF-8 BOM
        $headers = self::HEADERS;
        if ($includeContent) {
            $headers[] = 'response_text';
        }
        fputcsv($stream, $headers);

        $rows = 0;
        foreach ($query->orderBy('id')->cursor() as $item) {
            if ($rows >= self::MAX_ROWS) {
                break;
            }
            fputcsv($stream, $this->row($item, $includeContent));
            $rows++;
        }

        rewind($stream);
        $path = 'tenants/'.$export->tenant_id.'/feedback-exports/'.$export->ulid.'.csv';
        Storage::disk('local')->put($path, $stream);
        $size = (int) Storage::disk('local')->size($path);
        fclose($stream);

        return new ExportResult($path, $rows, $size);
    }

    /**
     * @param  Builder<FeedbackItem>  $query
     * @param  array<string, mixed>  $scope
     */
    private function applyScope(Builder $query, array $scope): void
    {
        if (($scope['all_branches'] ?? false) === true) {
            return;
        }

        /** @var list<int> $branchIds */
        $branchIds = array_map('intval', is_array($scope['branch_ids'] ?? null) ? $scope['branch_ids'] : []);
        $query->where(function (Builder $scoped) use ($branchIds): void {
            $scoped->whereNull('branch_id');
            if ($branchIds !== []) {
                $scoped->orWhereIn('branch_id', $branchIds);
            }
        });
    }

    /**
     * @param  Builder<FeedbackItem>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyUserFilters(Builder $query, array $filters): void
    {
        $map = [
            'branch_id' => 'branch_id',
            'survey_id' => 'survey_id',
            'campaign_id' => 'campaign_id',
            'survey_version_id' => 'survey_version_id',
            'assignee_id' => 'current_assignee_id',
        ];
        foreach ($map as $key => $column) {
            if (isset($filters[$key]) && is_numeric($filters[$key])) {
                $query->where($column, (int) $filters[$key]);
            }
        }
        if (isset($filters['statuses']) && is_array($filters['statuses'])) {
            $query->whereIn('status', array_map('strval', $filters['statuses']));
        }
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', (string) $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', (string) $filters['date_to']);
        }
    }

    /**
     * @return list<string>
     */
    private function row(FeedbackItem $item, bool $includeContent): array
    {
        /** @var array<string, mixed> $snapshot */
        $snapshot = $item->metric_snapshot ?? [];

        $cells = [
            $item->ulid,
            $item->created_at?->toIso8601String() ?? '',
            $this->attr($item->branch, 'name'),
            $this->attr($item->survey, 'name'),
            $this->attr($item->campaign, 'name'),
            $item->status->value,
            $this->attr($item->assignee, 'name'),
            $item->tags->pluck('slug')->implode('|'),
            (string) ($snapshot['csat'] ?? ''),
            (string) ($snapshot['nps'] ?? ''),
            (string) ($snapshot['ces'] ?? ''),
            $this->isoAttr($item->surveyResponse, 'submitted_at'),
        ];
        if ($includeContent) {
            $cells[] = (string) ($item->search_content ?? '');
        }

        return array_map(fn ($value) => $this->guardCell((string) $value), $cells);
    }

    private function attr(?Model $model, string $key): string
    {
        $value = $model?->getAttribute($key);

        return is_scalar($value) ? (string) $value : '';
    }

    private function isoAttr(?Model $model, string $key): string
    {
        $value = $model?->getAttribute($key);

        return $value instanceof Carbon ? $value->toIso8601String() : '';
    }

    /** Neutralize CSV/spreadsheet formula injection by quoting dangerous leading characters. */
    private function guardCell(string $value): string
    {
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
