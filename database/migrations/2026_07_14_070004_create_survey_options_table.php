<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A selectable option for a choice question. Tenant-owned. `option_key` and `display_order`
 * are unique within their question. `score` is an optional integer contribution for scored
 * choice questions. Options are immutable once the owning version is published (rule 32;
 * Step 7 §12).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_options', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->string('option_key', 64);
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('display_order');
            $table->integer('score')->nullable();
            $table->timestamps();
            $table->unique(['question_id', 'option_key']);
            $table->unique(['question_id', 'display_order']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_options');
    }
};
