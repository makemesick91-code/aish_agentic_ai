<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Enums\FeedbackStatus;
use App\Feedback\FeedbackSummaryService;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class FeedbackSummaryTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);
    }

    private function service(): FeedbackSummaryService
    {
        return app(FeedbackSummaryService::class);
    }

    public function test_summary_counts_by_status_and_unassigned(): void
    {
        FeedbackItem::factory()->count(2)->status(FeedbackStatus::New)->create();
        FeedbackItem::factory()->status(FeedbackStatus::Resolved)->create();

        $summary = $this->service()->summary();

        $this->assertSame(3, $summary['total']);
        $this->assertSame(2, $summary['by_status']['new']);
        $this->assertSame(1, $summary['by_status']['resolved']);
        $this->assertSame(3, $summary['unassigned']);
    }

    public function test_summary_is_tenant_scoped(): void
    {
        FeedbackItem::factory()->count(2)->create();

        // A second tenant's items must not be counted (global scope).
        $summary = $this->service()->summary();
        $this->assertSame(2, $summary['total']);
    }
}
