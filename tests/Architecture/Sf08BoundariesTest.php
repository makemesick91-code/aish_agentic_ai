<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Models\FeedbackAssignment;
use App\Models\FeedbackAttachment;
use App\Models\FeedbackEvent;
use App\Models\FeedbackExport;
use App\Models\FeedbackItem;
use App\Models\FeedbackItemTag;
use App\Models\FeedbackNote;
use App\Models\FeedbackTag;
use App\Tenancy\Contracts\TenantOwned;
use ReflectionClass;
use Tests\TestCase;

/**
 * Fitness functions for the Step 8 Feedback Operations Foundation. These enforce, by reflection and
 * source scanning, the permanent boundaries in rule 33: tenant ownership, a single projection writer,
 * a single scoring source, policy-guarded controllers, append-only records, private attachments, the
 * single entitlement resolver, and the absence of AI / recovery in Step 8 (rule 33; Step 8 §30).
 */
final class Sf08BoundariesTest extends TestCase
{
    private const FEEDBACK_MODELS = [
        FeedbackItem::class,
        FeedbackEvent::class,
        FeedbackAssignment::class,
        FeedbackTag::class,
        FeedbackItemTag::class,
        FeedbackNote::class,
        FeedbackAttachment::class,
        FeedbackExport::class,
    ];

    public function test_every_feedback_model_is_tenant_owned(): void
    {
        foreach (self::FEEDBACK_MODELS as $model) {
            $this->assertTrue(
                (new ReflectionClass($model))->implementsInterface(TenantOwned::class),
                "{$model} must implement TenantOwned (rule 33).",
            );
        }
    }

    public function test_feedback_item_is_created_only_by_the_projector(): void
    {
        $offenders = [];
        foreach ($this->phpFiles('app') as $file) {
            $contents = (string) file_get_contents($file);
            if (str_contains($contents, 'FeedbackItem::create(')) {
                $offenders[] = $this->relativePath($file);
            }
        }

        $this->assertSame(
            ['app/Feedback/FeedbackProjector.php'],
            $offenders,
            'FeedbackItem::create() must appear only in the approved projector (rule 33 §9).',
        );
    }

    public function test_no_second_scoring_implementation_in_feedback(): void
    {
        foreach ($this->phpFiles('app/Feedback') as $file) {
            $contents = strtolower((string) file_get_contents($file));
            foreach (['detractor', 'promoter', 'passives'] as $needle) {
                $this->assertStringNotContainsString(
                    $needle,
                    $contents,
                    'Feedback must reuse the canonical MetricCalculator, never re-implement scoring (rule 33 §9).',
                );
            }
        }
    }

    public function test_entitlement_resolver_is_used_only_by_the_feedback_facade(): void
    {
        $offenders = [];
        foreach ($this->phpFiles('app/Feedback') as $file) {
            $contents = (string) file_get_contents($file);
            if (str_contains($contents, 'EntitlementResolver') && ! str_ends_with($file, 'FeedbackEntitlements.php')) {
                $offenders[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $offenders, 'Feedback entitlement decisions must go through FeedbackEntitlements (rule 33 §22).');
    }

    public function test_every_feedback_controller_authorizes(): void
    {
        foreach ($this->phpFiles('app/Http/Controllers/Tenancy/Feedback') as $file) {
            $contents = (string) file_get_contents($file);
            $this->assertStringContainsString(
                '$this->authorize(',
                $contents,
                $this->relativePath($file).' must authorize server-side (rule 33 §21).',
            );
        }
    }

    public function test_append_only_models_have_no_updated_at(): void
    {
        foreach ([FeedbackEvent::class, FeedbackAssignment::class, FeedbackNote::class, FeedbackItemTag::class] as $model) {
            $this->assertNull($model::UPDATED_AT, "{$model} must be append-oriented (no updated_at) (rule 33).");
        }
    }

    public function test_attachments_never_use_a_public_disk(): void
    {
        $contents = (string) file_get_contents(base_path('app/Feedback/FeedbackAttachmentService.php'));
        $this->assertStringNotContainsString("'public'", $contents, 'Attachments must use a private disk (rule 33 §14).');
        $this->assertStringContainsString("DISK = 'local'", $contents);
    }

    public function test_no_ai_or_recovery_surface_in_feedback(): void
    {
        // Code-identifier needles (not prose) so documentation of what is out of scope does not trip.
        foreach ($this->phpFiles('app/Feedback') as $file) {
            $contents = strtolower((string) file_get_contents($file));
            foreach (['sentiment_score', 'severity_score', 'ai_sentiment', 'recoveryticket', 'recovery_ticket', 'ai_analysis'] as $needle) {
                $this->assertStringNotContainsString(
                    $needle,
                    $contents,
                    'AI analysis and Customer Recovery are out of scope for Step 8 (rule 33 §26).',
                );
            }
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
