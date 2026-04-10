<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EndpointTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_home_page_returns_successful_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Article Favorites');
    }

    public function test_guest_can_view_register_page(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Register');
    }

    public function test_guest_can_view_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Log in');
    }

    public function test_guest_can_view_forgot_password_page(): void
    {
        $this->get('/forgot-password')
            ->assertOk();
    }

    public function test_guest_is_redirected_from_verify_email_notice(): void
    {
        $this->get('/verify-email')->assertRedirect('/login');
    }

    public function test_unverified_user_can_view_verify_email_notice(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertOk()
            ->assertSee('Thanks for signing up!');
    }

    public function test_verified_user_is_redirected_from_verify_email_notice(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertRedirect('/dashboard');
    }

    public function test_unverified_user_can_request_verification_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->post('/email/verification-notification')
            ->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_authenticated_user_can_view_confirm_password_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/confirm-password')
            ->assertOk()
            ->assertSee('Please confirm your password');
    }

    public function test_authenticated_user_can_confirm_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->actingAs($user)
            ->post('/confirm-password', ['password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_guest_cannot_logout(): void
    {
        $this->post('/logout')->assertRedirect('/login');
    }
}
