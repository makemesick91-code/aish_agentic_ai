<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotificationDelivery;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;

/**
 * A user may only see and mutate their OWN in-app notifications, and only within the tenant
 * they are acting in. This blocks recipient-swap and delivery-record IDOR: the delivery is
 * not auto-scoped, so the policy is the enforcement point (rule 03, rule 31 §8.9).
 */
class NotificationDeliveryPolicy
{
    use ChecksTenantScope;

    public function viewAny(User $user): bool
    {
        // Any authenticated tenant user may view their own inbox.
        return true;
    }

    public function view(User $user, NotificationDelivery $delivery): bool
    {
        return $this->ownedByCurrentActor($user, $delivery);
    }

    public function markRead(User $user, NotificationDelivery $delivery): bool
    {
        return $this->ownedByCurrentActor($user, $delivery);
    }

    private function ownedByCurrentActor(User $user, NotificationDelivery $delivery): bool
    {
        return (int) $delivery->recipient_id === (int) $user->id
            && $this->inCurrentTenant($delivery);
    }
}
