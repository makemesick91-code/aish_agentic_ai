<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Audit\AuditRecorder;
use App\Enums\CampaignStatus;
use App\Enums\SurveyStatus;
use App\Enums\SurveyVersionStatus;
use App\Models\Survey;
use App\Models\SurveyCampaign;
use App\Models\SurveyVersion;
use App\Models\User;
use App\Surveys\Exceptions\SurveyStateException;
use Illuminate\Support\Facades\DB;

/**
 * Campaign lifecycle. A campaign always binds an immutable published survey version and can
 * never silently switch it. Branch scope is enforced: a branch-owned survey's campaign cannot
 * target another branch. All writes are tenant-scoped and audited (rule 32; Step 7 §16).
 */
final class CampaignService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @param array{name: string, branch_id?: int|null, invitation_expiry_days?: int|null, starts_at?: \DateTimeInterface|null, ends_at?: \DateTimeInterface|null} $data */
    public function create(Survey $survey, SurveyVersion $version, array $data, User $actor): SurveyCampaign
    {
        if ($version->survey_id !== $survey->id) {
            throw SurveyStateException::message('The version does not belong to this survey.');
        }

        if ($version->status !== SurveyVersionStatus::Published) {
            throw SurveyStateException::versionNotPublished();
        }

        // A branch-owned survey may only run branch-scoped campaigns for its own branch.
        $branchId = $data['branch_id'] ?? $survey->branch_id;
        if ($survey->branch_id !== null && $branchId !== $survey->branch_id) {
            throw SurveyStateException::message('A branch-owned survey cannot run a campaign for another branch.');
        }

        $campaign = DB::transaction(fn (): SurveyCampaign => SurveyCampaign::create([
            'branch_id' => $branchId,
            'survey_id' => $survey->id,
            'survey_version_id' => $version->id,
            'name' => $data['name'],
            'status' => CampaignStatus::Draft,
            'mode' => $version->mode->value,
            'invitation_expiry_days' => $data['invitation_expiry_days'] ?? 7,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'created_by' => $actor->id,
        ]));

        $this->audit->record('survey.campaign.created', [
            'subject' => $campaign,
            'actor_id' => $actor->id,
            'metadata' => ['campaign_ulid' => $campaign->ulid, 'version_number' => $version->version_number],
        ]);

        return $campaign;
    }

    public function activate(SurveyCampaign $campaign, User $actor): SurveyCampaign
    {
        // Activation requires the bound survey to be published (not paused/archived).
        $survey = Survey::whereKey($campaign->survey_id)->firstOrFail();
        if ($survey->status !== SurveyStatus::Published) {
            throw SurveyStateException::message('The campaign\'s survey must be published to activate the campaign.');
        }

        return $this->transition($campaign, CampaignStatus::Active, 'survey.campaign.activated', $actor);
    }

    public function pause(SurveyCampaign $campaign, User $actor): SurveyCampaign
    {
        return $this->transition($campaign, CampaignStatus::Paused, 'survey.campaign.paused', $actor);
    }

    public function end(SurveyCampaign $campaign, User $actor): SurveyCampaign
    {
        return $this->transition($campaign, CampaignStatus::Ended, 'survey.campaign.ended', $actor);
    }

    public function archive(SurveyCampaign $campaign, User $actor): SurveyCampaign
    {
        return $this->transition($campaign, CampaignStatus::Archived, 'survey.campaign.archived', $actor);
    }

    private function transition(SurveyCampaign $campaign, CampaignStatus $target, string $event, User $actor): SurveyCampaign
    {
        if (! $campaign->status->canTransitionTo($target)) {
            throw SurveyStateException::invalidTransition($campaign->status->value, $target->value);
        }

        $campaign->forceFill(['status' => $target])->save();

        $this->audit->record($event, [
            'subject' => $campaign,
            'actor_id' => $actor->id,
            'metadata' => ['status' => $target->value],
        ]);

        return $campaign->fresh();
    }
}
