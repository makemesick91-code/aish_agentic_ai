<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * The foundation dashboard. It shows only real context (workspace, branch, user, roles)
 * and truthful "not implemented" markers for future modules — no fabricated metrics or
 * data (rule 10, rule 27).
 */
final class FoundationDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $context = app(TenantContext::class);

        // Capabilities that genuinely exist in this foundation step.
        $foundationStatus = [
            'Authentication & invitations' => true,
            'Tenant & branch context' => true,
            'Roles & permissions' => true,
            'Audit trail' => true,
        ];

        // Planned modules, honestly marked as not built or measured yet.
        $notImplemented = [
            'Surveys & CSAT / NPS / CES',
            'Feedback inbox & AI analysis',
            'Recovery tickets & SLA',
            'Google Review management',
            'Analytics & reporting',
            'Billing & subscriptions',
            'Deployment & pilot readiness',
            'Production readiness',
        ];

        return view('dashboard', [
            'tenant' => $context->tenant(),
            'branch' => $context->hasBranch() ? $context->branch() : null,
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(),
            'foundationStatus' => $foundationStatus,
            'notImplemented' => $notImplemented,
        ]);
    }
}
