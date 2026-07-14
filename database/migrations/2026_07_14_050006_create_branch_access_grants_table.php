<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Explicit branch scope for branch-restricted members. `tenant_id` is denormalised so
 * isolation checks and composite integrity never need a join, and a grant can never
 * point a membership at another tenant's branch (rule 03; ADR 0015).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_access_grants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('tenant_membership_id')->constrained('tenant_memberships')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tenant_membership_id', 'branch_id']);
            $table->index(['tenant_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_access_grants');
    }
};
