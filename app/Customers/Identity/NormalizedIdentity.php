<?php

declare(strict_types=1);

namespace App\Customers\Identity;

use App\Enums\CustomerIdentityType;

/**
 * The result of normalizing a raw identity value.
 *
 * Immutable by construction so a normalized value cannot drift between the point it is validated
 * and the point it is hashed (rule 36; ADR 0071).
 */
final readonly class NormalizedIdentity
{
    public function __construct(
        public CustomerIdentityType $type,
        public string $value,
        public int $normalizerVersion,
    ) {}

    /**
     * Only non-PII identity types may persist their normalized value; PII types are hash-only
     * (ADR 0071). Callers use this instead of re-deriving the rule.
     */
    public function persistableValue(): ?string
    {
        return $this->type->isPii() ? null : $this->value;
    }
}
