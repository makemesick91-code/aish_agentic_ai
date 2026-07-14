<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal operational attachment added by an authorized tenant user (never a customer upload). The
 * file lives on a PRIVATE disk under a tenant-prefixed path with a random stored filename; the
 * original filename is sanitized metadata only. MIME and size are validated from content on upload
 * and recorded here with a SHA-256 checksum. No public path is ever stored; `path` is model-hidden
 * (rule 33; Step 8 §14).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_attachments', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('feedback_item_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 40)->default('local');
            $table->string('path');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256', 64);
            $table->string('state', 20)->default('available');
            $table->string('rejected_reason')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            $table->foreign(['tenant_id', 'feedback_item_id'], 'feedback_attachments_item_tenant_fk')
                ->references(['tenant_id', 'id'])->on('feedback_items')->cascadeOnDelete();
            $table->index(['tenant_id', 'feedback_item_id']);
            $table->index(['tenant_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_attachments');
    }
};
