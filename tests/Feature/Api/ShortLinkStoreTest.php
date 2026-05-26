<?php

namespace Tests\Feature\Api;

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShortLinkStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_stores_link_from_json_body(): void
    {
        Http::fake([
            'https://example.com/page' => Http::response('<html><head><title>Example</title></head></html>', 200),
        ]);

        $response = $this->postJson('/api/links', [
            'original_url' => 'https://example.com/page',
            'user_id' => 42,
            'user_agent' => 'ApiClient/1.0',
            'ip_address' => '127.0.0.1',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('redirect_mode', 'bridge');
        $response->assertJsonStructure(['id', 'short_code', 'short_url', 'original_url']);

        $this->assertDatabaseHas('short_links', [
            'original_url' => 'https://example.com/page',
            'user_id' => 42,
            'user_agent' => 'ApiClient/1.0',
            'ip_address' => '127.0.0.1',
            'redirect_mode' => 'bridge',
            'source' => 'api',
        ]);
    }

    public function test_api_accepts_optional_preview_fields(): void
    {
        Http::fake();

        $response = $this->postJson('/api/links', [
            'original_url' => 'https://example.com/custom',
            'page_title' => 'Custom Title',
            'thumbnail_url' => 'https://cdn.example.com/img.jpg',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('page_title', 'Custom Title');
        $response->assertJsonPath('thumbnail_url', 'https://cdn.example.com/img.jpg');
    }

    public function test_api_accepts_custom_source(): void
    {
        Http::fake();

        $response = $this->postJson('/api/links', [
            'original_url' => 'https://example.com/from-engagyo',
            'source' => 'engagyo',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('source', 'engagyo');
        $this->assertDatabaseHas('short_links', [
            'original_url' => 'https://example.com/from-engagyo',
            'source' => 'engagyo',
        ]);
    }

    public function test_api_validates_original_url(): void
    {
        $response = $this->postJson('/api/links', [
            'original_url' => 'not-a-url',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['original_url']);
    }

    public function test_api_returns_existing_link(): void
    {
        Http::fake();

        ShortLink::create([
            'short_code' => 'exist1',
            'original_url' => 'https://example.com/dup',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'user_id' => 1,
            'source' => 'api',
        ]);

        $response = $this->postJson('/api/links', [
            'original_url' => 'https://example.com/dup',
            'user_id' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonPath('existing', true);
        $response->assertJsonPath('short_code', 'exist1');
    }
}
