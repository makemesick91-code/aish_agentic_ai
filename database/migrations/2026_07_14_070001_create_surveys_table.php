<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A survey is a tenant-owned stable identity; its editable/immutable content lives in
 * survey_versions. `branch_id` null = tenant-wide; a branch-owned survey cannot be used by
 * another branch. `current_version_id` points at the current published version (FK added in a
 * later migration to avoid a circular dependency). A published survey MUST NOT be
 * hard-deleted (rule 32; Step 7 §10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'branch_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
