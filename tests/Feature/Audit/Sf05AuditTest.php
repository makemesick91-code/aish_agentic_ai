<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Audit\AuditRecorder;
use App\Models\PlatformSupportNote;
use App\Models\SubscriptionEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * SPRINT-SF-05 audit: sanitised metadata (no secrets), platform vs tenant context distinction,
 * and append-only history for the new event/support-note logs (rule 31 §11, §21).
 */
final class Sf05AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_metadata_redacts_secrets(): void
    {
        $log = app(AuditRecorder::class)->record('platform.role.assigned', [
            'tenant_id' => null,
            'metadata' => [
                'role' => 'admin',
                'password' => 'super-secret',
                'token' => 'abc123',
                'nested' => ['api_key' => 'k'],
            ],
        ]);

        $this->assertSame('[redacted]', $log->metadata['password']);
        $this->assertSame('[redacted]', $log->metadata['token']);
        $this->assertSame('[redacted]', $log->metadata['nested']['api_key']);
        $this->assertSame('admin', $log->metadata['role']);
    }

    public function test_platform_audit_carries_an_explicit_tenant_target_and_actor(): void
    {
        $tenant = Tenant::factory()->create();
        $actor = User::factory()->create();

        $log = app(AuditRecorder::class)->record('platform.tenant.suspended', [
            'tenant_id' => $tenant->id,
            'actor_id' => $actor->id,
            'subject' => $tenant,
            'metadata' => ['reason' => 'abuse'],
        ]);

        $this->assertSame($tenant->id, $log->tenant_id);
        $this->assertSame($actor->id, $log->actor_id);
        $this->assertSame(Tenant::class, $log->subject_type);
    }

    public function test_subscription_events_are_append_only(): void
    {
        $event = SubscriptionEvent::factory()->create();

        $this->expectException(RuntimeException::class);
        $event->update(['event' => 'tampered']);
    }

    public function test_support_notes_are_append_only(): void
    {
        $note = PlatformSupportNote::factory()->create();

        $this->expectException(RuntimeException::class);
        $note->update(['body' => 'tampered']);
    }

    public function test_support_notes_cannot_be_deleted(): void
    {
        $note = PlatformSupportNote::factory()->create();

        $this->expectException(RuntimeException::class);
        $note->delete();
    }
}
