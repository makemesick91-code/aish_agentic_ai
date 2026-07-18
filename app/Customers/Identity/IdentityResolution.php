<?php

declare(strict_types=1);

namespace App\Customers\Identity;

use App\Models\Customer;

/**
 * The outcome of resolving a set of identity candidates.
 *
 * `customer` is null for the anonymous case — a source with no verifiable identity must NOT create
 * a customer, and callers must handle that rather than inventing one (rule 36, rule 32).
 */
final readonly class IdentityResolution
{
    /**
     * @param  list<string>  $suggestedReasons  Why a candidate became a suggestion instead of a link.
     */
    public function __construct(
        public ?Customer $customer,
        public bool $customerWasCreated,
        public int $identitiesLinked,
        public array $suggestedReasons = [],
    ) {}

    /**
     * @param  list<string>  $suggestedReasons
     */
    public static function anonymous(array $suggestedReasons = []): self
    {
        return new self(null, false, 0, $suggestedReasons);
    }

    public function isAnonymous(): bool
    {
        return $this->customer === null;
    }
}
