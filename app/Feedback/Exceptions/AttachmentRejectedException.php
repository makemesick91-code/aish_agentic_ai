<?php

declare(strict_types=1);

namespace App\Feedback\Exceptions;

use RuntimeException;

/**
 * Raised when an uploaded internal attachment is rejected by content inspection (disallowed MIME
 * type detected from the file content, empty file, or size over the limit). The rejection is
 * truthful — no file is ever stored or claimed as scanned (rule 33; Step 8 §14).
 */
final class AttachmentRejectedException extends RuntimeException
{
    private function __construct(public readonly string $reasonCode, string $message)
    {
        parent::__construct($message);
    }

    public static function mimeNotAllowed(string $detected): self
    {
        return new self('mime_not_allowed', "The file type \"{$detected}\" is not allowed.");
    }

    public static function tooLarge(): self
    {
        return new self('too_large', 'The file exceeds the maximum allowed size.');
    }

    public static function empty(): self
    {
        return new self('empty', 'The file is empty.');
    }
}
