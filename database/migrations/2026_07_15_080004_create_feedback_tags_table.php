<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant-owned manual feedback tag. Names and slugs are unique within a tenant; a tag from one
 * tenant can never attach to another tenant's feedback (enforced by the pivot's composite FK). The
 * unique (tenant_id, id) constraint is the composite-FK target used by the pivot. Manual tags are
 * distinct from future AI-generated topics (rule 33; Step 8 §12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_tags', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status', 20)->default('active');
            $table->string('color', 20)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'id'], 'feedback_tags_tenant_id_unique');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_tags');
    }
};
