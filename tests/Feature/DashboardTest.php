<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome, '.$user->name)
            ->assertSee('Shorten a link');
    }

    public function test_dashboard_lists_only_user_links(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        ShortLink::create([
            'user_id' => $user->id,
            'short_code' => 'mine01',
            'original_url' => 'https://example.com/mine',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'source' => ShortLink::SOURCE_WEB,
        ]);

        ShortLink::create([
            'user_id' => $other->id,
            'short_code' => 'other1',
            'original_url' => 'https://example.com/other',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'source' => ShortLink::SOURCE_WEB,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('mine01')
            ->assertSee('https://example.com/mine')
            ->assertDontSee('other1')
            ->assertDontSee('https://example.com/other');
    }

    public function test_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'dash@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('login'), [
            'email' => 'dash@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_register_redirects_to_dashboard(): void
    {
        $this->post(route('register'), [
            'name' => 'Panel User',
            'email' => 'panel@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_dashboard_shorten_adds_user_link(): void
    {
        Http::fake([
            'https://example.com/dashboard' => Http::response('<html><head><title>Dashboard</title></head></html>', 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/shorten', [
            'original_url' => 'https://example.com/dashboard',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('short_links', [
            'user_id' => $user->id,
            'original_url' => 'https://example.com/dashboard',
        ]);
    }
}
