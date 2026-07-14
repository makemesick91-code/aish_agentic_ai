<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of a single survey version. A version is authored as `draft`, becomes `published`
 * (immutable content) exactly once, and is marked `superseded` when a newer version is
 * published. Editing published content creates a NEW draft version — it never mutates a
 * published one (rule 32; Step 7 §11).
 */
enum SurveyVersionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Superseded = 'superseded';

    /** Whether this version's content (questions, options, scoring, mode) may still be edited. */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /** Whether a campaign or response may bind to this version. */
    public function isBindable(): bool
    {
        return $this === self::Published;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Superseded => 'Superseded',
        };
    }
}
