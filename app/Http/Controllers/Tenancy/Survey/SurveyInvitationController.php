<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Survey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreInvitationRequest;
use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Surveys\Exceptions\EntitlementDeniedException;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\SurveyEntitlements;
use App\Surveys\SurveyInvitationService;
use App\Surveys\SurveyLinkBuilder;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Unique invitation issuance and revocation. The one-time token is delivered only by email
 * (never returned in the response, flashed to the session, logged, or audited); for tokenless
 * sharing the campaign exposes a public link + QR (rule 32; Step 7 §17.2).
 */
final class SurveyInvitationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SurveyInvitationService $invitations,
        private readonly SurveyEntitlements $entitlements,
        private readonly SurveyLinkBuilder $links,
    ) {}

    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        $this->authorize('create', SurveyInvitation::class);

        $campaign = SurveyCampaign::where('ulid', $request->validated()['campaign_ulid'])->firstOrFail();

        try {
            $this->entitlements->assertCanIssueInvitation(app(TenantContext::class)->tenant());
        } catch (EntitlementDeniedException $e) {
            return back()->withErrors(['entitlement' => $e->getMessage()]);
        }

        $email = $request->validated()['recipient_email'] ?? null;

        try {
            $issued = $this->invitations->issue($campaign, [
                'idempotency_key' => $request->validated()['idempotency_key'] ?? (string) Str::uuid(),
                'recipient_email' => $email,
            ], $request->user());
        } catch (SurveyStateException $e) {
            return back()->withErrors(['invitation' => $e->getMessage()]);
        }

        // Deliver by email only; the plaintext token lives solely inside the emailed link.
        if ($email !== null && $issued->plainToken !== null) {
            $url = $this->links->invitationUrl($issued->invitation, $issued->plainToken);
            $this->invitations->deliver($issued->invitation, $url, $request->user());

            return back()->with('status', __('Invitation created and sent by email.'));
        }

        return back()->with('status', __('Invitation created.'));
    }

    public function revoke(SurveyInvitation $invitation): RedirectResponse
    {
        $this->authorize('revoke', $invitation);

        try {
            $this->invitations->revoke($invitation, request()->user());
        } catch (SurveyStateException $e) {
            return back()->withErrors(['invitation' => $e->getMessage()]);
        }

        return back()->with('status', __('Invitation revoked.'));
    }
}
