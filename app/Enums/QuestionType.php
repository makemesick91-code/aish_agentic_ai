<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Supported survey question types (Master Source §47; PRD FR-SUR-002). The answer storage
 * shape is derived from the type: numeric-scale types store `numeric_value`, choice types
 * store `option_id` reference(s), boolean types store `boolean_value`, text types store
 * `text_value`. Conditional logic and multilingual authoring are intentionally deferred
 * (rule 32; Step 7 §12).
 */
enum QuestionType: string
{
    case Csat = 'csat';
    case Nps = 'nps';
    case Ces = 'ces';
    case StarRating = 'star_rating';
    case EmojiRating = 'emoji_rating';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case YesNo = 'yes_no';
    case Dropdown = 'dropdown';
    case ShortText = 'short_text';
    case LongText = 'long_text';
    case Consent = 'consent';

    /** Types answered by selecting from configured options. */
    public function usesOptions(): bool
    {
        return match ($this) {
            self::SingleChoice, self::MultipleChoice, self::Dropdown => true,
            default => false,
        };
    }

    /** Types answered on a numeric scale (stored in numeric_value). */
    public function usesNumericScale(): bool
    {
        return match ($this) {
            self::Csat, self::Nps, self::Ces, self::StarRating, self::EmojiRating => true,
            default => false,
        };
    }

    /** Types answered with a boolean (stored in boolean_value). */
    public function usesBoolean(): bool
    {
        return match ($this) {
            self::YesNo, self::Consent => true,
            default => false,
        };
    }

    /** Free-text types (stored in text_value; untrusted input). */
    public function usesText(): bool
    {
        return match ($this) {
            self::ShortText, self::LongText => true,
            default => false,
        };
    }

    /** Whether more than one option may be selected. */
    public function allowsMultiple(): bool
    {
        return $this === self::MultipleChoice;
    }

    /** The customer-experience metric this type contributes to, if any. */
    public function metricType(): ?MetricType
    {
        return match ($this) {
            self::Csat => MetricType::Csat,
            self::Nps => MetricType::Nps,
            self::Ces => MetricType::Ces,
            default => null,
        };
    }

    /** Whether this type carries scoring configuration by default. */
    public function isScorable(): bool
    {
        return $this->usesNumericScale();
    }

    public function label(): string
    {
        return match ($this) {
            self::Csat => 'CSAT',
            self::Nps => 'NPS',
            self::Ces => 'CES',
            self::StarRating => 'Star rating',
            self::EmojiRating => 'Emoji rating',
            self::SingleChoice => 'Single choice',
            self::MultipleChoice => 'Multiple choice',
            self::YesNo => 'Yes / No',
            self::Dropdown => 'Dropdown',
            self::ShortText => 'Short text',
            self::LongText => 'Long text',
            self::Consent => 'Consent',
        };
    }
}
