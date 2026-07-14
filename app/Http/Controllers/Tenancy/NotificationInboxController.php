<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The tenant user's in-app notification inbox. Every query is explicitly scoped to the
 * current tenant AND the acting recipient — deliveries are not auto-scoped, so this is the
 * isolation boundary. Mark-as-read re-verifies ownership via the policy (rule 31 §8.9).
 */
final class NotificationInboxController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, TenantContext $context): View
    {
        $this->authorize('viewAny', NotificationDelivery::class);

        $userId = (int) $request->user()->id;

        $notifications = NotificationDelivery::query()
            ->forTenant($context->tenantId())
            ->forRecipient($userId)
            ->inApp()
            ->latest()
            ->paginate(20);

        $unreadCount = NotificationDelivery::query()
            ->forTenant($context->tenantId())
            ->forRecipient($userId)
            ->inApp()
            ->unread()
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Request $request, NotificationDelivery $delivery): RedirectResponse
    {
        $this->authorize('markRead', $delivery);

        if (! $delivery->isRead()) {
            $delivery->forceFill(['read_at' => now()])->save();
        }

        return redirect()->route('notifications.index')->with('status', __('Notification marked as read.'));
    }

    public function markAllRead(Request $request, TenantContext $context): RedirectResponse
    {
        $this->authorize('viewAny', NotificationDelivery::class);

        NotificationDelivery::query()
            ->forTenant($context->tenantId())
            ->forRecipient((int) $request->user()->id)
            ->inApp()
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('status', __('All notifications marked as read.'));
    }
}
