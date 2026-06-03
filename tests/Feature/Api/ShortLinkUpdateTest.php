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

    public function test_api_updates_url_cloak_to_direct(): void
    {
        Http::fake();

        $link = ShortLink::create([
            'short_code' => 'upd002',
            'original_url' => 'https://example.com/page',
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'page_title' => 'Old Title',
            'thumbnail_url' => 'https://cdn.example.com/old.jpg',
            'source' => 'api',
        ]);

        $response = $this->patchJson('/api/links/'.$link->short_code, [
            'url_cloak' => 0,
        ]);

        $response->assertOk();
        $response->assertJsonPath('url_cloak', 0);
        $response->assertJsonPath('redirect_mode', 'direct');

        $link->refresh();
        $this->assertNull($link->page_title);
        $this->assertNull($link->thumbnail_url);
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
