<?php

declare(strict_types=1);

namespace App\Feedback\Exceptions;

use RuntimeException;

/**
 * Raised for invalid manual-tag operations: a duplicate tag name within a tenant, attaching an
 * archived tag, or a cross-tenant attach attempt (which the composite FK also blocks). Fails closed
 * (rule 33; Step 8 §12).
 */
final class FeedbackTagException extends RuntimeException
{
    public static function duplicateName(string $name): self
    {
        return new self("A feedback tag named \"{$name}\" already exists in this workspace.");
    }

    public static function archivedNotAttachable(): self
    {
        return new self('An archived tag cannot be attached to a feedback item.');
    }

    public static function crossTenant(): self
    {
        return new self('A tag can only be attached to feedback in the same workspace.');
    }
}
