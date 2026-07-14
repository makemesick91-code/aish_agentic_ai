<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step 6 SaaS core: the global user identity gains a global account status and a
 * last-authenticated marker. `users` remains the single global identity — no tenant
 * role or tenant ownership is stored here; tenant access flows only through an active
 * membership (rule 30; ADR 0013).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Global account state, independent of any tenant membership.
            $table->string('status', 20)->default('active')->after('password');
            $table->timestamp('last_authenticated_at')->nullable()->after('remember_token');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'last_authenticated_at']);
        });
    }
};
