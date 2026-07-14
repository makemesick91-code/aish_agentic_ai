<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use ReflectionClass;
use Tests\TestCase;

/**
 * Tooling-free fitness functions for the Step 6 tenancy boundary (rule 03, rule 20,
 * rule 30). Reflection + file scans, in the style of FoundationBoundariesTest, so the
 * first regression fails a test rather than review.
 */
final class TenancyBoundariesTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function phpFiles(string $relativeDir): array
    {
        $base = base_path($relativeDir);
        if (! is_dir($base)) {
            return [];
        }

        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function relativePath(string $absolute): string
    {
        return ltrim(str_replace('\\', '/', str_replace(base_path(), '', $absolute)), '/');
    }

    public function test_every_tenant_owned_model_uses_the_belongs_to_tenant_trait(): void
    {
        $checked = 0;

        foreach ($this->phpFiles('app/Models') as $file) {
            $class = 'App\\Models\\'.pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            if (! $reflection->implementsInterface(TenantOwned::class) || $reflection->isAbstract()) {
                continue;
            }

            $checked++;

            $this->assertContains(
                BelongsToTenant::class,
                class_uses_recursive($class),
                "{$class} implements TenantOwned but does not use the BelongsToTenant trait.",
            );

            $model = $reflection->newInstanceWithoutConstructor();
            $this->assertSame(
                'tenant_id',
                $model->tenantKeyName(),
                "{$class} must own its rows through a tenant_id key.",
            );
            $this->assertContains(
                'tenant_id',
                $model->getFillable(),
                "{$class} must expose tenant_id so it is stamped/owned per tenant.",
            );
        }

        // Guard against a silently-empty scan (e.g. models moved).
        $this->assertGreaterThanOrEqual(5, $checked, 'Expected the tenant-owned model set to be non-trivial.');
    }

    public function test_tenant_scope_is_only_bypassed_in_allowlisted_infrastructure(): void
    {
        // Legitimate places that MUST bypass the fail-closed TenantScope: the context is
        // established before it exists (middleware/provisioning), or the record is not
        // tenant-scoped by design. Any other bypass is a tenant-isolation hole.
        $allowedExact = [
            'app/Models/User.php',
            'app/Http/Middleware/ResolveTenantContext.php',
            'app/Console/Commands/VerifySaasCoreCommand.php',
        ];
        $allowedPrefixes = [
            'app/Tenancy/',
            'app/Services/Tenancy/',
        ];

        $violations = [];

        foreach ($this->phpFiles('app') as $file) {
            $contents = (string) file_get_contents($file);
            if (! str_contains($contents, 'withoutGlobalScope(TenantScope')) {
                continue;
            }

            $relative = $this->relativePath($file);

            $allowed = in_array($relative, $allowedExact, true);
            foreach ($allowedPrefixes as $prefix) {
                $allowed = $allowed || str_starts_with($relative, $prefix);
            }

            if (! $allowed) {
                $violations[] = $relative;
            }
        }

        $this->assertSame(
            [],
            $violations,
            'withoutGlobalScope(TenantScope) must only appear in allowlisted tenancy infrastructure.',
        );
    }

    public function test_controllers_live_only_under_the_http_controllers_namespace(): void
    {
        foreach ($this->phpFiles('app') as $file) {
            if (str_ends_with($file, 'Controller.php')) {
                $this->assertStringContainsString(
                    'app/Http/Controllers',
                    str_replace('\\', '/', $file),
                    "Controller must live under app/Http/Controllers: {$file}",
                );
            }
        }
    }
}
