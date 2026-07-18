<?php

declare(strict_types=1);

/**
 * Step 10 — Customer 360 Foundation: append-only merge/split ledger.
 *
 * Rule 36; ADR 0072 (no-delete merges; exact snapshot-based reversal; append-only ledger),
 * ADR 0064 (human-approved reversible merge/split with immutable audit).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_merge_events', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            // merge | split
            $table->string('action', 20);

            $table->unsignedBigInteger('survivor_customer_id');
            $table->unsignedBigInteger('merged_customer_id');

            // For a split: the merge event being reversed. Append-only — the merge row is never updated.
            $table->unsignedBigInteger('reverses_merge_event_id')->nullable();

            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 500);

            // Sanitized pre-merge snapshot + the exact moved id sets. Ids, provenance, counts, status only —
            // never raw email/phone, free text, tokens, or medical data (rule 36).
            $table->json('snapshot');

            // Append-only: created_at only, no updated_at (rule 36).
            $table->timestamp('created_at')->nullable();

            $table->foreign(['tenant_id', 'survivor_customer_id'], 'customer_merge_events_survivor_fk')
                ->references(['tenant_id', 'id'])->on('customers')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'merged_customer_id'], 'customer_merge_events_merged_fk')
                ->references(['tenant_id', 'id'])->on('customers')->cascadeOnDelete();

            $table->unique(['tenant_id', 'id'], 'customer_merge_events_tenant_id_unique');
            $table->index(['tenant_id', 'survivor_customer_id']);
            $table->index(['tenant_id', 'merged_customer_id']);
            $table->index(['tenant_id', 'action']);
        });

        Schema::table('customer_merge_events', function (Blueprint $table): void {
            $table->foreign(['tenant_id', 'reverses_merge_event_id'], 'customer_merge_events_reverses_fk')
                ->references(['tenant_id', 'id'])->on('customer_merge_events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_merge_events', function (Blueprint $table): void {
            $table->dropForeign('customer_merge_events_reverses_fk');
        });

        Schema::dropIfExists('customer_merge_events');
    }
};
