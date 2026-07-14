<?php

declare(strict_types=1);

namespace App\Feedback\Bulk;

/**
 * The truthful outcome of a bulk feedback operation. Step 8 bulk operations are all-or-nothing (the
 * whole batch is validated before any mutation), so `processed` equals the batch size on success;
 * there is no hidden partial success (rule 33; Step 8 §17).
 */
final class BulkResult
{
    public function __construct(
        public readonly string $operation,
        public readonly int $processed,
    ) {}
}
