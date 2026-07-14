<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Tenancy\Exceptions\TenantContextMissingException;
use App\Tenancy\Queue\TenantContextSmokeJob;
use App\Tenancy\TenantCache;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Exercises the SaaS core against REAL infrastructure (PostgreSQL + Redis cache + Redis
 * queue) — an open socket is not proof (rule 29, rule 30). Intended to run from a clean
 * checkout by scripts/runtime/verify-saas-core.sh. Uses only generated, non-sensitive
 * data and cleans up the cache keys it creates.
 */
final class VerifySaasCoreCommand extends Command
{
    protected $signature = 'aish:verify-saas-core';

    protected $description = 'Verify SaaS core tenant isolation against real PostgreSQL and Redis.';

    private int $failures = 0;

    public function handle(): int
    {
        $ctx = app(TenantContext::class);
        $cache = app(TenantCache::class);

        $a = Tenant::factory()->create(['name' => 'Verify A '.Str::random(4)]);
        $b = Tenant::factory()->create(['name' => 'Verify B '.Str::random(4)]);
        $mA = TenantMembership::factory()->create(['tenant_id' => $a->id]);
        $mB = TenantMembership::factory()->create(['tenant_id' => $b->id]);
        $key = 'verify:'.Str::random(8);

        // 1. Fail-closed: no context → query throws.
        $ctx->forget();
        try {
            Branch::query()->count();
            $this->bad('fail-closed query without context');
        } catch (TenantContextMissingException) {
            $this->ok('fail-closed query without context');
        }

        // 2. Real Redis cache isolation across tenants (no collision).
        $this->enter($ctx, $a, $mA);
        $cache->put($key, 'value-A', 120);
        $this->enter($ctx, $b, $mB);
        $cache->put($key, 'value-B', 120);
        $this->enter($ctx, $a, $mA);
        $this->assert($cache->get($key) === 'value-A', 'tenant A cache value isolated on Redis');
        $this->enter($ctx, $b, $mB);
        $this->assert($cache->get($key) === 'value-B', 'tenant B cache value isolated on Redis');

        // 3. Storage path prefixing + traversal rejection.
        $this->enter($ctx, $a, $mA);
        $storage = app(TenantStorage::class);
        $this->assert(str_starts_with($storage->path('x.txt'), 'tenants/'.$a->id.'/'), 'storage path is tenant-prefixed');
        try {
            $storage->path('../'.$b->id.'/escape.txt');
            $this->bad('storage traversal rejected');
        } catch (\Throwable) {
            $this->ok('storage traversal rejected');
        }

        // 4. Real Redis queue: job runs under the tenant it was dispatched from.
        $this->enter($ctx, $a, $mA);
        $qkey = 'queue:'.Str::random(8);
        TenantContextSmokeJob::dispatch($qkey);
        $ctx->forget();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        $this->call('queue:work', ['--once' => true, '--stop-when-empty' => true]);
        $this->enter($ctx, $a, $mA);
        $this->assert((int) $cache->get($qkey) === $a->id, 'queued job ran under the dispatching tenant');

        // Cleanup cache keys and the generated verification tenants (cascades memberships).
        $this->enter($ctx, $a, $mA);
        $cache->forget($key);
        $cache->forget($qkey);
        $this->enter($ctx, $b, $mB);
        $cache->forget($key);
        $ctx->forget();
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        Tenant::whereKey([$a->id, $b->id])->get()->each->delete();

        if ($this->failures > 0) {
            $this->error("SaaS core verification FAILED with {$this->failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('SaaS core verification passed against real PostgreSQL + Redis.');

        return self::SUCCESS;
    }

    private function enter(TenantContext $ctx, Tenant $tenant, TenantMembership $membership): void
    {
        $ctx->forget();
        $ctx->establish($tenant, $membership);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    }

    private function assert(bool $condition, string $label): void
    {
        $condition ? $this->ok($label) : $this->bad($label);
    }

    private function ok(string $label): void
    {
        $this->line("  <info>✓</info> {$label}");
    }

    private function bad(string $label): void
    {
        $this->failures++;
        $this->line("  <error>✗</error> {$label}");
    }
}
