<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only operational timeline for a feedback item. Every row uses a stable event code and
 * allowlisted, sanitized metadata — never a note body, response free text, attachment content,
 * tokens, or storage paths. Rows are immutable (no updated_at; update/delete blocked at the model
 * layer). The composite FK keeps each event inside its feedback item's tenant (rule 33; Step 8 §15).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_events', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('feedback_item_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('type', 60);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign(['tenant_id', 'feedback_item_id'], 'feedback_events_item_tenant_fk')
                ->references(['tenant_id', 'id'])->on('feedback_items')->cascadeOnDelete();
            $table->index(['tenant_id', 'feedback_item_id']);
            $table->index(['feedback_item_id', 'id']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_events');
    }
};
