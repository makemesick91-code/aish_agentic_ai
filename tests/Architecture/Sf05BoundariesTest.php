<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Authorization\Permissions;
use App\Models\PlatformRoleAssignment;
use App\Platform\PlatformPermissions;
use App\Tenancy\Contracts\TenantOwned;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

/**
 * Tooling-free fitness functions for the SPRINT-SF-05 boundaries (rule 31): notification
 * delivery goes through one dispatcher, platform authorization is separate and always enforced,
 * and impersonation does not exist. A regression fails a test rather than review.
 */
final class Sf05BoundariesTest extends TestCase
{
    /** @return list<string> */
    private function phpFiles(string $relativeDir): array
    {
        $base = base_path($relativeDir);
        if (! is_dir($base)) {
            return [];
        }

        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function relativePath(string $absolute): string
    {
        return ltrim(str_replace('\\', '/', str_replace(base_path(), '', $absolute)), '/');
    }

    public function test_tenant_and_platform_permission_vocabularies_are_disjoint(): void
    {
        $overlap = array_intersect(Permissions::all(), PlatformPermissions::all());

        $this->assertSame(
            [],
            array_values($overlap),
            'Tenant and platform permission vocabularies must never share a name (rule 31 §10.3).',
        );
    }

    public function test_every_platform_controller_enforces_a_platform_permission(): void
    {
        $files = $this->phpFiles('app/Http/Controllers/Platform');
        $this->assertNotEmpty($files, 'Expected platform controllers to exist.');

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);

            $this->assertStringContainsString(
                'PlatformPermissions::',
                $contents,
                'Platform controller must reference a platform permission: '.$this->relativePath($file),
            );

            $this->assertTrue(
                str_contains($contents, 'authorize(PlatformPermissions::')
                    || str_contains($contents, 'Gate::allows(PlatformPermissions::')
                    || str_contains($contents, 'can(PlatformPermissions::'),
                'Platform controller must enforce a platform permission (authorize/Gate/can): '.$this->relativePath($file),
            );
        }
    }

    public function test_no_impersonation_construct_exists(): void
    {
        foreach ($this->phpFiles('app') as $file) {
            $contents = (string) file_get_contents($file);

            $this->assertDoesNotMatchRegularExpression(
                '/\b(function|class|trait|interface)\s+\w*[Ii]mpersonat\w*/',
                $contents,
                'Impersonation constructs are prohibited (rule 31 §10.10): '.$this->relativePath($file),
            );
        }

        foreach (Route::getRoutes() as $route) {
            $this->assertStringNotContainsStringIgnoringCase('impersonat', (string) $route->uri());
            $this->assertStringNotContainsStringIgnoringCase('impersonat', (string) $route->getName());
        }
    }

    public function test_mail_is_only_sent_from_the_mail_channel(): void
    {
        // Outbound mail is centralized to reviewed adapters only: the member notification
        // MailChannel, and the Step 7 customer survey-invitation mailer (customers are
        // non-members and cannot use the member-only channel) (rule 31 §8.2, rule 32 §22).
        $allowed = [
            'app/Services/Notifications/Channels/MailChannel.php',
            'app/Surveys/SurveyInvitationMailer.php',
        ];

        foreach ($this->phpFiles('app') as $file) {
            $contents = (string) file_get_contents($file);
            if (str_contains($contents, 'Mail::to(')) {
                $this->assertContains(
                    $this->relativePath($file),
                    $allowed,
                    'Mail must only be sent from a reviewed mail adapter (rule 31 §8.2).',
                );
            }
        }
    }

    public function test_delivery_job_is_only_dispatched_by_the_dispatcher_or_itself(): void
    {
        $allowed = [
            'app/Services/Notifications/NotificationDispatcher.php',
            'app/Jobs/Notifications/DeliverNotificationJob.php',
        ];

        foreach ($this->phpFiles('app') as $file) {
            $contents = (string) file_get_contents($file);
            if (str_contains($contents, 'DeliverNotificationJob::dispatch')) {
                $this->assertContains(
                    $this->relativePath($file),
                    $allowed,
                    'Notification delivery must be enqueued only via the dispatcher (rule 31 §8.2).',
                );
            }
        }
    }

    public function test_platform_role_assignment_is_not_tenant_owned(): void
    {
        $reflection = new ReflectionClass(PlatformRoleAssignment::class);

        $this->assertFalse(
            $reflection->implementsInterface(TenantOwned::class),
            'Platform role assignments are a global operator plane, not tenant-owned (rule 31 §10.1).',
        );
    }
}
