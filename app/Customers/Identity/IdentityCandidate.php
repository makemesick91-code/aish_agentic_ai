<?php

declare(strict_types=1);

namespace App\Customers\Identity;

use App\Enums\CustomerIdentityType;

/**
 * A raw identity value offered by a source, together with whether that source actually PROVED the
 * customer owns it.
 *
 * `isVerified` is the security-critical field: only a verified identity may be linked
 * automatically. An unverified value is merely something someone typed, so linking on it would let
 * one person attach themselves to another person's history (rule 36; ADR 0064, ADR 0071).
 */
final readonly class IdentityCandidate
{
    public function __construct(
        public CustomerIdentityType $type,
        public string $rawValue,
        public bool $isVerified = false,
        public int $confidence = 100,
        public ?string $provenance = null,
    ) {}

    public static function verified(CustomerIdentityType $type, string $rawValue, ?string $provenance = null): self
    {
        return new self($type, $rawValue, true, 100, $provenance);
    }

    public static function unverified(
        CustomerIdentityType $type,
        string $rawValue,
        int $confidence = 60,
        ?string $provenance = null,
    ): self {
        return new self($type, $rawValue, false, $confidence, $provenance);
    }
}
