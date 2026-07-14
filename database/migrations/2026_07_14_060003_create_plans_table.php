<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The global plan catalog. Plans are a platform-owned catalog (NOT tenant-owned). A plan is
 * a specific (code, version); a retired version stays valid for existing references but is
 * never newly assigned. Plan versions must not silently change historical meaning
 * (rule 31 §9.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('code');
            $table->unsignedInteger('version')->default(1);
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('public_visible')->default(false);
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->timestamps();
            $table->unique(['code', 'version']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
