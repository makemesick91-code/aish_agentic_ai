<?php

declare(strict_types=1);

namespace App\Customers\Exceptions;

use RuntimeException;

/**
 * Raised when a merge or split is refused. Every refusal is a guard against an irreversible or
 * unauthorized identity change (rule 36; ADR 0072).
 */
final class CustomerMergeException extends RuntimeException
{
    public static function sameCustomer(): self
    {
        return new self('A customer cannot be merged into itself.');
    }

    public static function notLinkable(): self
    {
        return new self('Only active or inactive customers can be merged.');
    }

    public static function alreadyMerged(): self
    {
        return new self('This customer has already been merged; reverse the existing merge first.');
    }

    public static function notAMergeEvent(): self
    {
        return new self('Only a merge event can be reversed.');
    }

    public static function alreadyReversed(): self
    {
        return new self('This merge has already been reversed.');
    }

    public static function supersededByLaterMerge(): self
    {
        return new self('A later merge must be reversed before this one.');
    }

    public static function branchOutOfScope(): self
    {
        return new self('You do not have access to both customers involved in this merge.');
    }
}
