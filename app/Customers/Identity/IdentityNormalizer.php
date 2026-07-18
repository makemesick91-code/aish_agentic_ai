<?php

declare(strict_types=1);

namespace App\Customers\Identity;

use App\Customers\Exceptions\InvalidIdentityValueException;
use App\Enums\CustomerIdentityType;

/**
 * The single, versioned place where a raw identity value becomes a canonical one.
 *
 * Centralization is a correctness requirement, not a style preference: if two call sites
 * normalized differently, the unique `(tenant_id, identity_type, value_hash)` index would stop
 * preventing duplicates and the same person would fragment across customers (rule 36; ADR 0071).
 *
 * Normalization is deliberately CONSERVATIVE. Over-normalizing (Gmail-style dot stripping or
 * `+tag` removal) would silently merge distinct mailboxes into one customer — an unrecoverable
 * error — so the local part is preserved verbatim.
 */
final class IdentityNormalizer
{
    /**
     * Bump ONLY together with an additive, idempotent, resumable re-hash backfill; existing rows
     * are never rewritten in place (ADR 0071).
     */
    public const VERSION = 1;

    private const MAX_LENGTH = 190;

    /** Digits required before a national number can be treated as unambiguous. */
    private const MIN_PHONE_DIGITS = 7;

    private const MAX_PHONE_DIGITS = 15;

    /**
     * @param  string  $value  Raw, untrusted input.
     * @param  string|null  $defaultRegionCallingCode  Tenant default calling code (digits, e.g. "62"),
     *                                                 used only to resolve a national-format number.
     *
     * @throws InvalidIdentityValueException
     */
    public function normalize(CustomerIdentityType $type, string $value, ?string $defaultRegionCallingCode = null): NormalizedIdentity
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw InvalidIdentityValueException::forEmpty();
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidIdentityValueException::tooLong();
        }

        $normalized = match ($type) {
            CustomerIdentityType::Email => $this->normalizeEmail($trimmed),
            CustomerIdentityType::Phone => $this->normalizePhone($trimmed, $defaultRegionCallingCode),
            CustomerIdentityType::ExternalRef => $this->normalizeExternalRef($trimmed),
        };

        return new NormalizedIdentity($type, $normalized, self::VERSION);
    }

    /**
     * Lowercase and Unicode-normalize, but keep the local part exactly as given.
     *
     * The domain is case-insensitive per RFC 1035 and the local part is case-sensitive in theory,
     * but every practical mail provider treats it case-insensitively — lowercasing the whole
     * address is the behaviour that actually prevents duplicates without merging distinct people.
     */
    private function normalizeEmail(string $value): string
    {
        $value = $this->toNfkc($value);
        $value = mb_strtolower($value, 'UTF-8');

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidIdentityValueException::forEmail();
        }

        // Reject an address whose domain has no dot: it cannot be a routable public mailbox and
        // is far more likely to be a typo that would create a junk identity.
        $domain = substr(strrchr($value, '@') ?: '', 1);

        if ($domain === '' || ! str_contains($domain, '.')) {
            throw InvalidIdentityValueException::forEmail();
        }

        return $value;
    }

    /**
     * Produce E.164 or refuse.
     *
     * A number we cannot place unambiguously must NOT become a deterministic identity — matching
     * on a half-parsed number is how two different people get merged.
     */
    private function normalizePhone(string $value, ?string $defaultRegionCallingCode): string
    {
        $value = $this->toNfkc($value);

        // International prefix forms: "00" (ITU) and "011" are written as "+".
        $candidate = preg_replace('/^00/', '+', $value) ?? $value;

        // Strip only formatting characters; anything else left over makes the value ambiguous.
        $candidate = preg_replace('/[\s\-\(\)\.]/u', '', $candidate) ?? $candidate;

        $hasPlus = str_starts_with($candidate, '+');
        $digits = preg_replace('/\D/', '', $candidate) ?? '';

        // If stripping formatting removed letters or symbols, the input was not a phone number.
        if ($digits === '' || $candidate !== ($hasPlus ? '+'.$digits : $digits)) {
            throw InvalidIdentityValueException::forPhone();
        }

        if (! $hasPlus) {
            $region = $defaultRegionCallingCode !== null
                ? (preg_replace('/\D/', '', $defaultRegionCallingCode) ?? '')
                : '';

            // Without a tenant region we cannot know which country a national number belongs to.
            if ($region === '') {
                throw InvalidIdentityValueException::forPhone();
            }

            // National trunk prefix "0" is dropped when promoting to international form.
            $digits = ltrim($digits, '0');

            if ($digits === '') {
                throw InvalidIdentityValueException::forPhone();
            }

            $digits = $region.$digits;
        }

        $length = strlen($digits);

        if ($length < self::MIN_PHONE_DIGITS || $length > self::MAX_PHONE_DIGITS) {
            throw InvalidIdentityValueException::forPhone();
        }

        return '+'.$digits;
    }

    /**
     * External references are opaque tokens from another system: case and punctuation may be
     * meaningful, so only surrounding whitespace is removed.
     */
    private function normalizeExternalRef(string $value): string
    {
        return $this->toNfkc($value);
    }

    /**
     * Fold visually-identical Unicode representations together so a full-width or decomposed
     * character cannot create a second identity for the same value.
     */
    private function toNfkc(string $value): string
    {
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($value, \Normalizer::FORM_KC);

            if (is_string($normalized)) {
                return $normalized;
            }
        }

        return $value;
    }
}
