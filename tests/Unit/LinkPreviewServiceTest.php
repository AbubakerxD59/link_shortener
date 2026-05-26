<?php

namespace Tests\Unit;

use App\Services\LinkPreviewService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkPreviewServiceTest extends TestCase
{
    public function test_extracts_open_graph_title_and_image(): void
    {
        Http::fake([
            'https://example.com/article' => Http::response(<<<'HTML'
                <html>
                <head>
                    <meta property="og:title" content="Example Article Title" />
                    <meta property="og:image" content="https://cdn.example.com/hero.jpg" />
                </head>
                </html>
            HTML, 200),
        ]);

        $preview = (new LinkPreviewService)->fetch('https://example.com/article');

        $this->assertSame('Example Article Title', $preview['page_title']);
        $this->assertSame('https://cdn.example.com/hero.jpg', $preview['thumbnail_url']);
    }

    public function test_falls_back_to_host_when_fetch_fails(): void
    {
        Http::fake([
            'https://example.com/page' => Http::response('', 500),
        ]);

        $preview = (new LinkPreviewService)->fetch('https://example.com/page');

        $this->assertSame('example.com', $preview['page_title']);
        $this->assertNull($preview['thumbnail_url']);
    }
}
