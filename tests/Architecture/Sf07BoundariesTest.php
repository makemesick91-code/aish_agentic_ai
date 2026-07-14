<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyVersion;
use App\Tenancy\Contracts\TenantOwned;
use ReflectionClass;
use Tests\TestCase;

/**
 * Step 7 fitness functions (machine enforcement). These prevent the survey foundation from
 * drifting: scoring stays centralized, tokens are never persisted in plaintext, every
 * tenant-owned survey model carries the tenant contract, tenant controllers always authorize,
 * entitlement decisions go through one guard, and no public route exposes draft content
 * (rule 32; Step 7 §30).
 */
final class Sf07BoundariesTest extends TestCase
{
    private const SURVEY_MODELS = [
        Survey::class, SurveyVersion::class, SurveyQuestion::class, SurveyOption::class,
        SurveyCampaign::class, SurveyInvitation::class, SurveyResponse::class, SurveyAnswer::class,
    ];

    public function test_every_survey_model_is_tenant_owned(): void
    {
        foreach (self::SURVEY_MODELS as $model) {
            $this->assertTrue(
                (new ReflectionClass($model))->implementsInterface(TenantOwned::class),
                "{$model} must implement TenantOwned",
            );
        }
    }

    public function test_metric_scoring_math_lives_only_in_the_calculator(): void
    {
        foreach ($this->phpFiles('app') as $file) {
            $contents = (string) file_get_contents($file);
            if (stripos($contents, 'detractor') !== false || stripos($contents, 'promoter') !== false) {
                $this->assertStringStartsWith(
                    'app/Surveys/Scoring/',
                    $this->relativePath($file),
                    'NPS scoring math must live only under app/Surveys/Scoring/ (rule 32).',
                );
            }
        }
    }

    public function test_survey_invitation_never_persists_a_plaintext_token(): void
    {
        $invitation = new SurveyInvitation;

        $this->assertNotContains('token', $invitation->getFillable(), 'A plaintext token column must not be fillable.');
        $this->assertContains('token_hash', $invitation->getFillable());
        $this->assertContains('token_hash', $invitation->getHidden(), 'token_hash must be hidden from serialization.');
    }

    public function test_every_tenant_survey_controller_authorizes(): void
    {
        foreach ($this->phpFiles('app/Http/Controllers/Tenancy/Survey') as $file) {
            $contents = (string) file_get_contents($file);
            $this->assertStringContainsString(
                '$this->authorize(',
                $contents,
                $this->relativePath($file).' must authorize server-side (rule 32 §27).',
            );
        }
    }

    public function test_survey_entitlement_decisions_go_through_one_guard(): void
    {
        foreach ($this->phpFiles('app/Surveys') as $file) {
            $contents = (string) file_get_contents($file);
            if (str_contains($contents, 'EntitlementResolver')) {
                $this->assertSame(
                    'app/Surveys/SurveyEntitlements.php',
                    $this->relativePath($file),
                    'Survey entitlement decisions must go only through SurveyEntitlements (rule 32 §23).',
                );
            }
        }
    }

    public function test_no_public_survey_route_exposes_draft_or_preview(): void
    {
        $routes = collect(app('router')->getRoutes())->filter(
            fn ($r) => str_starts_with((string) $r->getName(), 'survey.public.')
        );

        $this->assertNotEmpty($routes);
        foreach ($routes as $route) {
            $this->assertStringNotContainsStringIgnoringCase('preview', $route->uri());
            $this->assertStringNotContainsStringIgnoringCase('draft', $route->uri());
            $this->assertStringNotContainsString('auth', implode(',', $route->gatherMiddleware()),
                'A public survey route must not sit behind auth, and must never expose draft content.');
        }
    }

    /** @return list<string> */
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
}
