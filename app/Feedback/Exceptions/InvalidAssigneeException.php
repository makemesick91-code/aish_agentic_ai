<?php

declare(strict_types=1);

namespace App\Feedback\Exceptions;

use RuntimeException;

/**
 * Raised when a feedback item cannot be assigned to a proposed user because the user is not an
 * active member of the tenant, is suspended, lacks feedback permission, or is outside the item's
 * branch scope. Cross-tenant/cross-branch assignment fails closed (rule 33; Step 8 §11).
 */
final class InvalidAssigneeException extends RuntimeException
{
    private function __construct(public readonly string $reasonCode, string $message)
    {
        parent::__construct($message);
    }

    public static function suspendedUser(): self
    {
        return new self('suspended_user', 'The proposed assignee account is not active.');
    }

    public static function notActiveMember(): self
    {
        return new self('not_active_member', 'The proposed assignee is not an active member of this workspace.');
    }

    public static function branchOutOfScope(): self
    {
        return new self('branch_out_of_scope', 'The proposed assignee cannot access this feedback item\'s branch.');
    }

    public static function missingPermission(): self
    {
        return new self('missing_permission', 'The proposed assignee does not have feedback access.');
    }
}
