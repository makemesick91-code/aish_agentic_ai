<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A question within a survey version. Tenant-owned. `question_key` and `display_order` are
 * unique within their version so answers resolve deterministically. `scoring_config` /
 * `validation_config` are typed JSON (scale bounds, satisfied threshold, direction, min/max
 * select, max length). Question content is immutable once its version is published
 * (rule 32; Step 7 §12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_version_id')->constrained('survey_versions')->cascadeOnDelete();
            $table->string('question_key', 64);
            $table->string('type', 24);
            $table->text('prompt');
            $table->text('help_text')->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedInteger('display_order');
            $table->boolean('scored')->default(false);
            $table->json('scoring_config')->nullable();
            $table->json('validation_config')->nullable();
            $table->timestamps();
            $table->unique(['survey_version_id', 'question_key']);
            $table->unique(['survey_version_id', 'display_order']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
