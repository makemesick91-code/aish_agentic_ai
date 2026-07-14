<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Authorization\Roles;
use App\Models\NotificationDelivery;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * The in-app inbox is scoped to the current tenant AND the acting recipient. Mark-as-read
 * re-verifies ownership, blocking recipient-swap and delivery IDOR (rule 31 §8.9).
 */
final class NotificationInboxTest extends TestCase
{
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_inbox_shows_only_the_actors_own_current_tenant_in_app_notifications(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        [$other] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $otherTenant = $this->provisionTenant();

        NotificationDelivery::factory()->create(['tenant_id' => $tenant->id, 'recipient_id' => $user->id, 'subject' => 'Mine here']);
        NotificationDelivery::factory()->create(['tenant_id' => $otherTenant->id, 'recipient_id' => $user->id, 'subject' => 'Other tenant']);
        NotificationDelivery::factory()->create(['tenant_id' => $tenant->id, 'recipient_id' => $other->id, 'subject' => 'Someone else']);

        $response = $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])->get('/notifications');

        $response->assertOk();
        $response->assertSee('Mine here');
        $response->assertDontSee('Other tenant');
        $response->assertDontSee('Someone else');
    }

    public function test_a_user_can_mark_their_own_notification_read(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $delivery = NotificationDelivery::factory()->unread()->create(['tenant_id' => $tenant->id, 'recipient_id' => $user->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->patch("/notifications/{$delivery->ulid}/read")
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($delivery->fresh()->read_at);
    }

    public function test_a_user_cannot_mark_another_users_notification_read(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        [$other] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $delivery = NotificationDelivery::factory()->unread()->create(['tenant_id' => $tenant->id, 'recipient_id' => $other->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->patch("/notifications/{$delivery->ulid}/read")
            ->assertForbidden();

        $this->assertNull($delivery->fresh()->read_at);
    }

    public function test_a_user_cannot_mark_a_delivery_from_another_tenant_read(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        $otherTenant = $this->provisionTenant();
        // Same recipient id, but the delivery belongs to a different tenant context.
        $delivery = NotificationDelivery::factory()->unread()->create(['tenant_id' => $otherTenant->id, 'recipient_id' => $user->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->patch("/notifications/{$delivery->ulid}/read")
            ->assertForbidden();
    }

    public function test_mark_all_read_only_affects_own_current_tenant_notifications(): void
    {
        $tenant = $this->provisionTenant();
        [$user] = $this->memberWithRole($tenant, Roles::READ_ONLY);
        [$other] = $this->memberWithRole($tenant, Roles::READ_ONLY);

        $mine = NotificationDelivery::factory()->unread()->create(['tenant_id' => $tenant->id, 'recipient_id' => $user->id]);
        $theirs = NotificationDelivery::factory()->unread()->create(['tenant_id' => $tenant->id, 'recipient_id' => $other->id]);

        $this->actingAs($user)->withSession(['current_tenant_id' => $tenant->id])
            ->patch('/notifications/read-all')
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($mine->fresh()->read_at);
        $this->assertNull($theirs->fresh()->read_at);
    }
}
