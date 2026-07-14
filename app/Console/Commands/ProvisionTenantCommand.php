<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Tenancy\TenantProvisioningService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Securely provisions a tenant and its first Business Owner. Never prints a plaintext
 * password or invitation token — the owner sets their own password by accepting the
 * emailed invitation (rule 30; ADR 0013).
 */
final class ProvisionTenantCommand extends Command
{
    protected $signature = 'aish:tenant-provision
        {--name= : Tenant display name}
        {--owner-email= : Email of the first Business Owner}
        {--owner-name= : Name of the first Business Owner}
        {--slug= : Optional tenant slug (defaults to a slug of the name)}
        {--branch-name= : Optional initial branch name}
        {--branch-code= : Optional initial branch code}
        {--timezone=Asia/Makassar : Tenant timezone}
        {--locale=en : Tenant locale}';

    protected $description = 'Securely provision a new tenant with its first Business Owner (invitation-based, no plaintext secrets).';

    public function handle(TenantProvisioningService $service): int
    {
        $name = (string) ($this->option('name') ?? '');
        $ownerEmail = (string) ($this->option('owner-email') ?? '');
        $ownerName = (string) ($this->option('owner-name') ?? '');

        if ($name === '' || $ownerEmail === '' || $ownerName === '') {
            $this->error('--name, --owner-email and --owner-name are required.');

            return self::INVALID;
        }

        if (! filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('--owner-email must be a valid email address.');

            return self::INVALID;
        }

        try {
            $result = $service->provision(
                tenantName: $name,
                ownerEmail: $ownerEmail,
                ownerName: $ownerName,
                slug: $this->option('slug') !== null ? (string) $this->option('slug') : null,
                branchName: $this->option('branch-name') !== null ? (string) $this->option('branch-name') : null,
                branchCode: $this->option('branch-code') !== null ? (string) $this->option('branch-code') : null,
                timezone: (string) $this->option('timezone'),
                locale: (string) $this->option('locale'),
            );
        } catch (Throwable $e) {
            // Fail safe: no partial output, no secret exposure.
            $this->error('Provisioning failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Tenant provisioned.');
        $this->table(['Field', 'Value'], [
            ['Tenant', $result->tenant->name],
            ['Slug', $result->tenant->slug],
            ['Tenant ref', $result->tenant->ulid],
            ['Owner email', $result->owner->email],
            ['Owner membership', $result->ownerMembership->status->value],
            ['Initial branch', $result->branch !== null ? $result->branch->code : '(none)'],
            ['Invitation ref', $result->ownerInvitation->ulid],
            ['Invitation expires', $result->ownerInvitation->expires_at->toDayDateTimeString()],
        ]);
        $this->line('An invitation email was dispatched to the owner, who sets their own credentials via that link. No secret is printed by design.');

        return self::SUCCESS;
    }
}
