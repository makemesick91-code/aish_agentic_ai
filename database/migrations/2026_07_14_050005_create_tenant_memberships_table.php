<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant membership: the ONLY bridge between a global user and a tenant. A user with no
 * active membership has no access to a tenant. States: invited | active | suspended |
 * revoked. `all_branches` marks a tenant-wide member (e.g. Owner/Corporate Admin);
 * branch-restricted members are scoped through branch_access_grants (rule 30).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_memberships', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->boolean('all_branches')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            // One membership per (tenant, user) — no duplicate memberships.
            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_memberships');
    }
};
