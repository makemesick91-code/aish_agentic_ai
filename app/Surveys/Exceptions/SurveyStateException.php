<?php

declare(strict_types=1);

namespace App\Surveys\Exceptions;

use RuntimeException;

/**
 * Raised when an operation is attempted against a survey/version/campaign in a state that does
 * not permit it (invalid transition, editing published content, binding a non-published
 * version, …).
 */
final class SurveyStateException extends RuntimeException
{
    public static function notEditable(): self
    {
        return new self('Only a draft survey version may be edited; edit by creating a new draft version.');
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self("Illegal survey state transition from {$from} to {$to}.");
    }

    public static function versionNotPublished(): self
    {
        return new self('A campaign can only bind an immutable published survey version.');
    }

    public static function cannotIssueInvitations(): self
    {
        return new self('Invitations can only be issued for an active campaign bound to a published survey.');
    }

    public static function message(string $message): self
    {
        return new self($message);
    }
}
