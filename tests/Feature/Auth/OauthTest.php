<?php

namespace Tests\Feature\Auth;

use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class OauthTest extends TestCase
{
    use RefreshDatabase;

    protected function mockSocialiteUser(string $provider, string $id, string $email, string $name, bool $verified = true, array $extraRaw = []): void
    {
        $raw = match ($provider) {
            'google' => array_merge(['email_verified' => $verified, 'email' => $email], $extraRaw),
            'microsoft' => array_merge(['email_verified' => $verified, 'mail' => $email], $extraRaw),
            'linkedin' => array_merge(['email_verified' => $verified, 'email' => $email], $extraRaw),
            default => array_merge(['email_verified' => $verified], $extraRaw),
        };

        $socialUser = new \Laravel\Socialite\Two\User;
        $socialUser->setRaw($raw);
        $socialUser->map([
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'nickname' => null,
        ]);

        $providerMock = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with($provider)->andReturn($providerMock);
    }

    protected function mockSocialiteRedirect(string $provider): void
    {
        $providerMock = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $providerMock->shouldReceive('redirect')->andReturn(redirect('https://oauth.'.$provider.'.example/authorize'));

        Socialite::shouldReceive('driver')->with($provider)->andReturn($providerMock);
    }

    public function test_redirect_to_provider_oauth(): void
    {
        $this->mockSocialiteRedirect('google');
        $this->get('/auth/google/redirect')->assertRedirect('https://oauth.google.example/authorize');

        $this->mockSocialiteRedirect('linkedin');
        $this->get('/auth/linkedin/redirect')->assertRedirect('https://oauth.linkedin.example/authorize');

        $this->mockSocialiteRedirect('microsoft');
        $this->get('/auth/microsoft/redirect')->assertRedirect('https://oauth.microsoft.example/authorize');
    }

    public function test_redirect_rejects_unknown_provider(): void
    {
        $this->get('/auth/apple/redirect')->assertNotFound();
        $this->get('/auth/twitter/redirect')->assertNotFound();
        $this->get('/auth/apple/callback')->assertNotFound();
        $this->get('/auth/twitter/callback')->assertNotFound();
    }

    public function test_callback_creates_new_user_and_oauth_provider(): void
    {
        $this->mockSocialiteUser('google', 'g123', 'newuser@example.com', 'New User', true);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);

        $this->assertDatabaseHas('oauth_providers', [
            'provider' => 'google',
            'provider_id' => 'g123',
            'provider_email' => 'newuser@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_callback_logs_in_existing_oauth_user(): void
    {
        $user = User::factory()->create();
        OauthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'g999',
            'provider_email' => $user->email,
        ]);

        $this->mockSocialiteUser('google', 'g999', $user->email, $user->name, true);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_callback_links_to_existing_verified_email(): void
    {
        $existing = User::factory()->create(['email' => 'linkme@example.com', 'email_verified_at' => null]);

        $this->mockSocialiteUser('google', 'g456', 'linkme@example.com', 'Link Me', true);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseHas('oauth_providers', [
            'provider' => 'google',
            'provider_id' => 'g456',
            'user_id' => $existing->id,
        ]);
        $this->assertDatabaseCount('users', 1);
        $this->assertNotNull($existing->fresh()->email_verified_at);
    }

    public function test_callback_does_not_link_if_email_not_verified(): void
    {
        $existing = User::factory()->create(['email' => 'unverified@example.com']);

        // LinkedIn without verified flag should be treated as unverified (our isEmailVerified returns false for linkedin without verified flag unless explicitly true)
        // We mock with verified=false
        $this->mockSocialiteUser('linkedin', 'li789', 'unverified@example.com', 'Hacker', false);

        $response = $this->get('/auth/linkedin/callback');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        // Should NOT have linked to existing user; should have created a new user
        $this->assertDatabaseCount('users', 2);
        $newUser = User::where('id', '!=', $existing->id)->first();
        $this->assertNotEquals($existing->id, $newUser->id);
        $this->assertDatabaseHas('oauth_providers', [
            'provider' => 'linkedin',
            'provider_id' => 'li789',
            'user_id' => $newUser->id,
        ]);
        $this->assertDatabaseMissing('oauth_providers', [
            'provider' => 'linkedin',
            'provider_id' => 'li789',
            'user_id' => $existing->id,
        ]);
    }

    public function test_password_nullable_for_oauth_user(): void
    {
        $this->mockSocialiteUser('microsoft', 'ms123', 'msuser@example.com', 'MS User', true);

        $this->get('/auth/microsoft/callback')->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'msuser@example.com')->first();
        $this->assertNull($user->password);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
