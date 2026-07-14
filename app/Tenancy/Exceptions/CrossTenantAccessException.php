<?php

declare(strict_types=1);

namespace App\Tenancy\Exceptions;

use RuntimeException;

/**
 * Thrown when an operation would cross a tenant boundary — a mismatched tenant_id, a
 * storage path escaping the tenant root, or a job restoring the wrong tenant (rule 03).
 */
final class CrossTenantAccessException extends RuntimeException {}
