<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinkPreviewService
{
    private const MAX_HTML_BYTES = 524288;

    /** @var list<string> */
    private const TITLE_KEYS = [
        'og:title',
        'twitter:title',
    ];

    /** @var list<string> */
    private const IMAGE_KEYS = [
        'og:image',
        'twitter:image',
        'twitter:image:src',
    ];

    /**
     * @return array{page_title: ?string, thumbnail_url: ?string}
     */
    public function fetch(string $url): array
    {
        if (! preg_match('#^https?://#i', $url)) {
            return ['page_title' => null, 'thumbnail_url' => null];
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; LinkShortener/1.0; +'.config('app.url').')',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (! $response->successful()) {
                return $this->fallback($url);
            }

            $html = substr($response->body(), 0, self::MAX_HTML_BYTES);
            $title = $this->extractMeta($html, self::TITLE_KEYS) ?? $this->extractTitleTag($html);
            $image = $this->extractMeta($html, self::IMAGE_KEYS);

            if ($image !== null) {
                $image = $this->resolveUrl($url, $image);
                if (! preg_match('#^https?://#i', $image)) {
                    $image = null;
                }
            }

            $title = $this->cleanText($title);
            $title = $title !== null ? Str::limit($title, 500, '') : null;

            if ($title === null && $image === null) {
                return $this->fallback($url);
            }

            return [
                'page_title' => $title,
                'thumbnail_url' => $image,
            ];
        } catch (\Throwable) {
            return $this->fallback($url);
        }
    }

    /**
     * @return array{page_title: ?string, thumbnail_url: ?string}
     */
    private function fallback(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);

        return [
            'page_title' => $host ? $this->cleanText($host) : null,
            'thumbnail_url' => null,
        ];
    }

    /**
     * @param  list<string>  $keys
     */
    private function extractMeta(string $html, array $keys): ?string
    {
        foreach ($keys as $key) {
            $patterns = [
                '/<meta[^>]+(?:property|name)=["\']'.preg_quote($key, '/').'["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']'.preg_quote($key, '/').'["\']/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    return $this->cleanText(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
            }
        }

        return null;
    }

    private function extractTitleTag(string $html): ?string
    {
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/is', $html, $matches)) {
            return $this->cleanText(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return null;
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value !== '' ? $value : null;
    }

    private function resolveUrl(string $baseUrl, string $relative): string
    {
        $relative = trim($relative);
        if ($relative === '') {
            return $relative;
        }

        if (preg_match('#^https?://#i', $relative)) {
            return $relative;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';

        if (str_starts_with($relative, '//')) {
            return $scheme.':'.$relative;
        }

        $origin = $scheme.'://'.$host;

        if (str_starts_with($relative, '/')) {
            return $origin.$relative;
        }

        $path = $parsed['path'] ?? '/';
        $directory = str_ends_with($path, '/') ? $path : preg_replace('#/[^/]*$#', '/', $path);

        return $origin.$directory.ltrim($relative, '/');
    }
}
