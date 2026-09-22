<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login_when_guest(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_root_redirects_to_dashboard_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_welcome_view_not_rendered(): void
    {
        // Ensure welcome view is not returned for either state; both should redirect
        $this->get('/')->assertRedirect();
        $user = User::factory()->create();
        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }
}
