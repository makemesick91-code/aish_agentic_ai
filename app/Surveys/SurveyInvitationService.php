<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Audit\AuditRecorder;
use App\Enums\InvitationStatus;
use App\Mail\SurveyInvitationMail;
use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Models\User;
use App\Surveys\Exceptions\SurveyStateException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Issues and manages unique survey invitations. A cryptographically strong token is generated,
 * only its SHA-256 hash is stored, and the plaintext is returned once (for link building) and
 * never persisted or logged. Issuance is idempotent per (tenant, idempotency_key) so a retry
 * never creates a duplicate invitation (rule 32; Step 7 §17).
 */
final class SurveyInvitationService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Issue (or idempotently return) a unique invitation for an active campaign.
     *
     * @param  array{idempotency_key: string, recipient_email?: string|null, branch_id?: int|null, expires_at?: \DateTimeInterface|null}  $data
     */
    public function issue(SurveyCampaign $campaign, array $data, User $actor): IssuedInvitation
    {
        if (! $campaign->status->canIssueInvitations()) {
            throw SurveyStateException::cannotIssueInvitations();
        }

        $existing = SurveyInvitation::where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing !== null) {
            return new IssuedInvitation($existing, null);
        }

        // 256 bits of entropy; only the hash is stored. The plaintext never touches the DB or logs.
        $plainToken = bin2hex(random_bytes(32));
        $expiresAt = $data['expires_at']
            ?? ($campaign->invitation_expiry_days !== null ? now()->addDays($campaign->invitation_expiry_days) : null);

        try {
            $invitation = DB::transaction(fn (): SurveyInvitation => SurveyInvitation::create([
                'branch_id' => $data['branch_id'] ?? $campaign->branch_id,
                'campaign_id' => $campaign->id,
                'survey_version_id' => $campaign->survey_version_id,
                'token_hash' => hash('sha256', $plainToken),
                'recipient_email' => $data['recipient_email'] ?? null,
                'status' => InvitationStatus::Created,
                'idempotency_key' => $data['idempotency_key'],
                'expires_at' => $expiresAt,
                'created_by' => $actor->id,
            ]));
        } catch (UniqueConstraintViolationException) {
            // Concurrent issue with the same idempotency key: return the winner, no new token.
            $winner = SurveyInvitation::where('idempotency_key', $data['idempotency_key'])->firstOrFail();

            return new IssuedInvitation($winner, null);
        }

        // Audit records the public id only — never the token or its hash.
        $this->audit->record('survey.invitation.created', [
            'subject' => $campaign,
            'actor_id' => $actor->id,
            'metadata' => ['invitation_public_id' => $invitation->public_id],
        ]);

        return new IssuedInvitation($invitation, $plainToken);
    }

    /**
     * Deliver the invitation email synchronously via the mail channel and record a truthful
     * delivery state. `$url` already contains the one-time token; the token is never stored in
     * any record or log. `sent` means accepted by the mail transport — not a proven receipt.
     */
    public function deliver(SurveyInvitation $invitation, string $url, User $actor): SurveyInvitation
    {
        if ($invitation->recipient_email === null) {
            throw SurveyStateException::message('This invitation has no recipient email to deliver to.');
        }

        try {
            Mail::to($invitation->recipient_email)->send(new SurveyInvitationMail(
                subjectLine: 'Kami ingin mendengar masukan Anda',
                url: $url,
            ));
        } catch (\Throwable) {
            $failed = $this->markDeliveryFailed($invitation, 'mail_transport_error');
            $this->audit->record('survey.invitation.delivery_requested', [
                'subject' => $invitation->campaign,
                'actor_id' => $actor->id,
                'metadata' => ['invitation_public_id' => $invitation->public_id, 'channel' => 'email', 'result' => 'failed'],
            ]);

            return $failed;
        }

        $sent = $this->markSent($invitation);
        $this->audit->record('survey.invitation.delivery_requested', [
            'subject' => $invitation->campaign,
            'actor_id' => $actor->id,
            'metadata' => ['invitation_public_id' => $invitation->public_id, 'channel' => 'email', 'result' => 'sent'],
        ]);

        return $sent;
    }

    public function markSent(SurveyInvitation $invitation): SurveyInvitation
    {
        return $this->setStatus($invitation, InvitationStatus::Sent);
    }

    public function markDeliveryFailed(SurveyInvitation $invitation, string $code): SurveyInvitation
    {
        $invitation->forceFill([
            'status' => InvitationStatus::DeliveryFailed,
            'delivery_failure_code' => substr($code, 0, 64),
        ])->save();

        return $invitation->fresh();
    }

    public function revoke(SurveyInvitation $invitation, User $actor): SurveyInvitation
    {
        if ($invitation->status->isTerminal()) {
            throw SurveyStateException::message('A completed/expired/revoked invitation cannot be revoked.');
        }

        $invitation->forceFill([
            'status' => InvitationStatus::Revoked,
            'revoked_at' => now(),
        ])->save();

        $this->audit->record('survey.invitation.revoked', [
            'subject' => $invitation->campaign,
            'actor_id' => $actor->id,
            'metadata' => ['invitation_public_id' => $invitation->public_id],
        ]);

        return $invitation->fresh();
    }

    private function setStatus(SurveyInvitation $invitation, InvitationStatus $status): SurveyInvitation
    {
        $invitation->forceFill(['status' => $status])->save();

        return $invitation->fresh();
    }
}
