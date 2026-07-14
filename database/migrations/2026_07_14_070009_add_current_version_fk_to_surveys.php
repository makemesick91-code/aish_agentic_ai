<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the deferred foreign key surveys.current_version_id -> survey_versions.id. Deferred to
 * this migration to break the circular dependency between surveys and survey_versions. Uses
 * nullOnDelete so removing a version (only possible while draft) never orphans the pointer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table): void {
            $table->foreign('current_version_id')
                ->references('id')->on('survey_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table): void {
            $table->dropForeign(['current_version_id']);
        });
    }
};
