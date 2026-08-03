<?php

namespace Tests\Feature\Api;

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShortLinkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_updates_destination_url(): void
    {
        Http::fake();

        $link = ShortLink::create([
            'short_code' => 'upd001',
            'original_url' => 'https://example.com/old',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'source' => 'api',
        ]);

        $response = $this->patchJson('/api/links/'.$link->short_code, [
            'original_url' => 'https://example.com/new',
        ]);

        $response->assertOk();
        $response->assertJsonPath('original_url', 'https://example.com/new');
        $this->assertDatabaseHas('short_links', [
            'short_code' => 'upd001',
            'original_url' => 'https://example.com/new',
        ]);
    }

    public function test_api_updates_url_cloak_when_sibling_has_same_url(): void
    {
        Http::fake();

        ShortLink::create([
            'short_code' => 'sib001',
            'original_url' => 'https://example.com/shared',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'source' => 'engagyo',
            'user_id' => 42,
        ]);

        $link = ShortLink::create([
            'short_code' => 'sib002',
            'original_url' => 'https://example.com/shared',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'page_title' => 'Preview',
            'source' => 'engagyo',
            'user_id' => 42,
        ]);

        $response = $this->patchJson('/api/links/'.$link->short_code, [
            'original_url' => 'https://example.com/shared',
            'url_cloak' => 0,
            'source' => 'engagyo',
            'user_id' => 42,
        ]);

        $response->assertOk();
        $response->assertJsonPath('url_cloak', 0);
        $response->assertJsonPath('redirect_mode', 'direct');
    }

    public function test_api_allows_same_url_on_different_custom_domains(): void
    {
        Http::fake();

        $domainA = \App\Models\CustomDomain::create([
            'user_id' => 1,
            'domain' => 'go.a.test',
            'domain_type' => \App\Models\CustomDomain::TYPE_SUBDOMAIN,
            'base_domain' => 'a.test',
            'subdomain_prefix' => 'go',
            'verification_token' => 'token-a',
            'verified_at' => now(),
        ]);
        $domainB = \App\Models\CustomDomain::create([
            'user_id' => 1,
            'domain' => 'go.b.test',
            'domain_type' => \App\Models\CustomDomain::TYPE_SUBDOMAIN,
            'base_domain' => 'b.test',
            'subdomain_prefix' => 'go',
            'verification_token' => 'token-b',
            'verified_at' => now(),
        ]);

        ShortLink::create([
            'short_code' => 'dom001',
            'original_url' => 'https://example.com/dest',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'source' => 'engagyo',
            'user_id' => 7,
            'custom_domain_id' => $domainA->id,
        ]);

        $link = ShortLink::create([
            'short_code' => 'dom002',
            'original_url' => 'https://example.com/other',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'source' => 'engagyo',
            'user_id' => 7,
            'custom_domain_id' => $domainB->id,
        ]);

        $response = $this->patchJson('/api/links/'.$link->short_code, [
            'original_url' => 'https://example.com/dest',
            'url_cloak' => 0,
            'source' => 'engagyo',
            'user_id' => 7,
        ]);

        $response->assertOk();
        $response->assertJsonPath('original_url', 'https://example.com/dest');
    }


    public function test_api_updates_preview_fields(): void
    {
        $link = ShortLink::create([
            'short_code' => 'upd003',
            'original_url' => 'https://example.com/page',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'source' => 'api',
        ]);

        $response = $this->patchJson('/api/links/'.$link->short_code, [
            'page_title' => 'New Title',
            'thumbnail_url' => 'https://cdn.example.com/new.jpg',
        ]);

        $response->assertOk();
        $response->assertJsonPath('page_title', 'New Title');
        $response->assertJsonPath('thumbnail_url', 'https://cdn.example.com/new.jpg');
    }

    public function test_api_returns_404_when_updating_unknown_code(): void
    {
        $response = $this->patchJson('/api/links/missing', [
            'original_url' => 'https://example.com/page',
        ]);

        $response->assertNotFound();
    }

    public function test_api_rejects_empty_update_body(): void
    {
        $link = ShortLink::create([
            'short_code' => 'upd004',
            'original_url' => 'https://example.com/page',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
        ]);

        $response = $this->patchJson('/api/links/'.$link->short_code, []);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'No fields to update.');
    }
}
