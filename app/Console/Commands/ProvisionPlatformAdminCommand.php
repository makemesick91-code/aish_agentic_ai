<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PlatformRole;
use App\Platform\PlatformUserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Securely provisions a platform operator. No password is accepted or printed — the operator
 * sets their own via the reset link sent on creation. Re-running for an existing email is safe
 * (rule 31 §10.5).
 */
final class ProvisionPlatformAdminCommand extends Command
{
    protected $signature = 'aish:platform-admin-provision
        {--email= : The operator email (required)}
        {--name= : The operator display name}
        {--role=super_admin : Platform role (super_admin, admin, support, finance, auditor, read_only)}';

    protected $description = 'Provision a platform operator securely (no fixed password; reset-link onboarding).';

    public function handle(PlatformUserService $users): int
    {
        $email = (string) ($this->option('email') ?? '');
        $roleValue = (string) ($this->option('role') ?? PlatformRole::SuperAdmin->value);
        $name = (string) ($this->option('name') ?? '');

        $validator = Validator::make(
            ['email' => $email, 'role' => $roleValue],
            [
                'email' => ['required', 'email'],
                'role' => ['required', 'in:'.implode(',', PlatformRole::values())],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $role = PlatformRole::from($roleValue);
        $name = $name !== '' ? $name : (string) str($email)->before('@');

        $users->provision($name, $email, $role, null);

        $this->info("Provisioned {$role->label()} for {$email}. A password-reset link has been sent; no password was set here.");

        return self::SUCCESS;
    }
}
