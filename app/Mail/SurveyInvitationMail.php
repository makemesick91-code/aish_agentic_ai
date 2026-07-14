<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Customer-facing survey invitation email. It carries ONLY the opaque public survey link and a
 * neutral subject — never medical/transaction content, and the token exists only inside the
 * link (never stored in a delivery record or logged). Deliberately NOT queued so the token
 * never lands in a persisted queue payload (rule 32; Step 7 §17.2, §22).
 */
final class SurveyInvitationMail extends Mailable
{
    public function __construct(
        public readonly string $subjectLine,
        public readonly string $url,
    ) {}

    public function envelope(): Envelope
    {
        $address = (string) (config('mail.from.address') ?: 'no-reply@aish.local');
        $name = (string) (config('mail.from.name') ?: 'Aish Agentic AI');

        return new Envelope(
            from: new Address($address, $name),
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.survey-invitation',
            with: [
                'subjectLine' => $this->subjectLine,
                'url' => $this->url,
            ],
        );
    }
}
