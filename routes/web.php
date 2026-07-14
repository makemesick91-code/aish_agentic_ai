<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\InvitationAcceptController;
use App\Http\Controllers\Platform\NotificationHealthController;
use App\Http\Controllers\Platform\PlanCatalogController;
use App\Http\Controllers\Platform\PlatformAuditController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Platform\SubscriptionDirectoryController;
use App\Http\Controllers\Platform\SupportNoteController;
use App\Http\Controllers\Platform\TenantDirectoryController;
use App\Http\Controllers\Tenancy\AuditLogController;
use App\Http\Controllers\Tenancy\BranchController;
use App\Http\Controllers\Tenancy\BranchSelectionController;
use App\Http\Controllers\Tenancy\FoundationDashboardController;
use App\Http\Controllers\Tenancy\InvitationController;
use App\Http\Controllers\Tenancy\MembershipController;
use App\Http\Controllers\Tenancy\NotificationInboxController;
use App\Http\Controllers\Tenancy\NotificationPreferenceController;
use App\Http\Controllers\Tenancy\SubscriptionOverviewController;
use App\Http\Controllers\Tenancy\TenantProfileController;
use App\Http\Controllers\Tenancy\TenantSelectionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/*
 * Invitation acceptance is intentionally public (token-authenticated) and carries NO
 * tenant context — a not-yet-member may be establishing their account (rule 30).
 */
Route::get('/invitations/{token}/accept', [InvitationAcceptController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('invitations.accept.show');
Route::post('/invitations/{token}/accept', [InvitationAcceptController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('invitations.accept.store');

/*
 * Authenticated but pre-tenant-context: choosing which workspace to act in. Requires an
 * active, verified account but not yet a tenant context.
 */
Route::middleware(['auth', 'verified', 'active'])->group(function (): void {
    Route::get('/tenant/select', [TenantSelectionController::class, 'index'])->name('tenant.select');
    Route::post('/tenant/select', [TenantSelectionController::class, 'store'])->name('tenant.select.store');
});

/*
 * Tenant-scoped surfaces: full stack (auth + verified + active + tenant + branch context).
 */
Route::middleware('tenant')->group(function (): void {
    Route::get('/dashboard', FoundationDashboardController::class)->name('dashboard');

    Route::get('/branch/select', [BranchSelectionController::class, 'index'])->name('branch.select');
    Route::post('/branch/select', [BranchSelectionController::class, 'store'])->name('branch.select.store');

    Route::get('/settings', [TenantProfileController::class, 'edit'])->name('tenant.edit');
    Route::put('/settings', [TenantProfileController::class, 'update'])->name('tenant.update');

    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::patch('/branches/{branch}/deactivate', [BranchController::class, 'deactivate'])->name('branches.deactivate');

    Route::get('/users', [MembershipController::class, 'index'])->name('users.index');
    Route::patch('/users/{membership}/suspend', [MembershipController::class, 'suspend'])->name('users.suspend');
    Route::patch('/users/{membership}/reactivate', [MembershipController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{membership}', [MembershipController::class, 'revoke'])->name('users.revoke');
    Route::patch('/users/{membership}/role', [MembershipController::class, 'setRole'])->name('users.role');

    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

    // SPRINT-SF-05 — tenant notification inbox + preferences.
    Route::get('/notifications', [NotificationInboxController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationInboxController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{delivery}/read', [NotificationInboxController::class, 'markRead'])->name('notifications.read');
    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'edit'])->name('notifications.preferences.edit');
    Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');

    // SPRINT-SF-05 — tenant subscription overview (read-only for the tenant).
    Route::get('/subscription', [SubscriptionOverviewController::class, 'show'])->name('subscription.show');
});

/*
 * SPRINT-SF-05 — Platform operator plane. Authenticated + verified + active + platform-role
 * gated. Establishes NO tenant context; each route additionally requires a specific platform
 * permission (rule 31 §10). Tenant-owned models here are resolved without the tenant scope and
 * always filtered explicitly by the acted-on tenant/subscription.
 */
Route::middleware('platform')->prefix('platform-admin')->name('platform.')->group(function (): void {
    Route::get('/', PlatformDashboardController::class)->name('dashboard');

    Route::get('/tenants', [TenantDirectoryController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [TenantDirectoryController::class, 'show'])->name('tenants.show');
    Route::patch('/tenants/{tenant}/suspend', [TenantDirectoryController::class, 'suspend'])->name('tenants.suspend');
    Route::patch('/tenants/{tenant}/reactivate', [TenantDirectoryController::class, 'reactivate'])->name('tenants.reactivate');
    Route::patch('/tenants/{tenant}/deletion-pending', [TenantDirectoryController::class, 'markDeletionPending'])->name('tenants.deletion-pending');
    Route::post('/tenants/{tenant}/support-notes', [SupportNoteController::class, 'store'])->name('tenants.support-notes.store');

    Route::get('/plans', [PlanCatalogController::class, 'index'])->name('plans.index');
    Route::post('/plans', [PlanCatalogController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}', [PlanCatalogController::class, 'show'])->name('plans.show');
    Route::patch('/plans/{plan}/activate', [PlanCatalogController::class, 'activate'])->name('plans.activate');
    Route::patch('/plans/{plan}/retire', [PlanCatalogController::class, 'retire'])->name('plans.retire');
    Route::post('/plans/{plan}/features', [PlanCatalogController::class, 'storeFeature'])->name('plans.features.store');

    Route::get('/subscriptions', [SubscriptionDirectoryController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions', [SubscriptionDirectoryController::class, 'assign'])->name('subscriptions.assign');
    Route::patch('/subscriptions/{subscription}/transition', [SubscriptionDirectoryController::class, 'transition'])->name('subscriptions.transition');

    Route::get('/notifications', [NotificationHealthController::class, 'index'])->name('notifications.index');

    Route::get('/users', [PlatformUserController::class, 'index'])->name('users.index');
    Route::post('/users', [PlatformUserController::class, 'invite'])->name('users.invite');
    Route::delete('/users/{assignment}', [PlatformUserController::class, 'removeRole'])->name('users.remove-role');

    Route::get('/audit', [PlatformAuditController::class, 'index'])->name('audit.index');
});
