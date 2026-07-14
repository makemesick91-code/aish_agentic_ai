<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Typed entitlement definitions for a plan. Values are stored in typed columns and the key
 * is allowlisted (App\Subscriptions\EntitlementKeys) — never unvalidated free-form JSON as a
 * source of truth (rule 31 §9.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('key');
            $table->string('type', 16);
            $table->boolean('value_boolean')->nullable();
            // -1 = unlimited (explicit sentinel; a negative limit is otherwise invalid).
            $table->bigInteger('value_int')->nullable();
            $table->string('value_string')->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
