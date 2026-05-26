<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShortLinkCloakTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_short_links_use_bridge_page(): void
    {
        $link = ShortLink::create([
            'short_code' => 'abc123',
            'original_url' => 'https://example.com/target',
            'redirect_mode' => ShortLink::REDIRECT_DIRECT,
            'page_title' => 'Target Page',
        ]);

        $response = $this->get(route('short.redirect', ['code' => $link->short_code]));

        $response->assertOk();
        $response->assertSee('Target Page', false);
        $response->assertSee('Continue to this link?', false);
        $this->assertSame(1, $link->fresh()->clicks);
    }

    public function test_bridge_cloak_renders_preview_and_continue_page(): void
    {
        $link = ShortLink::create([
            'short_code' => 'bridge1',
            'original_url' => 'https://example.com/secret',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'bridge_delay_seconds' => 5,
            'page_title' => 'Secret Page',
            'thumbnail_url' => 'https://cdn.example.com/thumb.jpg',
        ]);

        $response = $this->get(route('short.redirect', ['code' => $link->short_code]));

        $response->assertOk();
        $response->assertSee('Secret Page', false);
        $response->assertSee('https://cdn.example.com/thumb.jpg', false);
        $response->assertSee('Continue to this link?', false);
    }

    public function test_shorten_always_creates_bridge_link_with_preview(): void
    {
        Http::fake([
            'https://example.org/page' => Http::response(<<<'HTML'
                <html><head>
                <meta property="og:title" content="Org Page" />
                <meta property="og:image" content="https://example.org/cover.png" />
                </head></html>
            HTML, 200),
        ]);

        $response = $this->postJson(route('shorten'), [
            'original_url' => 'https://example.org/page',
        ]);

        $response->assertOk();
        $response->assertJsonPath('redirect_mode', 'bridge');
        $response->assertJsonPath('page_title', 'Org Page');
        $response->assertJsonPath('thumbnail_url', 'https://example.org/cover.png');

        $code = $response->json('short_code');
        $this->assertDatabaseHas('short_links', [
            'short_code' => $code,
            'redirect_mode' => 'bridge',
            'original_url' => 'https://example.org/page',
            'page_title' => 'Org Page',
            'thumbnail_url' => 'https://example.org/cover.png',
        ]);
    }
}
