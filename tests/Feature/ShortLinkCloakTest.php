<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShortLinkCloakTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_link_redirects_immediately(): void
    {
        $link = ShortLink::create([
            'short_code' => 'direct1',
            'original_url' => 'https://example.com/target',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
        ]);

        $response = $this->get(route('short.redirect', ['code' => $link->short_code]));

        $response->assertRedirect('https://example.com/target');
        $this->assertSame(1, $link->fresh()->clicks);
    }

    public function test_cloaked_link_shows_bridge_page(): void
    {
        $link = ShortLink::create([
            'short_code' => 'bridge1',
            'original_url' => 'https://example.com/secret',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'page_title' => 'Secret Page',
            'thumbnail_url' => 'https://cdn.example.com/thumb.jpg',
        ]);

        $response = $this->get(route('short.redirect', ['code' => $link->short_code]));

        $response->assertOk();
        $response->assertSee('Secret Page', false);
        $response->assertSee('Continue to this link?', false);
    }

    public function test_shorten_with_cloak_enabled_creates_bridge_link(): void
    {
        Http::fake([
            'https://example.org/page' => Http::response(<<<'HTML'
                <html><head>
                <meta property="og:title" content="Org Page" />
                </head></html>
            HTML, 200),
        ]);

        $response = $this->postJson(route('shorten'), [
            'original_url' => 'https://example.org/page',
            'cloak' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('cloaked', true);
        $response->assertJsonPath('redirect_mode', 'bridge');

        $this->assertDatabaseHas('short_links', [
            'original_url' => 'https://example.org/page',
            'redirect_mode' => 'bridge',
        ]);
    }

    public function test_shorten_with_cloak_disabled_creates_direct_link(): void
    {
        Http::fake();

        $response = $this->postJson(route('shorten'), [
            'original_url' => 'https://example.org/direct',
            'cloak' => false,
        ]);

        $response->assertOk();
        $response->assertJsonPath('cloaked', false);
        $response->assertJsonPath('redirect_mode', 'direct');

        $this->assertDatabaseHas('short_links', [
            'original_url' => 'https://example.org/direct',
            'redirect_mode' => 'direct',
        ]);
    }
}
