<?php

declare(strict_types=1);

namespace App\Customers;

use App\Customers\Identity\IdentityCandidate;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Models\FeedbackItem;
use App\Models\SurveyInvitation;

/**
 * Links an existing Step 8 feedback item to its canonical Customer 360 profile.
 *
 * The only identity Step 10 treats as VERIFIED for survey-sourced feedback is the recipient address
 * of a survey invitation that was actually redeemed: the tenant chose to send there and the
 * one-time token came back, which is real evidence of mailbox control. A public-link or QR response
 * carries no such evidence, so it stays anonymous rather than inventing a customer
 * (rule 36, rule 32; ADR 0064).
 *
 * Linking is idempotent: an already-linked item is left alone, so a replay, retry, or reconcile
 * re-run can never produce a second customer or a changed link.
 */
final class CustomerFeedbackLinker
{
    public function __construct(private readonly CustomerIdentityResolver $resolver) {}

    /**
     * @return bool True when this call established a new link.
     */
    public function link(FeedbackItem $item): bool
    {
        if ($item->customer_id !== null) {
            return false;
        }

        $email = $this->verifiedRecipientEmail($item);

        if ($email === null) {
            return false;
        }

        $resolution = $this->resolver->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(
                CustomerIdentityType::Email,
                $email,
                provenance: 'survey_invitation',
            )],
            branchId: $item->branch_id,
        );

        if ($resolution->customer === null) {
            return false;
        }

        // forceFill because customer_id is deliberately not fillable on FeedbackItem — the customer
        // domain is its only writer (ADR 0070).
        $item->forceFill(['customer_id' => $resolution->customer->id])->save();

        return true;
    }

    /**
     * The invitation address, but only when the invitation was genuinely completed. An invitation
     * that was merely sent proves nothing about who answered.
     */
    private function verifiedRecipientEmail(FeedbackItem $item): ?string
    {
        if ($item->invitation_id === null) {
            return null;
        }

        // Queried directly rather than through a relation: the customer domain reads Step 8/7 data
        // but must not widen those models to serve itself (ADR 0070).
        $invitation = SurveyInvitation::query()->find($item->invitation_id);

        if ($invitation === null || $invitation->completed_at === null) {
            return null;
        }

        $email = $invitation->recipient_email;

        return is_string($email) && $email !== '' ? $email : null;
    }
}
