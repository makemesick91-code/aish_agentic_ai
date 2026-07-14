<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Typed tenant settings (no unbounded JSON dumping ground; rule 30). Only the keys the
 * foundation actually needs are modelled; each is validated at the service boundary.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('timezone', 64)->default('Asia/Makassar');
            $table->string('locale', 12)->default('en');
            $table->unsignedSmallInteger('data_retention_days')->default(365);
            $table->unsignedSmallInteger('invitation_expiry_days')->default(7);
            $table->boolean('require_email_verification')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
