<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notification delivery records. One row per (recipient, channel) for a logical event.
 * Deliberately NOT auto-scoped by TenantScope: a delivery may be a legitimate platform
 * event with a null tenant_id (like audit_logs). Tenant isolation of *viewing* is enforced
 * by the forTenant() scope + NotificationDeliveryPolicy, and writes always carry an
 * explicit tenant_id. `dedup_key` is globally unique so a retry (or a duplicate dispatch of
 * the same logical event) yields exactly one logical delivery (rule 03, rule 31).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            // Nullable ONLY for legitimate platform events (rule 31 §8.6).
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('category', 32);
            $table->string('channel', 32);
            $table->string('state', 20)->default('pending');
            $table->boolean('critical')->default(false);
            // Logical event key (shared across a logical event's channels); dedup is per channel.
            $table->string('idempotency_key');
            $table->string('dedup_key')->unique();
            $table->string('correlation_id', 64)->nullable();
            // Foundation notification content is operational and non-sensitive by policy;
            // never medical/customer content, tokens, or secrets (rule 31 §8.6).
            $table->string('subject');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(3);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('suppressed_reason', 64)->nullable();
            $table->string('failure_code', 64)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'state']);
            $table->index(['tenant_id', 'recipient_id', 'read_at']);
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
