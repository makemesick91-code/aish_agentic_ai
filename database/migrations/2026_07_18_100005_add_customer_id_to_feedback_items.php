<?php

declare(strict_types=1);

/**
 * Step 10 — Customer 360 Foundation: additive nullable customer link on Step 8 feedback items.
 *
 * Rule 36; ADR 0068 (additive migration), ADR 0070 (derived read-model joins through this link).
 * ADDITIVE ONLY: no existing Step 8 column is altered or dropped, and this migration performs NO
 * backfill — linking happens later through the queued, idempotent, resumable backfill
 * (`aish:customer-reconcile`). Unlinked feedback remains valid (contract §5, §6).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('customer_id')->nullable()->after('branch_id');

            // Composite FK pins the link inside the item's own tenant — a cross-tenant link is
            // structurally impossible, not merely validated in application code (rule 36).
            $table->foreign(['tenant_id', 'customer_id'], 'feedback_items_customer_tenant_fk')
                ->references(['tenant_id', 'id'])->on('customers')->nullOnDelete();

            // Supports the bounded, indexed Customer 360 interactions read-model (ADR 0070).
            $table->index(['tenant_id', 'customer_id'], 'feedback_items_tenant_customer_index');
        });
    }

    public function down(): void
    {
        Schema::table('feedback_items', function (Blueprint $table): void {
            $table->dropForeign('feedback_items_customer_tenant_fk');
            $table->dropIndex('feedback_items_tenant_customer_index');
            $table->dropColumn('customer_id');
        });
    }
};
