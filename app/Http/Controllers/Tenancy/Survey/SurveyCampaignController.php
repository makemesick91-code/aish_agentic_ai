<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Survey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreCampaignRequest;
use App\Models\Survey;
use App\Models\SurveyCampaign;
use App\Surveys\CampaignService;
use App\Surveys\Exceptions\EntitlementDeniedException;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\SurveyEntitlements;
use App\Surveys\SurveyLinkBuilder;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

final class SurveyCampaignController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CampaignService $campaigns,
        private readonly SurveyEntitlements $entitlements,
        private readonly SurveyLinkBuilder $links,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SurveyCampaign::class);

        $campaigns = SurveyCampaign::query()->orderByDesc('id')->paginate(20);

        return view('surveys.campaigns.index', compact('campaigns'));
    }

    public function show(SurveyCampaign $campaign): View
    {
        $this->authorize('view', $campaign);

        return view('surveys.campaigns.show', [
            'campaign' => $campaign,
            'publicUrl' => $this->links->campaignUrl($campaign),
            'qrUrl' => $this->links->campaignQrUrl($campaign),
            'invitations' => $campaign->invitations()->orderByDesc('id')->paginate(20),
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $this->authorize('create', SurveyCampaign::class);

        try {
            $this->entitlements->assertCanCreateCampaign(app(TenantContext::class)->tenant());
        } catch (EntitlementDeniedException $e) {
            return back()->withErrors(['entitlement' => $e->getMessage()]);
        }

        $survey = Survey::where('ulid', $request->validated()['survey_ulid'])->firstOrFail();
        $version = $survey->current_version_id !== null ? $survey->currentVersion()->first() : null;

        if ($version === null) {
            return back()->withErrors(['campaign' => __('Publish the survey before creating a campaign.')]);
        }

        try {
            $campaign = $this->campaigns->create($survey, $version, $request->validated(), $request->user());
        } catch (SurveyStateException $e) {
            return back()->withErrors(['campaign' => $e->getMessage()]);
        }

        return redirect()->route('survey-campaigns.show', $campaign)->with('status', __('Campaign created.'));
    }

    public function activate(SurveyCampaign $campaign): RedirectResponse
    {
        $this->authorize('activate', $campaign);

        return $this->transition(fn () => $this->campaigns->activate($campaign, request()->user()), 'Campaign activated.');
    }

    public function pause(SurveyCampaign $campaign): RedirectResponse
    {
        $this->authorize('pause', $campaign);

        return $this->transition(fn () => $this->campaigns->pause($campaign, request()->user()), 'Campaign paused.');
    }

    public function end(SurveyCampaign $campaign): RedirectResponse
    {
        $this->authorize('end', $campaign);

        return $this->transition(fn () => $this->campaigns->end($campaign, request()->user()), 'Campaign ended.');
    }

    private function transition(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (SurveyStateException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', __($message));
    }
}
