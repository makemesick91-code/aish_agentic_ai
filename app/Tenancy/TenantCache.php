<?php

declare(strict_types=1);

namespace App\Tenancy;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Tenant-aware cache facade. Every key is namespaced with the current tenant (and,
 * optionally, branch) id so the same logical key never collides across tenants. Reading
 * or writing a tenant cache key with no context fails closed via TenantContext (rule 03,
 * rule 30). Never call Cache::flush()/FLUSHDB as application behaviour.
 */
final class TenantCache
{
    public function __construct(private readonly TenantContext $context) {}

    public function key(string $key): string
    {
        return 'tenant:'.$this->context->tenantId().':'.$this->normalize($key);
    }

    public function branchKey(string $key): string
    {
        $branch = $this->context->branchId() ?? 'none';

        return 'tenant:'.$this->context->tenantId().':branch:'.$branch.':'.$this->normalize($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($this->key($key), $default);
    }

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): bool
    {
        return $ttlSeconds !== null
            ? $this->store()->put($this->key($key), $value, $ttlSeconds)
            : $this->store()->forever($this->key($key), $value);
    }

    /**
     * @param  Closure(): mixed  $callback
     */
    public function remember(string $key, int $ttlSeconds, Closure $callback): mixed
    {
        return $this->store()->remember($this->key($key), $ttlSeconds, $callback);
    }

    public function forget(string $key): bool
    {
        return $this->store()->forget($this->key($key));
    }

    private function store(): Repository
    {
        return Cache::store();
    }

    private function normalize(string $key): string
    {
        // Keep keys stable and driver-safe; never embed raw PII in a key.
        $key = trim($key);

        return preg_replace('/\s+/', '_', $key) ?? $key;
    }
}
