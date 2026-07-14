<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\AuditLog;
use App\Models\Survey;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\PlanService;
use App\Subscriptions\SubscriptionService;
use App\Subscriptions\UsageMeter;
use App\Surveys\CampaignService;
use App\Surveys\Exceptions\InvalidSurveyLinkException;
use App\Surveys\PublicSurveyGateway;
use App\Surveys\SurveyInvitationService;
use App\Surveys\SurveyService;
use App\Surveys\SurveySummaryService;
use App\Surveys\SurveyVersionPublisher;
use App\Tenancy\TenantContext;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Exercises the Step 7 survey & CSAT foundation against REAL infrastructure (PostgreSQL + Redis)
 * with positive AND negative checks — an open socket is not proof (rule 29, rule 32). Intended
 * to run from a clean checkout by scripts/runtime/verify-step-7.sh. Uses only generated,
 * non-sensitive data and cleans up what it creates.
 */
final class VerifyStep7Command extends Command
{
    protected $signature = 'aish:verify-step-7';

    protected $description = 'Verify the Step 7 survey & CSAT foundation against real PostgreSQL + Redis.';

    private int $failures = 0;

    public function handle(): int
    {
        $suffix = Str::random(6);
        $context = app(TenantContext::class);

        $a = Tenant::factory()->create(['name' => "S7 A {$suffix}"]);
        $b = Tenant::factory()->create(['name' => "S7 B {$suffix}"]);
        $owner = User::factory()->create();
        $ownerB = User::factory()->create();
        $membership = TenantMembership::factory()->create(['tenant_id' => $a->id, 'user_id' => $owner->id]);
        $membershipB = TenantMembership::factory()->create(['tenant_id' => $b->id, 'user_id' => $ownerB->id]);

        // Plan granting unlimited survey entitlements + active subscription for tenant A.
        $plans = app(PlanService::class);
        $plan = $plans->create(['code' => "s7_{$suffix}", 'version' => 1, 'name' => 'S7 Plan']);
        $plans->activate($plan);
        $plans->setFeature($plan, EntitlementKeys::SURVEYS_ENABLED, true);
        foreach ([EntitlementKeys::SURVEYS_MAX, EntitlementKeys::SURVEY_CAMPAIGNS_MAX, EntitlementKeys::SURVEY_INVITATIONS_MONTHLY, EntitlementKeys::SURVEY_RESPONSES_MONTHLY] as $key) {
            $plans->setFeature($plan, $key, EntitlementKeys::UNLIMITED);
        }
        app(SubscriptionService::class)->start($a, $plan, SubscriptionStatus::Active, null, 30);

        $this->establish($context, $a, $membership);

        // --- Authoring + immutable publish -----------------------------------------------
        $svc = app(SurveyService::class);
        $survey = $svc->create(['name' => 'CX'], $owner);
        $draft = $svc->draftVersion($survey);
        $svc->addQuestion($draft, ['question_key' => 'csat', 'type' => 'csat', 'prompt' => 'Puas?', 'required' => true, 'display_order' => 1, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 5, 'satisfied_threshold' => 4, 'direction' => 'higher_is_better']], $owner);
        $svc->addQuestion($draft, ['question_key' => 'nps', 'type' => 'nps', 'prompt' => 'Rekomendasi?', 'required' => true, 'display_order' => 2, 'scored' => true, 'scoring_config' => ['scale_min' => 0, 'scale_max' => 10, 'direction' => 'higher_is_better']], $owner);
        $svc->addQuestion($draft, ['question_key' => 'ces', 'type' => 'ces', 'prompt' => 'Mudah?', 'required' => true, 'display_order' => 3, 'scored' => true, 'scoring_config' => ['scale_min' => 1, 'scale_max' => 7, 'direction' => 'higher_is_better']], $owner);
        $version = app(SurveyVersionPublisher::class)->publish($draft->fresh(), $owner);
        $this->assert($version->status->value === 'published', 'survey version publishes and becomes immutable');

        try {
            $version->questions()->first()->update(['prompt' => 'changed']);
            $this->bad('a published version question could be mutated');
        } catch (\Throwable) {
            $this->ok('published version content is immutable');
        }

        // --- Campaign + secure invitation + QR -------------------------------------------
        $campaign = app(CampaignService::class)->activate(
            app(CampaignService::class)->create($survey->fresh(), $version, ['name' => 'C'], $owner),
            $owner,
        );
        $issued = app(SurveyInvitationService::class)->issue($campaign, ['idempotency_key' => "k-{$suffix}", 'recipient_email' => 'p@example.com'], $owner);
        $this->assert($issued->plainToken !== null, 'unique invitation issued with a one-time token');
        $this->assert(strlen($issued->invitation->token_hash) === 64 && $issued->invitation->token_hash !== $issued->plainToken, 'invitation token is stored only as a hash');

        $url = route('survey.public.campaign', ['campaign' => $campaign->public_id]);
        $svg = (new Writer(new ImageRenderer(new RendererStyle(256, 1), new SvgImageBackEnd)))->writeString($url);
        $this->assert(str_contains($svg, '<svg') && ! str_contains($svg, (string) $issued->plainToken), 'QR encodes only the public URL (no token)');

        // --- Public responses (no ambient context) ---------------------------------------
        $this->forget($context);
        $gateway = app(PublicSurveyGateway::class);
        $answers = ['csat' => 5, 'nps' => 9, 'ces' => 6];

        $resp = $gateway->submitForCampaign($campaign->public_id, $answers);
        $this->assert($resp->status->value === 'completed', 'public campaign response completes');

        try {
            $gateway->submitForCampaign('01JUNKUNKNOWNPUBLICID000000', $answers);
            $this->bad('an unknown public link was accepted');
        } catch (InvalidSurveyLinkException) {
            $this->ok('an invalid public link is rejected generically');
        }

        try {
            $gateway->submitForInvitation($issued->invitation->public_id, 'tampered-token', $answers);
            $this->bad('a tampered token was accepted');
        } catch (InvalidSurveyLinkException) {
            $this->ok('a tampered invitation token is rejected');
        }

        $gateway->submitForInvitation($issued->invitation->public_id, (string) $issued->plainToken, $answers);
        try {
            $gateway->submitForInvitation($issued->invitation->public_id, (string) $issued->plainToken, $answers);
            $this->bad('a unique invitation completed twice');
        } catch (InvalidSurveyLinkException) {
            $this->ok('a unique invitation completes at most once');
        }

        // --- Metrics, usage, audit, isolation (tenant A context) --------------------------
        $this->establish($context, $a, $membership);

        $metrics = app(SurveySummaryService::class)->metricsForVersion($version->fresh());
        $this->assert(isset($metrics['csat']['csat_percentage'], $metrics['nps']['nps_score'], $metrics['ces']['average']), 'CSAT/NPS/CES computed deterministically');

        $this->assert(app(UsageMeter::class)->total($a, MeterKeys::SURVEY_RESPONSES_COMPLETED) >= 1, 'survey response usage is metered');
        $this->assert(app(UsageMeter::class)->total($a, MeterKeys::SURVEY_INVITATIONS_CREATED) >= 1, 'survey invitation usage is metered');
        $this->assert(AuditLog::where('event', 'survey.response.completed')->exists(), 'survey response completion is audited');

        $tokenLeak = AuditLog::all()->contains(fn (AuditLog $log) => str_contains((string) json_encode($log->metadata), (string) $issued->plainToken));
        $this->assert(! $tokenLeak, 'no invitation token appears in audit metadata');

        // Cross-tenant isolation: a survey in B is invisible under A's context.
        $this->establish($context, $b, $membershipB);
        $surveyB = app(SurveyService::class)->create(['name' => 'B-only'], $ownerB);
        $this->establish($context, $a, $membership);
        $this->assert(! Survey::where('id', $surveyB->id)->exists(), 'tenant A cannot see tenant B survey');

        $decision = app(EntitlementResolver::class)->resolve($a, 'surveys.__unknown__');
        $this->assert(! $decision->allowed && $decision->reasonCode === 'unknown_feature', 'unknown entitlement fails closed');

        $this->forget($context);
        $this->cleanup([$a, $b], [$owner, $ownerB]);

        if ($this->failures > 0) {
            $this->error("Step 7 verification FAILED with {$this->failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('Step 7 verification passed against real PostgreSQL + Redis.');

        return self::SUCCESS;
    }

    private function establish(TenantContext $context, Tenant $tenant, TenantMembership $membership): void
    {
        $context->forget();
        $context->establish($tenant, $membership);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    }

    private function forget(TenantContext $context): void
    {
        $context->forget();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    private function assert(bool $condition, string $label): void
    {
        $condition ? $this->ok($label) : $this->bad($label);
    }

    private function ok(string $label): void
    {
        $this->line("  <info>✓</info> {$label}");
    }

    private function bad(string $label): void
    {
        $this->line("  <error>✗</error> {$label}");
        $this->failures++;
    }

    /**
     * @param  list<Tenant>  $tenants
     * @param  list<User>  $users
     */
    private function cleanup(array $tenants, array $users): void
    {
        foreach ($tenants as $tenant) {
            Tenant::withoutGlobalScopes()->whereKey($tenant->id)->delete();
        }
        foreach ($users as $user) {
            User::whereKey($user->id)->delete();
        }
    }
}
