<?php

namespace Tests\Feature\Api;

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortLinkShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_link_details_with_clicks(): void
    {
        $link = ShortLink::create([
            'short_code' => 'abc123',
            'original_url' => 'https://example.com/target',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'source' => 'api',
            'clicks' => 42,
            'page_title' => 'Example Target',
        ]);

        $response = $this->getJson('/api/links/'.$link->short_code);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('short_code', 'abc123');
        $response->assertJsonPath('original_url', 'https://example.com/target');
        $response->assertJsonPath('clicks', 42);
        $response->assertJsonPath('page_title', 'Example Target');
        $response->assertJsonPath('source', 'api');
        $response->assertJsonStructure([
            'id',
            'short_url',
            'redirect_mode',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_api_returns_404_for_unknown_code(): void
    {
        $response = $this->getJson('/api/links/unknown');

        $response->assertNotFound();
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Short link not found.');
    }
}
