<?php

namespace Tests\Feature;

use App\Models\CustomDomain;
use App\Models\ShortLink;
use App\Models\User;
use App\Services\DnsLookup;
use App\Services\SslProbe;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CustomDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function mockVerifiedSubdomainDns(string $domain, string $cnameTarget = '127.0.0.1', bool $sslOk = true): void
    {
        $dns = Mockery::mock(DnsLookup::class);
        $dns->shouldReceive('cnameTargets')
            ->with($domain)
            ->andReturn([$cnameTarget]);

        $ssl = Mockery::mock(SslProbe::class);
        $ssl->shouldReceive('domainHasWorkingHttps')
            ->with($domain)
            ->andReturn($sslOk);

        $this->instance(DnsLookup::class, $dns);
        $this->instance(SslProbe::class, $ssl);
    }


    protected function makeSubdomain(array $overrides = []): CustomDomain
    {
        return CustomDomain::create(array_merge([
            'user_id' => $overrides['user_id'] ?? User::factory()->create()->id,
            'domain' => 'go.brand.test',
            'domain_type' => CustomDomain::TYPE_SUBDOMAIN,
            'base_domain' => 'brand.test',
            'subdomain_prefix' => 'go',
            'verification_token' => 'token123',
        ], $overrides));
    }

    public function test_authenticated_user_can_view_branded_domains_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('branded-domains.index'))
            ->assertOk()
            ->assertSee('Branded domains')
            ->assertSee('Add a domain')
            ->assertSee('Use a subdomain')
            ->assertSee('Use the main domain');
    }

    public function test_authenticated_user_can_save_subdomain(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('branded-domains.store'), [
                'base_domain' => 'brand.test',
                'domain_type' => 'subdomain',
                'subdomain_prefix' => 'go',
            ])
            ->assertRedirect()
            ->assertSessionHas('domain_status');

        $this->assertDatabaseHas('custom_domains', [
            'user_id' => $user->id,
            'domain' => 'go.brand.test',
            'domain_type' => 'subdomain',
            'base_domain' => 'brand.test',
            'subdomain_prefix' => 'go',
            'verified_at' => null,
            'is_default' => true,
        ]);
    }

    public function test_authenticated_user_can_save_main_domain(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('branded-domains.store'), [
                'base_domain' => 'brand.test',
                'domain_type' => 'apex',
            ])
            ->assertRedirect()
            ->assertSessionHas('domain_status');

        $this->assertDatabaseHas('custom_domains', [
            'user_id' => $user->id,
            'domain' => 'brand.test',
            'domain_type' => 'apex',
            'base_domain' => 'brand.test',
            'subdomain_prefix' => null,
        ]);
    }

    public function test_user_can_add_multiple_domains(): void
    {
        $user = User::factory()->create();

        $this->makeSubdomain([
            'user_id' => $user->id,
            'verification_token' => 'token1',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->post(route('branded-domains.store'), [
                'base_domain' => 'brand.test',
                'domain_type' => 'subdomain',
                'subdomain_prefix' => 'links',
            ])
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

    public function test_subdomain_details_page_shows_cname_steps(): void
    {
        $user = User::factory()->create();
        $domain = $this->makeSubdomain(['user_id' => $user->id]);

        config(['app.url' => 'http://127.0.0.1:8000']);

        $this->actingAs($user)
            ->get(route('branded-domains.show', $domain))
            ->assertOk()
            ->assertSee('go.brand.test')
            ->assertSee('DNS setup required for go.brand.test')
            ->assertSee('127.0.0.1')
            ->assertSee("'go'");
    }

    public function test_main_domain_details_page_shows_cname_with_at_host(): void
    {
        $user = User::factory()->create();
        $domain = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'brand.test',
            'domain_type' => CustomDomain::TYPE_APEX,
            'base_domain' => 'brand.test',
            'subdomain_prefix' => null,
            'verification_token' => 'token123',
        ]);

        config(['app.url' => 'http://127.0.0.1:8000']);

        $this->actingAs($user)
            ->get(route('branded-domains.show', $domain))
            ->assertOk()
            ->assertSee('DNS setup required for brand.test')
            ->assertSee('127.0.0.1')
            ->assertSee("'@'")
            ->assertSee('CNAME');
    }

    public function test_guest_cannot_manage_custom_domain(): void
    {
        $this->post(route('branded-domains.store'), [
            'base_domain' => 'brand.test',
            'domain_type' => 'subdomain',
            'subdomain_prefix' => 'go',
        ])->assertRedirect(route('login'));
    }

    public function test_verify_marks_subdomain_as_verified_when_cname_matches(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $user = User::factory()->create();
        $customDomain = $this->makeSubdomain(['user_id' => $user->id]);

        $this->mockVerifiedSubdomainDns('go.brand.test', '127.0.0.1');

        $this->actingAs($user)
            ->post(route('branded-domains.verify', $customDomain))
            ->assertRedirect(route('branded-domains.show', $customDomain))
            ->assertSessionHas('domain_status');

        $customDomain->refresh();
        $this->assertNotNull($customDomain->verified_at);
    }

    public function test_verify_marks_main_domain_as_verified_when_cname_matches(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $user = User::factory()->create();
        $customDomain = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'brand.test',
            'domain_type' => CustomDomain::TYPE_APEX,
            'base_domain' => 'brand.test',
            'subdomain_prefix' => null,
            'verification_token' => 'token123',
        ]);

        $this->mockVerifiedSubdomainDns('brand.test', '127.0.0.1');

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
            'domain_type' => CustomDomain::TYPE_SUBDOMAIN,
            'base_domain' => 'brand.test',
            'subdomain_prefix' => 'links',
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

        $this->assertSame('https://links.brand.test/abc123', $details['short_url']);
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

        $this->assertSame('http://127.0.0.1:8000/abc123', $details['short_url']);
        $this->assertSame('127.0.0.1', $details['link_domain']);
    }

    public function test_custom_domain_only_serves_owner_links(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $branded = $this->makeSubdomain([
            'user_id' => $owner->id,
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

        $this->get('http://go.brand.test/'.$ownerLink->short_code)
            ->assertRedirect('https://example.com/owner');

        $this->get('http://go.brand.test/other1')
            ->assertNotFound();
    }

    public function test_user_can_set_verified_domain_as_default(): void
    {
        $user = User::factory()->create();
        $primary = $this->makeSubdomain([
            'user_id' => $user->id,
            'verification_token' => 'token1',
            'verified_at' => now(),
            'is_default' => true,
        ]);
        $secondary = CustomDomain::create([
            'user_id' => $user->id,
            'domain' => 'links.brand.test',
            'domain_type' => CustomDomain::TYPE_SUBDOMAIN,
            'base_domain' => 'brand.test',
            'subdomain_prefix' => 'links',
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
        $customDomain = $this->makeSubdomain([
            'user_id' => $user->id,
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
            ->post(route('branded-domains.store'), [
                'base_domain' => '127.0.0.1',
                'domain_type' => 'apex',
            ])
            ->assertSessionHasErrors('domain');
    }

    public function test_user_cannot_view_another_users_domain_details(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $domain = $this->makeSubdomain(['user_id' => $owner->id]);

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
