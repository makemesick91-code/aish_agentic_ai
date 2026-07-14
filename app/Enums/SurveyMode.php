<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Whether a survey version collects responses anonymously or linked to an invitation
 * recipient. Anonymous means no required customer identity and NO hidden customer creation;
 * an IP address is never treated as a customer identity (rule 32; Step 7 §20).
 */
enum SurveyMode: string
{
    case Anonymous = 'anonymous';
    case Identified = 'identified';

    public function label(): string
    {
        return match ($this) {
            self::Anonymous => 'Anonymous',
            self::Identified => 'Identified',
        };
    }
}
