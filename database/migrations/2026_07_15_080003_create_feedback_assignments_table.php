<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only assignment history for a feedback item. Each row records the transition from a
 * previous assignee to a new assignee (either nullable — a null new assignee is an unassignment),
 * with the acting user and an optional reason. Rows are immutable; the current assignee lives on
 * `feedback_items.current_assignee_id`. The composite FK keeps history inside the owning tenant
 * (rule 33; Step 8 §11).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_assignments', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('feedback_item_id');
            $table->foreignId('previous_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign(['tenant_id', 'feedback_item_id'], 'feedback_assignments_item_tenant_fk')
                ->references(['tenant_id', 'id'])->on('feedback_items')->cascadeOnDelete();
            $table->index(['tenant_id', 'feedback_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_assignments');
    }
};
