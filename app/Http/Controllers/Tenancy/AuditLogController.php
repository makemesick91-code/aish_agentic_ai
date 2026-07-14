<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Read-only, tenant-scoped audit trail. The forTenant() scope plus AuditLogPolicy keep a
 * tenant from ever seeing another tenant's records (rule 07, rule 03).
 */
final class AuditLogController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::forTenant(app(TenantContext::class)->tenantId())
            ->with('actor')
            ->latest('created_at')
            ->paginate(30);

        return view('audit.index', compact('logs'));
    }
}
