<?php

declare(strict_types=1);

/**
 * Step 10 — Customer 360 Foundation: the canonical tenant-scoped customer aggregate.
 *
 * Rule 36; ADR 0064 (identity ownership), ADR 0070 (platform-core placement),
 * ADR 0072 (no-delete reversible merge). Additive only — no Step 8 structure is altered.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            // Branch is provenance only (ADR 0064) — never a cross-tenant key.
            $table->foreignId('primary_branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('display_name', 190)->nullable();

            // Contact fields are PII and are gated by the `customer.view-contact` permission (rule 36).
            // They live here, not on customer_identities, so a single permission gates every read (ADR 0071).
            $table->string('contact_email', 190)->nullable();
            $table->string('contact_phone', 40)->nullable();

            $table->string('status', 20)->default('active');

            // Merge survivor pointer (ADR 0072). A merged customer is retained, never deleted.
            $table->unsignedBigInteger('merged_into_customer_id')->nullable();

            $table->timestamp('pii_purged_at')->nullable();
            $table->boolean('legal_hold')->default(false);

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Composite-FK target so children are structurally pinned inside the parent's tenant.
            $table->unique(['tenant_id', 'id'], 'customers_tenant_id_unique');

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'primary_branch_id']);
            $table->index(['tenant_id', 'merged_into_customer_id'], 'customers_tenant_merged_into_index');
            $table->index(['tenant_id', 'last_seen_at']);
        });

        // Self-referencing survivor FK, added after creation so the table exists.
        Schema::table('customers', function (Blueprint $table): void {
            $table->foreign(['tenant_id', 'merged_into_customer_id'], 'customers_merged_into_tenant_fk')
                ->references(['tenant_id', 'id'])->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropForeign('customers_merged_into_tenant_fk');
        });

        Schema::dropIfExists('customers');
    }
};
