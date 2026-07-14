<?php

declare(strict_types=1);

namespace App\Feedback\Export;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackExportStatus;
use App\Feedback\FeedbackEntitlements;
use App\Jobs\Feedback\ProcessFeedbackExportJob;
use App\Models\BranchAccessGrant;
use App\Models\FeedbackExport;
use App\Models\User;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Tenancy\TenantContext;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Requests a queued feedback export. Entitlement is enforced (fail-closed) before anything is
 * created. The requester's branch scope and content permission are SNAPSHOTTED into the export so the
 * background job produces exactly what the requester was allowed to see. The request is idempotent per
 * (tenant, requester, filters, content-flag, day): a repeat returns the same export. Only allowlisted
 * filter values are stored (rule 33; Step 8 §18, §22).
 */
final class FeedbackExportService
{
    private const ALLOWED_FILTER_KEYS = [
        'statuses', 'branch_id', 'survey_id', 'campaign_id', 'survey_version_id',
        'assignee_id', 'tag_id', 'date_from', 'date_to', 'metric', 'metric_value',
    ];

    public function __construct(
        private readonly FeedbackEntitlements $entitlements,
        private readonly UsageMeter $usage,
        private readonly AuditRecorder $audit,
        private readonly TenantContext $context,
    ) {}

    /**
     * @param  array<string, mixed>  $userFilters
     */
    public function request(array $userFilters, User $actor, bool $includeContent): FeedbackExport
    {
        $tenant = $this->context->tenant();
        $this->entitlements->assertExportsEnabled($tenant);

        $membership = $this->context->membership();
        $scope = [
            'all_branches' => (bool) $membership->all_branches,
            'branch_ids' => $membership->all_branches ? [] : BranchAccessGrant::query()
                ->where('tenant_membership_id', $membership->id)
                ->pluck('branch_id')
                ->all(),
        ];

        $userFilters = $this->sanitizeFilters($userFilters);
        $filters = [
            'user' => $userFilters,
            'scope' => $scope,
            'includes_content' => $includeContent,
        ];

        $idempotencyKey = 'feedback-export:'.md5((string) json_encode([
            $tenant->id, $actor->id, $includeContent, $userFilters, now()->format('Y-m-d'),
        ]));

        $existing = FeedbackExport::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return $existing;
        }

        try {
            $export = FeedbackExport::create([
                'branch_id' => null,
                'requested_by' => $actor->id,
                'status' => FeedbackExportStatus::Pending,
                'format' => 'csv',
                'includes_content' => $includeContent,
                'filters' => $filters,
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (UniqueConstraintViolationException) {
            return FeedbackExport::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();
        }

        $this->usage->record(
            $tenant,
            MeterKeys::FEEDBACK_EXPORTS_CREATED,
            1,
            'feedback-export:'.$export->ulid,
            actorId: $actor->id,
        );

        $this->audit->record('feedback.export.requested', [
            'tenant_id' => $tenant->id,
            'actor_id' => $actor->id,
            'subject' => $export,
            'metadata' => ['export_ulid' => $export->ulid, 'includes_content' => $includeContent],
        ]);

        ProcessFeedbackExportJob::dispatch($export->id);

        return $export;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function sanitizeFilters(array $filters): array
    {
        $clean = [];
        foreach (self::ALLOWED_FILTER_KEYS as $key) {
            if (! array_key_exists($key, $filters)) {
                continue;
            }
            $value = $filters[$key];
            if (is_array($value)) {
                $clean[$key] = array_values(array_filter($value, 'is_scalar'));
            } elseif (is_scalar($value)) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
