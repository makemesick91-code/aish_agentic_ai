<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\InvitationAcceptController;
use App\Http\Controllers\Tenancy\AuditLogController;
use App\Http\Controllers\Tenancy\BranchController;
use App\Http\Controllers\Tenancy\BranchSelectionController;
use App\Http\Controllers\Tenancy\FoundationDashboardController;
use App\Http\Controllers\Tenancy\InvitationController;
use App\Http\Controllers\Tenancy\MembershipController;
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
});
