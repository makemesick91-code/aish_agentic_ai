<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An immutable-once-published snapshot of a survey's content. Tenant-owned (denormalized
 * tenant_id for fail-closed scoping and integrity). `version_number` is unique per survey.
 * Publishing sets status=published and stamps published_at/by; editing published content
 * creates a new draft version and never mutates this row's questions/options (rule 32;
 * Step 7 §11).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_versions', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 20)->default('draft');
            $table->string('title');
            $table->text('introduction')->nullable();
            $table->text('completion_message')->nullable();
            $table->string('locale', 10)->default('id');
            $table->string('mode', 20)->default('anonymous');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['survey_id', 'version_number']);
            $table->index(['tenant_id', 'survey_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_versions');
    }
};
