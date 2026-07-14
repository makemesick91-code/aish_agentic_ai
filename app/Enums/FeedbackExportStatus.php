<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Truthful state of a queued feedback export. `Ready` is set only after the private file has been
 * successfully written; it never claims success before the file exists (rule 33; Step 8 §18).
 */
enum FeedbackExportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Ready, self::Failed, self::Expired, self::Cancelled], true);
    }

    public function isDownloadable(): bool
    {
        return $this === self::Ready;
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Ready => 'Ready',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }
}
