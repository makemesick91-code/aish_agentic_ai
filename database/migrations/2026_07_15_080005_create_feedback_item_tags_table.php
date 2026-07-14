<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot linking a feedback item to a manual tag. Both composite FKs reference (tenant_id, id) on
 * their parent, so a link can only ever join a feedback item and a tag that belong to the SAME
 * tenant — cross-tenant pivot injection is impossible at the database layer. A tag can be attached
 * to a feedback item at most once (rule 33; Step 8 §12, §25).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_item_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('feedback_item_id');
            $table->unsignedBigInteger('feedback_tag_id');
            $table->foreignId('attached_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->foreign(['tenant_id', 'feedback_item_id'], 'feedback_item_tags_item_tenant_fk')
                ->references(['tenant_id', 'id'])->on('feedback_items')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'feedback_tag_id'], 'feedback_item_tags_tag_tenant_fk')
                ->references(['tenant_id', 'id'])->on('feedback_tags')->cascadeOnDelete();

            $table->unique(['feedback_item_id', 'feedback_tag_id']);
            $table->index(['tenant_id', 'feedback_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_item_tags');
    }
};
