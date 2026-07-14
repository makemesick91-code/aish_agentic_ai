<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The foundation email envelope. It carries only the pre-rendered, non-sensitive subject and
 * body produced for a delivery — never tokens, secrets, or customer/medical content
 * (rule 04, rule 31). The subject/body are escaped by the Blade view.
 */
final class FoundationNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $subjectLine,
        public readonly string $bodyText,
    ) {}

    public function envelope(): Envelope
    {
        // A guaranteed From so the transport always accepts, independent of env config.
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
            view: 'mail.notification',
            with: [
                'subjectLine' => $this->subjectLine,
                'bodyText' => $this->bodyText,
            ],
        );
    }
}
