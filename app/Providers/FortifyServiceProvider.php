<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Audit\AuditRecorder;
use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registration is disabled (rule 30); no createUsersUsing binding. 2FA/passkeys
        // are out of scope this step.
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->registerViews();
        $this->registerAuthentication();
        $this->registerRateLimiter();
        $this->registerAuditListeners();
    }

    private function registerViews(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
    }

    /**
     * Authenticate with generic failure semantics: a wrong password, an unknown email,
     * and a suspended account all fail identically — no account enumeration (rule 30).
     */
    private function registerAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request): ?User {
            $audit = app(AuditRecorder::class);
            $email = Str::lower((string) $request->input('email'));
            $user = User::where('email', $email)->first();

            if ($user !== null
                && Hash::check((string) $request->input('password'), (string) $user->password)
                && $user->isActive()) {
                $user->forceFill(['last_authenticated_at' => now()])->save();
                $audit->record('auth.login.succeeded', ['actor_id' => $user->id]);

                return $user;
            }

            $audit->record('auth.login.failed', [
                'metadata' => ['email' => $email, 'reason' => $user !== null ? 'invalid_or_suspended' : 'unknown'],
            ]);

            return null;
        });
    }

    private function registerRateLimiter(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $throttleKey = Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    private function registerAuditListeners(): void
    {
        $audit = fn (): AuditRecorder => app(AuditRecorder::class);

        Event::listen(Logout::class, function (Logout $event) use ($audit): void {
            $audit()->record('auth.logout', ['actor_id' => $event->user->getAuthIdentifier()]);
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event) use ($audit): void {
            $audit()->record('auth.password.reset', ['actor_id' => $event->user->getAuthIdentifier()]);
        });

        Event::listen(Verified::class, function (Verified $event) use ($audit): void {
            // The verified user is always an Authenticatable in this application; guard so
            // the identifier is read from the Authenticatable contract, not MustVerifyEmail.
            $user = $event->user;
            $actorId = $user instanceof Authenticatable
                ? $user->getAuthIdentifier()
                : null;
            $audit()->record('auth.email.verified', ['actor_id' => $actorId]);
        });
    }
}
