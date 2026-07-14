<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A campaign binds an immutable published survey version to a distribution. Tenant-owned;
 * `branch_id` null = tenant-wide. `survey_id`/`survey_version_id` are restricted-on-delete so a
 * bound survey/version can never be silently removed, and a campaign can never silently switch
 * version. `public_id` is an opaque ULID used in public/QR links so no sequential internal id
 * is exposed and the tenant is not inferable (rule 32; Step 7 §16, §17.3, ADR 0058).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->ulid('public_id')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->restrictOnDelete();
            $table->foreignId('survey_version_id')->constrained('survey_versions')->restrictOnDelete();
            $table->string('name');
            $table->string('status', 20)->default('draft');
            $table->string('mode', 20)->default('anonymous');
            $table->json('channel_config')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('invitation_expiry_days')->nullable();
            $table->json('frequency_config')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'branch_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_campaigns');
    }
};
