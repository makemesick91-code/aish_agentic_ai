<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Audit\AuditRecorder;
use App\Authorization\Roles;
use App\Enums\FeedbackExportStatus;
use App\Feedback\Exceptions\EntitlementDeniedException;
use App\Feedback\Export\FeedbackExportService;
use App\Feedback\Export\FeedbackExportWriter;
use App\Jobs\Feedback\ProcessFeedbackExportJob;
use App\Models\FeedbackExport;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use App\Subscriptions\EntitlementKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsFeedbackPlan;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

final class FeedbackExportTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsFeedbackPlan;
    use ProvisionsTenants;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->tenant = $this->provisionTenant();
        $this->provisionFeedbackPlan($this->tenant);
        [$this->owner, $membership] = $this->memberWithRole($this->tenant, Roles::BUSINESS_OWNER);
        $this->establishTenantContext($this->tenant, $membership);
    }

    private function service(): FeedbackExportService
    {
        return app(FeedbackExportService::class);
    }

    public function test_request_creates_pending_export_meters_and_dispatches(): void
    {
        Queue::fake();

        $export = $this->service()->request([], $this->owner, includeContent: true);

        $this->assertSame(FeedbackExportStatus::Pending, $export->status);
        $this->assertTrue($export->includes_content);
        $this->assertDatabaseHas('usage_records', ['tenant_id' => $this->tenant->id, 'meter_key' => 'feedback_exports.created']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.export.requested']);
        Queue::assertPushed(ProcessFeedbackExportJob::class);
    }

    public function test_request_is_idempotent_per_day(): void
    {
        Queue::fake();

        $a = $this->service()->request(['statuses' => ['new']], $this->owner, includeContent: false);
        $b = $this->service()->request(['statuses' => ['new']], $this->owner, includeContent: false);

        $this->assertTrue($a->is($b));
        $this->assertDatabaseCount('feedback_exports', 1);
        $this->assertDatabaseCount('usage_records', 1);
    }

    public function test_request_denied_without_entitlement(): void
    {
        $this->endRequestScope();
        $tenant = $this->provisionTenant();
        $this->provisionFeedbackPlan($tenant, [EntitlementKeys::FEEDBACK_EXPORTS_ENABLED => false]);
        [$owner, $membership] = $this->memberWithRole($tenant, Roles::BUSINESS_OWNER);
        $this->establishTenantContext($tenant, $membership);

        $this->expectException(EntitlementDeniedException::class);
        $this->service()->request([], $owner, includeContent: false);
    }

    public function test_job_generates_ready_csv_and_escapes_formula_injection(): void
    {
        FeedbackItem::factory()->create(['search_content' => '=HYPERLINK("http://evil","x")']);
        $export = $this->makeExport(includeContent: true);

        $this->runJob($export);

        $fresh = $export->fresh();
        $this->assertSame(FeedbackExportStatus::Ready, $fresh->status);
        $this->assertNotNull($fresh->ready_at);
        $this->assertNotNull($fresh->expires_at);
        Storage::disk('local')->assertExists($fresh->path);

        $csv = Storage::disk('local')->get($fresh->path);
        $this->assertStringContainsString('response_text', $csv);      // content column present
        $this->assertStringContainsString("'=HYPERLINK", $csv);        // formula neutralized
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);     // never a bare leading =
    }

    public function test_export_without_content_permission_excludes_response_text(): void
    {
        FeedbackItem::factory()->create(['search_content' => 'SECRET-RESPONSE-TEXT']);
        $export = $this->makeExport(includeContent: false);

        $this->runJob($export);

        $csv = Storage::disk('local')->get($export->fresh()->path);
        $this->assertStringNotContainsString('response_text', $csv);
        $this->assertStringNotContainsString('SECRET-RESPONSE-TEXT', $csv);
    }

    private function makeExport(bool $includeContent): FeedbackExport
    {
        Queue::fake();

        return $this->service()->request([], $this->owner, includeContent: $includeContent);
    }

    private function runJob(FeedbackExport $export): void
    {
        (new ProcessFeedbackExportJob($export->id))->handle(
            app(FeedbackExportWriter::class),
            app(NotificationDispatcher::class),
            app(AuditRecorder::class),
        );
    }
}
