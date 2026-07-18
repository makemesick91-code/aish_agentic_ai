<?php

declare(strict_types=1);

/**
 * Step 10 — Customer 360 Foundation: source identities with keyed tenant-bound hashing.
 *
 * Rule 36; ADR 0071 (normalization + HMAC hashing; no plaintext PII stored here),
 * ADR 0064 (deterministic vs suggested), ADR 0072 (merge provenance for exact reversal).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_identities', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id');

            // Where the identity came from: survey, transaction, google, whatsapp, email, api, manual.
            $table->string('source_type', 40);
            // email | phone | external_ref
            $table->string('identity_type', 20);

            // Populated ONLY for non-PII identity types (external_ref). Never for email/phone (ADR 0071).
            $table->string('value_normalized', 190)->nullable();

            // HMAC-SHA256 hex, keyed with an APP_KEY-derived pepper bound to tenant_id (ADR 0071).
            $table->char('value_hash', 64);
            $table->unsignedSmallInteger('normalizer_version')->default(1);

            $table->string('provenance', 100)->nullable();
            $table->unsignedTinyInteger('confidence')->default(100);
            $table->boolean('is_deterministic')->default(false);
            $table->boolean('is_verified')->default(false);

            // Set when this identity was moved by a merge, so a split restores exactly (ADR 0072).
            $table->unsignedBigInteger('merged_from_customer_id')->nullable();

            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->foreign(['tenant_id', 'customer_id'], 'customer_identities_customer_tenant_fk')
                ->references(['tenant_id', 'id'])->on('customers')->cascadeOnDelete();

            // Duplicate prevention + cross-tenant isolation in one constraint (ADR 0064 §7, ADR 0071).
            $table->unique(['tenant_id', 'identity_type', 'value_hash'], 'customer_identities_value_unique');

            $table->unique(['tenant_id', 'id'], 'customer_identities_tenant_id_unique');
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'source_type']);
        });

        // Make ADR 0071 structural, not merely enforced by a model hook: a PII identity must never
        // carry a plaintext value, even if written by a raw query, an import, or future code that
        // bypasses Eloquent. Applied on PostgreSQL (the production driver); SQLite cannot add a
        // CHECK constraint via ALTER, and the hermetic suite is covered by the model-layer guard
        // plus tests/Feature/Sf10MigrationIntegrityTest.php.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE customer_identities
                ADD CONSTRAINT customer_identities_no_plaintext_pii
                CHECK (identity_type NOT IN ('email', 'phone') OR value_normalized IS NULL)
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_identities');
    }
};
