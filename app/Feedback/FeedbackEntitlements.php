<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Feedback\Exceptions\EntitlementDeniedException;
use App\Models\Tenant;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;

/**
 * The single place feedback entitlement decisions are made. It delegates entirely to the
 * authoritative EntitlementResolver — no duplicated plan logic — and every check fails closed: an
 * ungranted or unknown key denies. A commercial restriction never overrides a security suspension
 * (the resolver already encodes suspension precedence) (rule 33; Step 8 §22).
 */
final class FeedbackEntitlements
{
    public function __construct(private readonly EntitlementResolver $resolver) {}

    public function assertFeedbackEnabled(Tenant $tenant): void
    {
        $this->assertBooleanGranted($tenant, EntitlementKeys::FEEDBACK_ENABLED);
    }

    public function assertAttachmentsEnabled(Tenant $tenant): void
    {
        $this->assertFeedbackEnabled($tenant);
        $this->assertBooleanGranted($tenant, EntitlementKeys::FEEDBACK_ATTACHMENTS_ENABLED);
    }

    public function assertExportsEnabled(Tenant $tenant): void
    {
        $this->assertFeedbackEnabled($tenant);
        $this->assertBooleanGranted($tenant, EntitlementKeys::FEEDBACK_EXPORTS_ENABLED);
    }

    public function assertBulkActionsEnabled(Tenant $tenant): void
    {
        $this->assertFeedbackEnabled($tenant);
        $this->assertBooleanGranted($tenant, EntitlementKeys::FEEDBACK_BULK_ACTIONS_ENABLED);
    }

    public function feedbackEnabled(Tenant $tenant): bool
    {
        return $this->resolver->resolve($tenant, EntitlementKeys::FEEDBACK_ENABLED)->allowed;
    }

    private function assertBooleanGranted(Tenant $tenant, string $key): void
    {
        $decision = $this->resolver->resolve($tenant, $key);
        if (! $decision->allowed) {
            throw EntitlementDeniedException::notGranted($key, $decision->reasonCode);
        }
    }
}
