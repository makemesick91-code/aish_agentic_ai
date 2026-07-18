<?php

declare(strict_types=1);

/**
 * Step 10 — Customer 360 Foundation: append-only consent and communication-preference history.
 *
 * Rule 36; ADR 0064 (versioned append-only consent; survey completion is not marketing consent);
 * rule 32 (consent semantics), rule 18 (no MED data).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_consents', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id');

            // marketing | follow_up | survey_invitation | do_not_contact
            $table->string('consent_type', 40);
            $table->boolean('accepted');

            // The exact consent text version the customer saw — history must stay explainable.
            $table->string('consent_text_version', 40);
            $table->string('source', 60);
            $table->string('channel', 20)->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            // Append-only: created_at only, no updated_at (rule 36). A change is a new row.
            $table->timestamp('created_at')->nullable();

            $table->foreign(['tenant_id', 'customer_id'], 'customer_consents_customer_tenant_fk')
                ->references(['tenant_id', 'id'])->on('customers')->cascadeOnDelete();

            $table->index(['tenant_id', 'customer_id']);
            // Latest-consent-per-type lookup.
            $table->index(['tenant_id', 'customer_id', 'consent_type', 'id'], 'customer_consents_latest_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_consents');
    }
};
