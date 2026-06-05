<?php

namespace Tests\Feature;

use App\Models\CustomDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShortenDomainSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_domain_dropdown_options(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $user = User::factory()->create();
        CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token123',
            'verified_at' => now(),
        ]);
        CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'links.brand.test',
            'verification_token' => 'token456',
            'verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Redirect domain')
            ->assertSee('127.0.0.1 (default)')
            ->assertSee('go.brand.test')
            ->assertDontSee('links.brand.test');
    }

    public function test_shorten_rejects_unverified_domain_selection(): void
    {
        Http::fake();

        $user = User::factory()->create();
        $pending = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'pending.brand.test',
            'verification_token' => 'token123',
        ]);

        $this->actingAs($user)
            ->postJson('/shorten', [
                'original_url' => 'https://example.com/page',
                'custom_domain_id' => $pending->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Select a valid verified branded domain.');
    }
}
