<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint as B;
use Illuminate\Support\Facades\Schema;

/**
 * A survey response, always bound to the exact answered survey version. Tenant-owned. A
 * partial unique index guarantees a unique invitation can have at most ONE completed response
 * (no double-completion). `metadata` carries only a minimized allowlist (e.g. hashed ip / ua)
 * — never free-text answer content. A completed response is immutable via the normal workflow;
 * `invalidated` requires an authorized reason and never deletes the row (rule 32; Step 7 §18,
 * §19).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->ulid('correlation_id')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->restrictOnDelete();
            $table->foreignId('survey_version_id')->constrained('survey_versions')->restrictOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('survey_campaigns')->nullOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('survey_invitations')->nullOnDelete();
            $table->string('mode', 20);
            $table->string('status', 20)->default('started');
            $table->string('locale', 10)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->string('invalidated_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'survey_version_id']);
            $table->index(['tenant_id', 'campaign_id']);
            $table->index('status');
        });

        // One completed response per unique invitation (partial unique index — Postgres).
        DB::statement(
            'CREATE UNIQUE INDEX survey_responses_one_completed_per_invitation '
            ."ON survey_responses (invitation_id) WHERE status = 'completed' AND invitation_id IS NOT NULL"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
