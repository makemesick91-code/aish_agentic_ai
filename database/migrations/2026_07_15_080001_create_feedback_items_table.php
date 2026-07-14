<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Operational feedback item projected from a completed survey response. Exactly one feedback item
 * exists per completed response, enforced by the unique (tenant_id, source_type, source_id) index
 * (idempotent projection). Tenant-owned; branch scope follows the source response. It holds only an
 * operational projection and allowlisted references — never a free-text copy of the response. The
 * unique (tenant_id, id) constraint is the composite-FK target that keeps every child row inside the
 * owning tenant (rule 33; Step 8 §9, §25).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_items', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Generic source identity (idempotency) + concrete survey references.
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            // Cascade: a feedback item is a projection of its response, so it is removed only when the
            // response is (e.g. tenant-lifecycle deletion). The normal workflow never deletes responses.
            $table->foreignId('survey_response_id')->nullable()->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('survey_id')->nullable()->constrained('surveys')->nullOnDelete();
            $table->foreignId('survey_version_id')->nullable()->constrained('survey_versions')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('survey_campaigns')->nullOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('survey_invitations')->nullOnDelete();

            $table->string('status', 20)->default('new');
            $table->foreignId('current_assignee_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('triaged_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            // Allowlisted operational snapshot (raw scored answer values, safe labels) — no free text.
            $table->json('metric_snapshot')->nullable();
            // Allowlisted searchable projections. `search_content` (response free text) is only exposed
            // to actors with the content-view permission; `search_meta` is safe metadata.
            $table->text('search_meta')->nullable();
            $table->text('search_content')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'source_type', 'source_id'], 'feedback_items_source_unique');
            $table->unique('survey_response_id', 'feedback_items_survey_response_unique');
            $table->unique(['tenant_id', 'id'], 'feedback_items_tenant_id_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['tenant_id', 'current_assignee_id']);
            $table->index(['tenant_id', 'last_activity_at']);
        });

        // PostgreSQL full-text search: maintained tsvector generated columns + GIN indexes. Guarded by
        // driver so the hermetic SQLite test suite (which falls back to LIKE search) still migrates.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'ALTER TABLE feedback_items ADD COLUMN search_meta_vector tsvector '
                ."GENERATED ALWAYS AS (to_tsvector('simple', coalesce(search_meta, ''))) STORED"
            );
            DB::statement(
                'ALTER TABLE feedback_items ADD COLUMN search_content_vector tsvector '
                ."GENERATED ALWAYS AS (to_tsvector('simple', coalesce(search_content, ''))) STORED"
            );
            DB::statement('CREATE INDEX feedback_items_search_meta_gin ON feedback_items USING gin (search_meta_vector)');
            DB::statement('CREATE INDEX feedback_items_search_content_gin ON feedback_items USING gin (search_content_vector)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_items');
    }
};
