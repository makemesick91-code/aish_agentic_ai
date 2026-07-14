<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-oriented subscription history. Tenant-owned. There is no updated_at: a subscription
 * transition writes a new row and never mutates an existing one (rule 07, rule 31 §9.4).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_events', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('tenant_subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('event');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
    }
};
