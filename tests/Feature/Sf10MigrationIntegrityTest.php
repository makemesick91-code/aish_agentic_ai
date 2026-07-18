<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CustomerIdentityType;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\FeedbackItem;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Database-level invariants for Step 10. These assert the constraints actually BITE — an
 * application-layer check that is not backed by the schema is one refactor away from being bypassed
 * (rule 36; contract §5).
 */
final class Sf10MigrationIntegrityTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    /** Duplicate prevention must be enforced by the database, not just by the resolver. */
    public function test_an_identity_value_is_unique_per_tenant_and_type(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $identity = CustomerIdentity::factory()->forCustomer($customer)->create();

        $this->expectException(QueryException::class);

        CustomerIdentity::factory()->forCustomer($customer)->create([
            'identity_type' => $identity->identity_type,
            'value_hash' => $identity->value_hash,
        ]);
    }

    /** The same hash in a different tenant must remain insertable — isolation, not collision. */
    public function test_the_same_hash_is_allowed_in_a_different_tenant(): void
    {
        $tenantA = $this->provisionTenant();
        $this->establishTenantContext($tenantA);
        $customerA = Customer::factory()->create(['tenant_id' => $tenantA->id]);
        $identity = CustomerIdentity::factory()->forCustomer($customerA)->create();
        $this->forgetTenantContext();

        $tenantB = $this->provisionTenant();
        $this->establishTenantContext($tenantB);
        $customerB = Customer::factory()->create(['tenant_id' => $tenantB->id]);

        $duplicate = CustomerIdentity::factory()->forCustomer($customerB)->create([
            'identity_type' => $identity->identity_type,
            'value_hash' => $identity->value_hash,
        ]);

        $this->assertNotNull($duplicate->id);
    }

    /** The Step 8 link must be additive and optional — unlinked feedback stays valid. */
    public function test_feedback_items_customer_id_is_nullable_and_additive(): void
    {
        $this->assertTrue(Schema::hasColumn('feedback_items', 'customer_id'));

        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $item = FeedbackItem::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertNull($item->customer_id);
    }

    public function test_all_step_10_tables_exist(): void
    {
        foreach (['customers', 'customer_identities', 'customer_merge_events', 'customer_consents'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing Step 10 table [{$table}].");
        }
    }

    /** Append-only ledgers must not carry an updated_at column (rule 36). */
    public function test_append_only_tables_have_no_updated_at(): void
    {
        foreach (['customer_merge_events', 'customer_consents'] as $table) {
            $this->assertFalse(
                Schema::hasColumn($table, 'updated_at'),
                "[{$table}] must be append-only and carry no updated_at (rule 36).",
            );
        }
    }

    /** Public route keys must be opaque ULIDs, never guessable sequential ids. */
    public function test_customer_tables_expose_ulid_route_keys(): void
    {
        foreach (['customers', 'customer_identities', 'customer_merge_events', 'customer_consents'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'ulid'), "[{$table}] must expose a ULID.");
        }
    }

    /** The model layer must refuse a plaintext PII identity even on a direct write (ADR 0071). */
    public function test_a_pii_identity_cannot_persist_a_plaintext_value(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->expectException(\RuntimeException::class);

        CustomerIdentity::factory()->forCustomer($customer)->create([
            'identity_type' => CustomerIdentityType::Email,
            'value_normalized' => 'leaked@example.com',
        ]);
    }
}
