<?php

namespace Tests\Feature;

use App\Models\CustomDomain;
use App\Models\ShortLink;
use App\Models\User;
use App\Services\DnsLookup;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class CustomDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function mockVerifiedDns(string $domain, string $cnameTarget = '127.0.0.1'): void
    {
        $dns = Mockery::mock(DnsLookup::class);
        $dns->shouldReceive('cnameTargets')
            ->with($domain)
            ->andReturn([$cnameTarget]);

        $this->instance(DnsLookup::class, $dns);
    }

    public function test_authenticated_user_can_view_branded_domains_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('branded-domains.index'))
            ->assertOk()
            ->assertSee('Branded domains')
            ->assertSee('Add a domain');
    }

    public function test_authenticated_user_can_save_custom_domain(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('branded-domains.store'), ['domain' => 'go.brand.test'])
            ->assertRedirect()
            ->assertSessionHas('domain_status');

        $this->assertDatabaseHas('custom_domains', [
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verified_at' => null,
            'is_default' => true,
        ]);
    }

    public function test_user_can_add_multiple_domains(): void
    {
        $user = User::factory()->create();

        CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token1',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->post(route('branded-domains.store'), ['domain' => 'links.brand.test'])
            ->assertRedirect();

        $this->assertDatabaseHas('custom_domains', [
            'user_id' => $user->id,
            'domain' => 'links.brand.test',
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->get(route('branded-domains.index'))
            ->assertOk()
            ->assertSee('go.brand.test')
            ->assertSee('links.brand.test');
    }

    public function test_domain_details_page_shows_connection_steps(): void
    {
        $user = User::factory()->create();
        $domain = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token123',
        ]);

        config(['app.url' => 'http://127.0.0.1:8000']);

        $this->actingAs($user)
            ->get(route('branded-domains.show', $domain))
            ->assertOk()
            ->assertSee('go.brand.test')
            ->assertSee('DNS setup required for go.brand.test')
            ->assertSee('127.0.0.1')
            ->assertSee("'go'");
    }

    public function test_guest_cannot_manage_custom_domain(): void
    {
        $this->post(route('branded-domains.store'), ['domain' => 'go.brand.test'])
            ->assertRedirect(route('login'));
    }

    public function test_verify_marks_domain_as_verified_when_dns_matches(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $user = User::factory()->create();
        $customDomain = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token123',
        ]);

        $this->mockVerifiedDns('go.brand.test', '127.0.0.1');

        $this->actingAs($user)
            ->post(route('branded-domains.verify', $customDomain))
            ->assertRedirect(route('branded-domains.show', $customDomain))
            ->assertSessionHas('domain_status');

        $customDomain->refresh();
        $this->assertNotNull($customDomain->verified_at);
    }

    public function test_short_urls_use_selected_custom_domain(): void
    {
        config(['custom_domains.scheme' => 'https']);

        $user = User::factory()->create();
        $branded = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'links.brand.test',
            'verification_token' => 'token456',
            'verified_at' => now(),
            'is_default' => true,
        ]);

        $shortLink = ShortLink::create([
            'user_id' => $user->id,
            'custom_domain_id' => $branded->id,
            'short_code' => 'abc123',
            'original_url' => 'https://example.com/page',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'source' => ShortLink::SOURCE_WEB,
        ]);

        $shortLink->load('customDomain');
        $details = app(UrlShortenerService::class)->linkDetails($shortLink);

        $this->assertSame('https://links.brand.test/s/abc123', $details['short_url']);
        $this->assertSame('links.brand.test', $details['link_domain']);
    }

    public function test_short_urls_without_custom_domain_use_app_host(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $user = User::factory()->create();
        $shortLink = ShortLink::create([
            'user_id' => $user->id,
            'short_code' => 'abc123',
            'original_url' => 'https://example.com/page',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'source' => ShortLink::SOURCE_WEB,
        ]);

        $details = app(UrlShortenerService::class)->linkDetails($shortLink);

        $this->assertSame('http://127.0.0.1:8000/s/abc123', $details['short_url']);
        $this->assertSame('127.0.0.1', $details['link_domain']);
    }

    public function test_custom_domain_only_serves_owner_links(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $branded = CustomDomain::create([
            'user_id' => $owner->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token123',
            'verified_at' => now(),
        ]);

        $ownerLink = ShortLink::create([
            'user_id' => $owner->id,
            'custom_domain_id' => $branded->id,
            'short_code' => 'owner1',
            'original_url' => 'https://example.com/owner',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'source' => ShortLink::SOURCE_WEB,
        ]);

        ShortLink::create([
            'user_id' => $other->id,
            'short_code' => 'other1',
            'original_url' => 'https://example.com/other',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'source' => ShortLink::SOURCE_WEB,
        ]);

        $this->get('http://go.brand.test/s/'.$ownerLink->short_code)
            ->assertRedirect('https://example.com/owner');

        $this->get('http://go.brand.test/s/other1')
            ->assertNotFound();
    }

    public function test_user_can_set_verified_domain_as_default(): void
    {
        $user = User::factory()->create();
        $primary = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token1',
            'verified_at' => now(),
            'is_default' => true,
        ]);
        $secondary = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'links.brand.test',
            'verification_token' => 'token2',
            'verified_at' => now(),
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->post(route('branded-domains.default', $secondary))
            ->assertRedirect();

        $primary->refresh();
        $secondary->refresh();

        $this->assertFalse($primary->is_default);
        $this->assertTrue($secondary->is_default);
    }

    public function test_user_can_remove_custom_domain(): void
    {
        $user = User::factory()->create();
        $customDomain = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token123',
            'verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete(route('branded-domains.destroy', $customDomain))
            ->assertRedirect(route('branded-domains.index'))
            ->assertSessionHas('domain_status');

        $this->assertDatabaseMissing('custom_domains', [
            'id' => $customDomain->id,
        ]);
    }

    public function test_reserved_app_host_is_rejected(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('branded-domains.store'), ['domain' => '127.0.0.1'])
            ->assertSessionHasErrors('domain');
    }

    public function test_user_cannot_view_another_users_domain_details(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $domain = CustomDomain::create([
            'user_id' => $owner->id,
            'domain' => 'go.brand.test',
            'verification_token' => 'token123',
        ]);

        $this->actingAs($other)
            ->get(route('branded-domains.show', $domain))
            ->assertForbidden();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
