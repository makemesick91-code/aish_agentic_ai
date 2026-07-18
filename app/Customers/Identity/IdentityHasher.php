<?php

declare(strict_types=1);

namespace App\Customers\Identity;

use Illuminate\Contracts\Config\Repository as Config;

/**
 * Turns a normalized identity into the value stored in `customer_identities.value_hash`.
 *
 * The hash is KEYED, not a plain digest. Phone numbers and email addresses occupy a small enough
 * value space to enumerate exhaustively, so an unsalted SHA-256 column would be an offline
 * crackable directory of every customer's contact details.
 *
 * The tenant id is bound into the key, so the same email in two tenants produces unrelated
 * hashes. That makes the table useless for confirming that a person exists in another tenant —
 * cross-tenant correlation is prevented by construction, not only by query scoping (ADR 0071).
 */
final class IdentityHasher
{
    private const ALGO = 'sha256';

    public function __construct(private readonly Config $config) {}

    /**
     * @return string 64-char lowercase hex.
     */
    public function hash(int $tenantId, NormalizedIdentity $identity): string
    {
        $message = $identity->type->value.':'.$identity->value;

        return hash_hmac(self::ALGO, $message, $this->key($tenantId));
    }

    /**
     * Constant-time comparison — hash equality is checked on a value an attacker may be able to
     * influence, so it must not leak through timing.
     */
    public function matches(string $known, string $candidate): bool
    {
        return hash_equals($known, $candidate);
    }

    /**
     * Derive the per-tenant key from the application pepper.
     *
     * The pepper is derived from APP_KEY rather than configured separately so a deployment cannot
     * accidentally run with an empty or default pepper. It is never logged, audited, or returned.
     */
    private function key(int $tenantId): string
    {
        $appKey = (string) $this->config->get('app.key', '');

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);

            if ($decoded !== false) {
                $appKey = $decoded;
            }
        }

        if ($appKey === '') {
            // Fail loudly: silently hashing with an empty key would produce a table that looks
            // protected but is not.
            throw new \RuntimeException(
                'Cannot hash a customer identity without an application key (rule 36; ADR 0071).'
            );
        }

        // Domain-separated so this key can never collide with another APP_KEY-derived secret.
        return hash_hmac(self::ALGO, 'customer-identity:v1:tenant:'.$tenantId, $appKey, true);
    }
}
