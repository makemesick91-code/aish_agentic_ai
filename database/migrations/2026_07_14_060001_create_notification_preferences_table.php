<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user, per-tenant notification preferences. Tenant-owned (tenant-leading unique), so a
 * user's preferences in one tenant can never leak to or collide with another tenant
 * (rule 03, rule 31). Critical security notifications ignore these toggles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            // "HH:MM" 24h local times; null = no quiet window.
            $table->string('quiet_hours_start', 5)->nullable();
            $table->string('quiet_hours_end', 5)->nullable();
            $table->string('timezone', 64)->default('Asia/Makassar');
            // Validated per-category channel overrides (structure enforced in the value object).
            $table->json('category_overrides')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
