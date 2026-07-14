<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only internal staff note on a feedback item. Notes are operational, never customer
 * communication. The body is untrusted free text — length-limited, escaped on output, and never
 * written to logs, audit metadata, or default notifications. Rows are immutable (no updated_at;
 * update/delete blocked at the model layer); a correction is a new note (rule 33; Step 8 §13).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_notes', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('feedback_item_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->nullable();

            $table->foreign(['tenant_id', 'feedback_item_id'], 'feedback_notes_item_tenant_fk')
                ->references(['tenant_id', 'id'])->on('feedback_items')->cascadeOnDelete();
            $table->index(['tenant_id', 'feedback_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_notes');
    }
};
