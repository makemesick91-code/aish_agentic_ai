<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generic, tenant-scoped, idempotent usage metering. Tenant-owned. The tenant-leading unique
 * (tenant_id, meter_key, idempotency_key) makes a repeated increment a no-op, so a retry can
 * never double-count. This is a skeleton meter, NOT billing-grade financial reconciliation
 * (rule 31 §9.7).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_records', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('meter_key');
            $table->unsignedBigInteger('quantity');
            $table->string('idempotency_key');
            $table->timestamp('occurred_at');
            $table->string('period_key', 16);
            $table->string('source_reference')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'meter_key', 'idempotency_key']);
            $table->index(['tenant_id', 'meter_key', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
