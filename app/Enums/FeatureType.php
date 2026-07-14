<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The type of a plan feature/entitlement value. Values are typed and validated — never
 * stored as unvalidated free-form JSON as a source of truth (rule 31 §9.3).
 */
enum FeatureType: string
{
    case Boolean = 'boolean';
    case Integer = 'integer';
    case StringValue = 'string';
}
