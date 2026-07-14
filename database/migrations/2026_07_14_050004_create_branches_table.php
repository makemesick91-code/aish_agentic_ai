<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Branches are tenant-owned. Code is unique *within* a tenant (tenant-leading unique),
 * never globally, so no cross-tenant natural key exists (rule 03; ADR 0014).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            // active | inactive. Inactive branches can never be a working context.
            $table->string('status', 20)->default('active');
            $table->string('timezone', 64)->nullable();
            $table->string('locale', 12)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
