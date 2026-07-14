<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenants: the root of every isolation boundary. Public references use the ULID
 * (non-enumerable) to prevent IDOR; the integer id is internal and also serves as the
 * Spatie "team" key for tenant-scoped RBAC (rule 03, rule 20; ADR 0011).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            // Lifecycle: active | suspended | deletion_pending. No hard delete in Step 6.
            $table->string('status', 20)->default('active');
            $table->string('timezone', 64)->default('Asia/Makassar');
            $table->string('locale', 12)->default('en');
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
