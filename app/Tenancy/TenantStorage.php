<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Tenancy\Exceptions\CrossTenantAccessException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Tenant-aware storage. Every path is rooted under tenants/{tenant}/ (optionally
 * .../branches/{branch}/). Path traversal and absolute paths are rejected so a caller
 * can never escape its tenant root, and files default to private visibility
 * (rule 03, rule 04, rule 30). No real business data is stored in Step 6.
 */
final class TenantStorage
{
    public function __construct(private readonly TenantContext $context) {}

    public function path(string $path): string
    {
        return 'tenants/'.$this->context->tenantId().'/'.$this->normalize($path);
    }

    public function branchPath(string $path): string
    {
        $branch = $this->context->branchId();

        if ($branch === null) {
            throw new CrossTenantAccessException('A branch context is required for a branch-scoped storage path.');
        }

        return 'tenants/'.$this->context->tenantId().'/branches/'.$branch.'/'.$this->normalize($path);
    }

    public function put(string $path, string $contents): bool
    {
        return $this->disk()->put($this->path($path), $contents, ['visibility' => 'private']);
    }

    public function get(string $path): ?string
    {
        return $this->disk()->get($this->path($path));
    }

    public function exists(string $path): bool
    {
        return $this->disk()->exists($this->path($path));
    }

    public function delete(string $path): bool
    {
        return $this->disk()->delete($this->path($path));
    }

    public function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.default'));
    }

    private function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, '/')) {
            throw new CrossTenantAccessException('Absolute paths are not permitted in tenant storage.');
        }

        // Reject any traversal segment before it can escape the tenant root.
        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                throw new CrossTenantAccessException('Path traversal is not permitted in tenant storage.');
            }
        }

        return ltrim($path, '/');
    }
}
