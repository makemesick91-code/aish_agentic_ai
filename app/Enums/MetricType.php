<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Customer-experience metric a scored question contributes to. Scoring is deterministic and
 * versioned, computed from stored raw answer values — never from UI labels (rule 32; Step 7
 * §13, ADR 0059).
 */
enum MetricType: string
{
    case Csat = 'csat';
    case Nps = 'nps';
    case Ces = 'ces';

    public function label(): string
    {
        return match ($this) {
            self::Csat => 'CSAT',
            self::Nps => 'NPS',
            self::Ces => 'CES',
        };
    }
}
