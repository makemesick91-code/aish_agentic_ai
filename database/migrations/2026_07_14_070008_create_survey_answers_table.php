<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single answer within a response. Tenant-owned. The question is restricted-on-delete and
 * must belong to the response's version; the option is restricted-on-delete and must belong to
 * the question (both enforced in the service/DB). A given (response, question, option) is
 * unique so a multiple-choice selection can't duplicate an option; single-answer questions get
 * exactly one row (app-enforced). Only the representation required by the question type is
 * stored (rule 32; Step 7 §19).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('survey_questions')->restrictOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('survey_options')->restrictOnDelete();
            $table->integer('numeric_value')->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
            $table->unique(['response_id', 'question_id', 'option_id']);
            $table->index('tenant_id');
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
