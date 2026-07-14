<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of an internal feedback attachment. Only states backed by real behavior are modeled: an
 * accepted upload is `Available`, a rejected upload is `Rejected`, and an archived/removed file is
 * `Removed`. No `quarantined`/`scanned` state is claimed because no malware scanner is wired in
 * Step 8 (rule 33; Step 8 §14).
 */
enum FeedbackAttachmentState: string
{
    case Available = 'available';
    case Rejected = 'rejected';
    case Removed = 'removed';

    public function isDownloadable(): bool
    {
        return $this === self::Available;
    }

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Rejected => 'Rejected',
            self::Removed => 'Removed',
        };
    }
}
