<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A queued feedback export request. The generated CSV is written to a PRIVATE disk, has an
 * expiry, and is only downloadable by an authorized member of the owning tenant. State is truthful
 * (`ready` is set only after the file is written). The unique (tenant_id, idempotency_key) makes a
 * repeated request return the same export rather than duplicating work (rule 33; Step 8 §18).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_exports', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('format', 10)->default('csv');
            $table->boolean('includes_content')->default(false);
            $table->json('filters')->nullable();
            $table->string('disk', 40)->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('row_count')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('failure_code')->nullable();
            $table->string('idempotency_key');
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_exports');
    }
};
