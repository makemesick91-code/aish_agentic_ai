<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Mail\SurveyInvitationMail;
use Illuminate\Support\Facades\Mail;

/**
 * The single designated adapter for sending customer survey-invitation email. Customer
 * invitations go to non-members, so they cannot use the member-only notification MailChannel;
 * centralizing them here keeps outbound mail to reviewed adapters only (rule 31 §8.2, rule 32;
 * Step 7 §22). The URL contains the one-time token and is sent synchronously so it never lands
 * in a persisted queue payload.
 */
final class SurveyInvitationMailer
{
    public function send(string $email, string $url, string $subject = 'Kami ingin mendengar masukan Anda'): void
    {
        Mail::to($email)->send(new SurveyInvitationMail(subjectLine: $subject, url: $url));
    }
}
