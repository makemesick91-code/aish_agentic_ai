<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Platform\PlatformPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Platform-wide notification delivery health. Shows delivery state counts and recent deliveries
 * by subject/state/tenant/failure-code only — never message bodies, PII, or secrets
 * (rule 31 §8.6, §10.6).
 */
final class NotificationHealthController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize(PlatformPermissions::NOTIFICATIONS_VIEW_HEALTH);

        $stateCounts = NotificationDelivery::query()
            ->selectRaw('state, count(*) as aggregate')
            ->groupBy('state')
            ->pluck('aggregate', 'state')
            ->all();

        $recent = NotificationDelivery::query()
            ->latest()
            ->paginate(25, ['id', 'ulid', 'tenant_id', 'type', 'channel', 'state', 'failure_code', 'created_at']);

        return view('platform.notifications.index', compact('stateCounts', 'recent'));
    }
}
