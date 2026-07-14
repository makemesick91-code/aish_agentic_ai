<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-only operational notes about a tenant. Append-oriented (no updated_at). They may
 * reference a tenant but MUST NOT contain customer/medical data or secrets, and they are NOT
 * a form of impersonation (rule 31 §10.9).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_support_notes', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->nullable();
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_support_notes');
    }
};
