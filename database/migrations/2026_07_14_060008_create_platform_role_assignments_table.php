<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform (operator) role assignments — the SEPARATE platform authorization plane. These are
 * NOT Spatie tenant roles and carry no tenant_id: a platform role grants no tenant-data access
 * (rule 31 §10.1). A user holds each platform role at most once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_role_assignments', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32);
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_role_assignments');
    }
};
