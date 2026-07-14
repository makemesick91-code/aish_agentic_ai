<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicSurvey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\SubmitSurveyResponseRequest;
use App\Surveys\Exceptions\EntitlementDeniedException;
use App\Surveys\Exceptions\InvalidSurveyLinkException;
use App\Surveys\Exceptions\ResponseValidationException;
use App\Surveys\PublicSurveyGateway;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The public, unauthenticated survey surface. It never touches tenant application context or
 * RBAC; access is entirely via the opaque link/token resolved by the gateway. Draft content is
 * unreachable here. All failure modes render truthful states without revealing whether a token
 * maps to a real tenant (rule 32; Step 7 §18).
 */
final class PublicSurveyController extends Controller
{
    public function __construct(private readonly PublicSurveyGateway $gateway) {}

    public function showCampaign(string $campaign): View|Response
    {
        try {
            $view = $this->gateway->campaignView($campaign);
        } catch (InvalidSurveyLinkException $e) {
            return $this->unavailable($e->getMessage());
        }

        return view('survey.public.show', [
            'view' => $view,
            'submitUrl' => route('survey.public.campaign.submit', ['campaign' => $campaign]),
        ]);
    }

    public function submitCampaign(SubmitSurveyResponseRequest $request, string $campaign): Response|RedirectResponse
    {
        try {
            $this->gateway->submitForCampaign($campaign, $request->answers(), $this->meta($request));
        } catch (ResponseValidationException $e) {
            return redirect()->route('survey.public.campaign', ['campaign' => $campaign])
                ->withErrors($e->errors)->withInput();
        } catch (EntitlementDeniedException) {
            return $this->unavailable(__('This survey is not accepting responses right now.'));
        } catch (InvalidSurveyLinkException $e) {
            return $this->unavailable($e->getMessage());
        }

        return $this->thanks();
    }

    public function showInvitation(string $invitation, string $token): View|Response
    {
        try {
            $view = $this->gateway->invitationView($invitation, $token);
        } catch (InvalidSurveyLinkException $e) {
            return $this->unavailable($e->getMessage());
        }

        return view('survey.public.show', [
            'view' => $view,
            'submitUrl' => route('survey.public.invitation.submit', ['invitation' => $invitation, 'token' => $token]),
        ]);
    }

    public function submitInvitation(SubmitSurveyResponseRequest $request, string $invitation, string $token): Response|RedirectResponse
    {
        try {
            $this->gateway->submitForInvitation($invitation, $token, $request->answers(), $this->meta($request));
        } catch (ResponseValidationException $e) {
            return redirect()->route('survey.public.invitation', ['invitation' => $invitation, 'token' => $token])
                ->withErrors($e->errors)->withInput();
        } catch (EntitlementDeniedException) {
            return $this->unavailable(__('This survey is not accepting responses right now.'));
        } catch (InvalidSurveyLinkException $e) {
            return $this->unavailable($e->getMessage());
        }

        return $this->thanks();
    }

    /** @return array<string, string> Minimized, hashed request metadata only — never raw PII. */
    private function meta(Request $request): array
    {
        return [
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
        ];
    }

    private function unavailable(?string $message = null): Response
    {
        return response()->view('survey.public.unavailable', [
            'message' => $message ?? __('This survey link is not valid or is no longer available.'),
        ], 410);
    }

    private function thanks(): Response
    {
        return response()->view('survey.public.thanks', [], 200);
    }
}
