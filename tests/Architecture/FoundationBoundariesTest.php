<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Support\Health\HealthCheck;
use Tests\TestCase;

/**
 * Lightweight, tooling-free architecture fitness checks for the Step 5 foundation
 * (rule 20; ARCHITECTURE_FITNESS_FUNCTIONS). These grow as modules land; today they
 * lock in the boundaries that already exist so a regression fails a test, not review.
 */
final class FoundationBoundariesTest extends TestCase
{
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

    public function test_shared_kernel_does_not_depend_on_any_module(): void
    {
        // FF-MOD-04: the Shared Kernel must depend on no module.
        $violations = [];
        foreach ($this->phpFiles('app/Shared') as $file) {
            if (str_contains((string) file_get_contents($file), 'App\\Modules\\')) {
                $violations[] = $file;
            }
        }

        $this->assertSame([], $violations, 'Shared Kernel must not reference App\\Modules');
    }

    public function test_no_module_writes_another_modules_namespace(): void
    {
        // FF-MOD-01 (foundation form): a module file must not reference a *different*
        // module's namespace directly. Empty until modules land; the guard exists now
        // so the first violation fails a test rather than review.
        $violations = [];
        foreach ($this->phpFiles('app/Modules') as $file) {
            $module = basename(dirname($file));
            if (preg_match_all('/App\\\\Modules\\\\([A-Za-z0-9_]+)/', (string) file_get_contents($file), $m)) {
                foreach ($m[1] as $referenced) {
                    if ($referenced !== $module) {
                        $violations[] = "{$module} -> {$referenced} ({$file})";
                    }
                }
            }
        }

        $this->assertSame([], $violations, 'A module must not reference another module directly');
    }

    public function test_all_readiness_checks_implement_the_health_check_contract(): void
    {
        foreach (config('health.readiness', []) as $class) {
            $this->assertTrue(
                is_subclass_of($class, HealthCheck::class),
                "{$class} must implement ".HealthCheck::class,
            );
        }
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
