<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Platform\PlatformPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Platform audit view. Shows platform-plane events only (platform.*, plan.*, subscription.*).
 * Metadata is already sanitised by the recorder — never secrets, tokens, or message bodies
 * (rule 07, rule 31 §11).
 */
final class PlatformAuditController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize(PlatformPermissions::AUDIT_VIEW);

        $events = AuditLog::query()
            ->with('actor')
            ->where(function ($query): void {
                $query->where('event', 'like', 'platform.%')
                    ->orWhere('event', 'like', 'plan.%')
                    ->orWhere('event', 'like', 'subscription.%');
            })
            ->latest()
            ->paginate(30);

        return view('platform.audit.index', compact('events'));
    }
}
