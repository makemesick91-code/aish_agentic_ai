<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Platform\PlatformPermissions;
use App\Platform\SupportNoteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Creates append-oriented platform support notes for a tenant. Requires support-notes.manage.
 * Notes must not contain customer/medical data or secrets, and are audited (rule 31 §10.9).
 */
final class SupportNoteController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Tenant $tenant, SupportNoteService $notes): RedirectResponse
    {
        $this->authorize(PlatformPermissions::SUPPORT_NOTES_MANAGE);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $notes->create($tenant, $request->user(), $validated['body']);

        return redirect()->route('platform.tenants.show', $tenant)->with('status', __('Support note added.'));
    }
}
