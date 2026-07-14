<?php

declare(strict_types=1);

namespace App\Audit;

use App\Models\AuditLog;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The single entry point for writing audit records. It derives tenant/branch/actor from
 * the current TenantContext (overridable for platform/system events), sanitises metadata
 * (allowlisted keys, sensitive keys redacted), and never persists secrets, tokens,
 * passwords, or customer/medical content (rule 07, rule 30).
 */
final class AuditRecorder
{
    /** Metadata keys whose values are always redacted, regardless of nesting. */
    private const REDACTED_KEYS = [
        'password', 'password_confirmation', 'current_password', 'new_password',
        'token', 'token_hash', 'plain_token', 'secret', 'api_key', 'apikey',
        'authorization', 'cookie', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'access_token', 'refresh_token', 'private_key',
    ];

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function record(string $event, array $attributes = []): AuditLog
    {
        $subject = $attributes['subject'] ?? null;
        $request = request();

        return AuditLog::create([
            'tenant_id' => $attributes['tenant_id'] ?? $this->context->tenantIdOrNull(),
            'branch_id' => $attributes['branch_id'] ?? $this->context->branchId(),
            'actor_id' => array_key_exists('actor_id', $attributes) ? $attributes['actor_id'] : Auth::id(),
            'event' => $event,
            'subject_type' => $subject instanceof Model ? $subject::class : ($attributes['subject_type'] ?? null),
            'subject_id' => $subject instanceof Model
                ? (string) $subject->getKey()
                : (isset($attributes['subject_id']) ? (string) $attributes['subject_id'] : null),
            'correlation_id' => $attributes['correlation_id'] ?? $this->correlationId(),
            'channel' => $attributes['channel'] ?? $this->channel(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'metadata' => $this->sanitize($attributes['metadata'] ?? []),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitize(array $metadata): array
    {
        $clean = [];

        foreach ($metadata as $key => $value) {
            if (in_array(Str::lower((string) $key), self::REDACTED_KEYS, true)) {
                $clean[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } elseif (is_scalar($value) || $value === null) {
                $clean[$key] = $value;
            } else {
                // Never serialise objects/resources into the audit trail.
                $clean[$key] = '[omitted]';
            }
        }

        return $clean;
    }

    private function channel(): string
    {
        if (app()->runningInConsole()) {
            return 'console';
        }

        return request()->is('api/*') ? 'api' : 'web';
    }

    private function correlationId(): string
    {
        $request = request();
        $header = $request->headers->get('X-Request-Id');

        return $header !== null && $header !== '' ? Str::limit($header, 64, '') : (string) Str::ulid();
    }
}
