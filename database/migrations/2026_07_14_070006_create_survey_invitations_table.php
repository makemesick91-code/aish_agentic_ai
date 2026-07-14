<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A unique survey invitation. Tenant-owned. `public_id` (opaque ULID, in the URL) plus a
 * SHA-256 `token_hash` at rest — the plaintext secret exists only transiently to build the
 * link and is NEVER stored or logged. `token_hash` is globally unique. The tenant-leading
 * unique (tenant_id, idempotency_key) makes creation idempotent so a retry never issues a
 * duplicate invitation (rule 32; Step 7 §17, ADR 0058).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_invitations', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->ulid('public_id')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('campaign_id')->constrained('survey_campaigns')->cascadeOnDelete();
            $table->foreignId('survey_version_id')->constrained('survey_versions')->restrictOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->string('recipient_email')->nullable();
            $table->string('status', 24)->default('created');
            $table->string('idempotency_key');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('delivery_failure_code', 64)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['campaign_id', 'status']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_invitations');
    }
};
