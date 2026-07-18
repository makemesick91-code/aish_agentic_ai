<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Customers\CustomerConsentService;
use App\Customers\CustomerIdentityResolver;
use App\Customers\CustomerMergeService;
use App\Customers\Identity\IdentityCandidate;
use App\Enums\CustomerConsentType;
use App\Enums\CustomerIdentitySource;
use App\Enums\CustomerIdentityType;
use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\Concerns\ProvisionsTenants;
use Tests\TestCase;

/**
 * Customer 360 audit coverage and sanitization. Every identity-changing action is audited, and no
 * audit row may carry an identity value, contact detail, or consent prose (rule 36; contract §12).
 */
final class Sf10AuditTest extends TestCase
{
    use InteractsWithTenancy;
    use ProvisionsTenants;
    use RefreshDatabase;

    public function test_customer_creation_and_identity_linking_are_audited(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [IdentityCandidate::verified(CustomerIdentityType::Email, 'ana@example.com')],
        );

        $this->assertTrue(AuditLog::query()->where('event', 'customer.created')->exists());
        $this->assertTrue(AuditLog::query()->where('event', 'customer.identity.linked')->exists());
    }

    /** The audit trail must never become a way to recover a customer's contact details. */
    public function test_no_audit_row_contains_an_identity_value(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        app(CustomerIdentityResolver::class)->resolve(
            CustomerIdentitySource::Survey,
            [
                IdentityCandidate::verified(CustomerIdentityType::Email, 'private.person@example.com'),
                IdentityCandidate::verified(CustomerIdentityType::Phone, '+628112345678'),
            ],
        );

        $serialized = AuditLog::query()->get()
            ->map(fn (AuditLog $log): string => json_encode($log->metadata) ?: '')
            ->implode(' ');

        $this->assertStringNotContainsString('private.person@example.com', $serialized);
        $this->assertStringNotContainsString('628112345678', $serialized);
    }

    public function test_merge_and_split_are_audited_with_counts_not_content(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $survivor = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'contact_email' => 'survivor@example.com',
        ]);
        $merged = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'contact_email' => 'merged@example.com',
        ]);

        $service = app(CustomerMergeService::class);
        $event = $service->merge($survivor, $merged, 'Duplicate profile.');
        $service->split($event, 'Reversed after review.');

        $mergeAudit = AuditLog::query()->where('event', 'customer.merged')->firstOrFail();
        $splitAudit = AuditLog::query()->where('event', 'customer.split')->firstOrFail();

        $this->assertArrayHasKey('identities_moved', $mergeAudit->metadata);
        $this->assertArrayHasKey('identities_restored', $splitAudit->metadata);

        $serialized = json_encode([$mergeAudit->metadata, $splitAudit->metadata]) ?: '';
        $this->assertStringNotContainsString('survivor@example.com', $serialized);
        $this->assertStringNotContainsString('merged@example.com', $serialized);
    }

    public function test_consent_capture_is_audited_without_the_consent_prose(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        app(CustomerConsentService::class)->record(
            $customer,
            CustomerConsentType::Marketing,
            true,
            'v3',
            'operator',
        );

        $audit = AuditLog::query()->where('event', 'customer.consent.recorded')->firstOrFail();

        $this->assertSame('marketing', $audit->metadata['consent_type']);
        $this->assertSame('v3', $audit->metadata['consent_text_version']);
        $this->assertTrue($audit->metadata['accepted']);
    }

    /** Audit history must not be rewritable (rule 07, rule 36). */
    public function test_audit_rows_cannot_be_edited(): void
    {
        $tenant = $this->provisionTenant();
        $this->establishTenantContext($tenant);

        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        app(CustomerConsentService::class)->record($customer, CustomerConsentType::FollowUp, true, 'v1', 'operator');

        $audit = AuditLog::query()->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $audit->update(['event' => 'tampered']);
    }
}
