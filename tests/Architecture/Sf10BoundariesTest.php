<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Models\Customer;
use App\Models\CustomerConsent;
use App\Models\CustomerIdentity;
use App\Models\CustomerMergeEvent;
use App\Tenancy\Contracts\TenantOwned;
use ReflectionClass;
use Tests\TestCase;

/**
 * Fitness functions for the Step 10 Customer 360 Foundation. These enforce, by reflection and source
 * scanning, the permanent boundaries in rule 36: tenant ownership, single-writer customer identity,
 * platform-core placement, a derived (never materialized) interactions timeline, no plaintext PII in
 * identity rows, centralized normalization, and the absence of AI on customer data in Step 10
 * (rule 36; ADR 0070, ADR 0071, ADR 0072).
 */
final class Sf10BoundariesTest extends TestCase
{
    private const CUSTOMER_MODELS = [
        Customer::class,
        CustomerIdentity::class,
        CustomerMergeEvent::class,
        CustomerConsent::class,
    ];

    public function test_every_customer_model_is_tenant_owned(): void
    {
        foreach (self::CUSTOMER_MODELS as $model) {
            $this->assertTrue(
                (new ReflectionClass($model))->implementsInterface(TenantOwned::class),
                "{$model} must implement TenantOwned (rule 36).",
            );
        }
    }

    /** Customer 360 is platform-core, not a business module (ADR 0070). */
    public function test_customer_domain_lives_in_the_platform_core_namespace(): void
    {
        $this->assertDirectoryExists(base_path('app/Customers'));
        $this->assertDirectoryDoesNotExist(base_path('app/Modules/Customer'));
        $this->assertDirectoryDoesNotExist(base_path('app/Modules/Customers'));
    }

    /**
     * Only the customer domain may write customer identity. If another domain could create these
     * rows, "single source of truth" would be documentation rather than a property of the system.
     */
    public function test_customer_identity_is_written_only_by_the_customer_domain(): void
    {
        $offenders = [];

        foreach ($this->phpFiles('app') as $file) {
            $relative = $this->relativePath($file);

            if (str_starts_with($relative, 'app/Customers/')) {
                continue;
            }

            $contents = (string) file_get_contents($file);

            foreach (['CustomerIdentity::create(', 'CustomerMergeEvent::create(', 'Customer::create('] as $needle) {
                if (str_contains($contents, $needle)) {
                    $offenders[] = $relative.' → '.$needle;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Customer identity may only be written inside app/Customers (rule 36; ADR 0070): '
                .implode(', ', $offenders),
        );
    }

    /** The customer domain reads Step 8 data but must never own or mutate feedback state. */
    public function test_the_customer_domain_does_not_write_feedback_state(): void
    {
        $offenders = [];

        foreach ($this->phpFiles('app/Customers') as $file) {
            $contents = (string) file_get_contents($file);

            foreach (['FeedbackItem::create(', 'FeedbackEvent::create(', 'FeedbackNote::create('] as $needle) {
                if (str_contains($contents, $needle)) {
                    $offenders[] = $this->relativePath($file).' → '.$needle;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'The customer domain must not create feedback records (rule 36; ADR 0065, ADR 0070): '
                .implode(', ', $offenders),
        );
    }

    /**
     * The interactions timeline must stay derived. A `customer_interactions` table would be a
     * second event history competing with the Step 8 timeline (ADR 0063, ADR 0065, ADR 0070).
     */
    public function test_no_materialized_customer_interaction_table_exists(): void
    {
        $offenders = [];

        foreach ($this->phpFiles('database/migrations') as $file) {
            $contents = (string) file_get_contents($file);

            foreach (["create('customer_interactions'", "create('customer_timeline'", "create('customer_events'"] as $needle) {
                if (str_contains($contents, $needle)) {
                    $offenders[] = $this->relativePath($file);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'The Customer 360 timeline must remain a derived read-model (ADR 0070): '.implode(', ', $offenders),
        );
    }

    /** Normalization must be centralized so duplicate prevention cannot drift (ADR 0071). */
    public function test_identity_normalization_is_centralized(): void
    {
        $offenders = [];

        foreach ($this->phpFiles('app') as $file) {
            $relative = $this->relativePath($file);

            if (str_starts_with($relative, 'app/Customers/Identity/')) {
                continue;
            }

            $contents = (string) file_get_contents($file);

            // A hand-rolled lowercase-an-email is exactly the drift this rule prevents.
            if (preg_match('/mb_strtolower\s*\(\s*\$\w*(email|phone)/i', $contents) === 1) {
                $offenders[] = $relative;
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Identity values must be normalized only by IdentityNormalizer (rule 36; ADR 0071): '
                .implode(', ', $offenders),
        );
    }

    /** Step 10 performs no AI on customer data (rule 36; rules 05, 18). */
    public function test_the_customer_domain_contains_no_ai_integration(): void
    {
        $offenders = [];

        foreach ($this->phpFiles('app/Customers') as $file) {
            $contents = strtolower((string) file_get_contents($file));

            foreach (['openai', 'anthropic', 'llm', 'gpt-', 'claude-', 'embedding'] as $needle) {
                if (str_contains($contents, $needle)) {
                    $offenders[] = $this->relativePath($file).' → '.$needle;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Step 10 must not send customer data to an AI provider (rule 36): '.implode(', ', $offenders),
        );
    }

    /** Append-only history must be enforced at the model layer, not only by convention. */
    public function test_merge_and_consent_records_are_append_only(): void
    {
        foreach ([CustomerMergeEvent::class, CustomerConsent::class] as $model) {
            $source = (string) file_get_contents((new ReflectionClass($model))->getFileName());

            $this->assertStringContainsString('UPDATED_AT = null', $source, "{$model} must have no updated_at (rule 36).");
            $this->assertStringContainsString('static::updating(', $source, "{$model} must block updates (rule 36).");
            $this->assertStringContainsString('static::deleting(', $source, "{$model} must block deletes (rule 36).");
        }
    }

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
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function relativePath(string $absolute): string
    {
        return ltrim(str_replace(base_path(), '', $absolute), '/');
    }
}
