<?php

declare(strict_types=1);

namespace App\Feedback\Exceptions;

use RuntimeException;

/**
 * Raised when a bulk feedback operation cannot proceed. Bulk operations are bounded and all-or-nothing:
 * an empty/oversized batch, an unresolved id (not in the tenant), an item the actor cannot reach, or an
 * item that cannot undergo the requested operation aborts the ENTIRE batch before any mutation — never a
 * hidden partial success (rule 33; Step 8 §17).
 */
final class FeedbackBulkException extends RuntimeException
{
    public static function emptyBatch(): self
    {
        return new self('No feedback items were selected.');
    }

    public static function tooLarge(int $max): self
    {
        return new self("A bulk operation may target at most {$max} feedback items.");
    }

    public static function unresolved(): self
    {
        return new self('One or more selected items are not available in this workspace.');
    }

    public static function forbiddenItem(string $ulid): self
    {
        return new self("You cannot act on feedback item {$ulid} (out of branch scope).");
    }

    public static function invalidForItem(string $ulid): self
    {
        return new self("The requested change is not valid for feedback item {$ulid}.");
    }

    public static function reopenNotAllowed(): self
    {
        return new self('Reopening requires a reason and cannot be performed as a bulk operation.');
    }
}
