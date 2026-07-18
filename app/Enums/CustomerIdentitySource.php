<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Where a customer identity was observed. Provenance is recorded on every identity so a link can
 * always be explained and, when wrong, precisely reversed (rule 36; ADR 0064, ADR 0071).
 */
enum CustomerIdentitySource: string
{
    case Survey = 'survey';
    case Feedback = 'feedback';
    case Manual = 'manual';
    case Api = 'api';
    case Transaction = 'transaction';
    case Google = 'google';
    case Whatsapp = 'whatsapp';

    /**
     * Sources whose runtime ingestion does not exist yet. Recording the enum value keeps the
     * contract stable, but Step 10 must not claim these integrations work (rules 27, 36).
     */
    public function isDeferredIngestion(): bool
    {
        return in_array($this, [self::Transaction, self::Google, self::Whatsapp], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Survey => 'Survey',
            self::Feedback => 'Feedback',
            self::Manual => 'Manual entry',
            self::Api => 'API',
            self::Transaction => 'Transaction',
            self::Google => 'Google',
            self::Whatsapp => 'WhatsApp',
        };
    }
}
