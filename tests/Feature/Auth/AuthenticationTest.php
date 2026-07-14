<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Authentication surface (Fortify-backed): generic failure semantics (no enumeration),
 * suspended-account fail-closed, login throttling, verification gating, disabled public
 * registration, and the forgot/reset password flow (rule 04, rule 30).
 */
final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_authenticate_and_redirect_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_fail_without_revealing_the_reason(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unknown_email_and_wrong_password_produce_identical_errors(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $unknown = $this->from('/login')->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'whatever',
        ]);

        $wrong = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // No account enumeration: the two failures are indistinguishable to the client.
        $this->assertSame(
            $unknown->assertSessionHasErrors('email')->getSession()->get('errors')->get('email'),
            $wrong->assertSessionHasErrors('email')->getSession()->get('errors')->get('email'),
        );
    }

    public function test_suspended_user_cannot_authenticate(): void
    {
        $user = User::factory()->suspended()->create(['password' => Hash::make('password')]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_attempts(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
            $this->assertNotSame(429, $response->getStatusCode());
        }

        // The sixth request within the window is rejected by the rate limiter.
        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect();

        $this->assertGuest();
    }

    public function test_unverified_user_is_redirected_to_verification_from_a_tenant_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->withSession(['current_tenant_id' => 1])
            ->get('/dashboard');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_public_registration_routes_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_forgot_and_reset_password_happy_path(): void
    {
        Notification::fake();

        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }
}
