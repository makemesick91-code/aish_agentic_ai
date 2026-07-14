<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Audit\AuditRecorder;
use App\Enums\InvitationStatus;
use App\Enums\ResponseStatus;
use App\Models\SurveyAnswer;
use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Surveys\Exceptions\InvalidSurveyLinkException;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantScope;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The ONLY entry point for the public, unauthenticated survey plane. It resolves a campaign or
 * invitation cross-tenant via the allowlisted TenantScope bypass (opaque public id + constant-
 * time token comparison, no enumeration), then performs all data work under a tenant-only
 * context (no membership, no RBAC, no platform access). Responses are one-time per invitation,
 * transactional, and metered/audited without leaking answer content (rule 32; Step 7 §17-§19).
 */
final class PublicSurveyGateway
{
    public function __construct(
        private readonly ResponseValidator $validator,
        private readonly UsageMeter $usage,
        private readonly AuditRecorder $audit,
        private readonly NotificationDispatcher $dispatcher,
        private readonly SurveyEntitlements $entitlements,
    ) {}

    /** Resolve a public campaign link for rendering (throws generically on any failure). */
    public function campaignView(string $publicId): PublicSurveyView
    {
        $campaign = $this->resolveCampaign($publicId);

        return $this->runWithTenant($campaign->tenant_id, function () use ($campaign): PublicSurveyView {
            $version = SurveyVersion::findOrFail($campaign->survey_version_id);
            $version->load('questions.options');

            return new PublicSurveyView($campaign, $version, null);
        });
    }

    /** Resolve a unique invitation link for rendering, marking it opened. */
    public function invitationView(string $publicId, string $token): PublicSurveyView
    {
        $invitation = $this->resolveInvitation($publicId, $token);
        $campaign = $this->resolveCampaign(null, $invitation);

        return $this->runWithTenant($campaign->tenant_id, function () use ($campaign, $invitation): PublicSurveyView {
            $scoped = SurveyInvitation::findOrFail($invitation->id);
            if (in_array($scoped->status, [InvitationStatus::Created, InvitationStatus::Sent, InvitationStatus::DeliveryFailed], true)) {
                $scoped->forceFill(['status' => InvitationStatus::Opened, 'opened_at' => now()])->save();
            }

            $version = SurveyVersion::findOrFail($campaign->survey_version_id);
            $version->load('questions.options');

            return new PublicSurveyView($campaign, $version, $scoped);
        });
    }

    /**
     * Submit an anonymous response for a public campaign link.
     *
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $meta
     */
    public function submitForCampaign(string $publicId, array $answers, array $meta = []): SurveyResponse
    {
        $campaign = $this->resolveCampaign($publicId);

        return $this->runWithTenant($campaign->tenant_id, fn (Tenant $tenant): SurveyResponse => $this->persist($tenant, $campaign, null, $answers, $meta));
    }

    /**
     * Submit a response for a unique invitation link (one-time).
     *
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $meta
     */
    public function submitForInvitation(string $publicId, string $token, array $answers, array $meta = []): SurveyResponse
    {
        $invitation = $this->resolveInvitation($publicId, $token);
        $campaign = $this->resolveCampaign(null, $invitation);

        return $this->runWithTenant($campaign->tenant_id, fn (Tenant $tenant): SurveyResponse => $this->persist($tenant, $campaign, $invitation, $answers, $meta));
    }

    /** Resolve an active campaign by opaque public id, or (when $invitation given) by its campaign. */
    private function resolveCampaign(?string $publicId, ?SurveyInvitation $invitation = null): SurveyCampaign
    {
        $query = SurveyCampaign::withoutGlobalScope(TenantScope::class);
        $campaign = $invitation !== null
            ? $query->whereKey($invitation->campaign_id)->first()
            : $query->where('public_id', $publicId)->first();

        if ($campaign === null || ! $campaign->status->acceptsResponses()) {
            throw InvalidSurveyLinkException::generic();
        }

        return $campaign;
    }

    /** Resolve a usable invitation by opaque public id + constant-time token comparison. */
    private function resolveInvitation(string $publicId, string $token): SurveyInvitation
    {
        $invitation = SurveyInvitation::withoutGlobalScope(TenantScope::class)
            ->where('public_id', $publicId)
            ->first();

        // Constant-time comparison; a missing invitation still runs a comparison shape via the
        // generic failure below (no timing/enumeration oracle on tenant existence).
        if ($invitation === null || ! hash_equals($invitation->token_hash, hash('sha256', $token))) {
            throw InvalidSurveyLinkException::generic();
        }

        if (! $invitation->isUsable()) {
            throw InvalidSurveyLinkException::generic();
        }

        return $invitation;
    }

    /**
     * @param  array<string, mixed>  $answers
     * @param  array<string, mixed>  $meta
     */
    private function persist(Tenant $tenant, SurveyCampaign $campaign, ?SurveyInvitation $invitation, array $answers, array $meta): SurveyResponse
    {
        // Fail closed on the monthly response entitlement before doing any work.
        $this->entitlements->assertCanAcceptResponse($tenant);

        $version = SurveyVersion::findOrFail($campaign->survey_version_id);
        $specs = $this->validator->validate($version, $answers); // throws ResponseValidationException

        $response = DB::transaction(function () use ($tenant, $campaign, $invitation, $version, $specs, $meta): SurveyResponse {
            try {
                $response = SurveyResponse::create([
                    'branch_id' => $campaign->branch_id,
                    'survey_id' => $campaign->survey_id,
                    'survey_version_id' => $version->id,
                    'campaign_id' => $campaign->id,
                    'invitation_id' => $invitation?->id,
                    'mode' => $campaign->mode->value,
                    'status' => ResponseStatus::Completed,
                    'locale' => $version->locale,
                    'started_at' => now(),
                    'submitted_at' => now(),
                    'metadata' => $this->minimizeMeta($meta),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Partial unique index: a completed response already exists for this invitation.
                throw InvalidSurveyLinkException::alreadyCompleted();
            }

            foreach ($specs as $spec) {
                SurveyAnswer::create(['response_id' => $response->id, ...$spec]);
            }

            if ($invitation !== null) {
                SurveyInvitation::whereKey($invitation->id)->first()?->forceFill([
                    'status' => InvitationStatus::Completed,
                    'completed_at' => now(),
                ])->save();
            }

            // Idempotent usage increment keyed by the unique response correlation id.
            $this->usage->record(
                $tenant,
                MeterKeys::SURVEY_RESPONSES_COMPLETED,
                1,
                'resp:'.$response->correlation_id,
                actorId: null,
            );

            // Audit records ids/state only — never answer content (rule 32).
            $this->audit->record('survey.response.completed', [
                'tenant_id' => $tenant->id,
                'branch_id' => $campaign->branch_id,
                'subject' => $response,
                'actor_id' => null,
                'channel' => 'public',
                'metadata' => [
                    'correlation_id' => $response->correlation_id,
                    'version_number' => $version->version_number,
                    'invited' => $invitation !== null,
                ],
            ]);

            return $response;
        });

        $this->notifyInternal($tenant, $campaign, $response);

        return $response->fresh();
    }

    /**
     * Best-effort internal in-app notification to the campaign owner that a response completed.
     * It carries no answer content and never fails the public submission (rule 32; Step 7 §22).
     */
    private function notifyInternal(Tenant $tenant, SurveyCampaign $campaign, SurveyResponse $response): void
    {
        if ($campaign->created_by === null) {
            return;
        }

        $recipient = User::find($campaign->created_by);
        if ($recipient === null) {
            return;
        }

        try {
            $this->dispatcher->dispatch(
                NotificationType::SurveyResponseCompleted,
                $recipient,
                'survey-response:'.$response->correlation_id,
                'A survey response was completed',
                tenant: $tenant,
                body: 'A response was submitted for the campaign "'.$campaign->name.'".',
                branchId: $campaign->branch_id,
            );
        } catch (\Throwable) {
            // An internal notification failure must never fail a public response.
        }
    }

    /**
     * Keep only a minimized allowlist of request metadata — never PII, answer content, or
     * tokens.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function minimizeMeta(array $meta): array
    {
        return array_filter([
            'ip_hash' => isset($meta['ip_hash']) ? (string) $meta['ip_hash'] : null,
            'user_agent_hash' => isset($meta['user_agent_hash']) ? (string) $meta['user_agent_hash'] : null,
        ], fn ($v) => $v !== null);
    }

    private function runWithTenant(int $tenantId, Closure $callback): mixed
    {
        $tenant = Tenant::findOrFail($tenantId);
        $context = app(TenantContext::class);
        $context->forget();
        $context->establish($tenant);

        try {
            return $callback($tenant);
        } finally {
            $context->forget();
        }
    }
}
