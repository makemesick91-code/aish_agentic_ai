<?php

declare(strict_types=1);

namespace App\Feedback\Export;

/**
 * The outcome of writing a feedback export file: the private storage path, the number of rows
 * written, and the byte size (rule 33; Step 8 §18).
 */
final class ExportResult
{
    public function __construct(
        public readonly string $path,
        public readonly int $rows,
        public readonly int $sizeBytes,
    ) {}
}
