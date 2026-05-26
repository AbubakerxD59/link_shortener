<?php

namespace App\Services;

use App\Models\ShortLink;

class UrlShortenerService
{
    public function __construct(protected LinkPreviewService $linkPreview) {}

    /**
     * Public shorten (matches Engagyo GeneralController::shortenPublic).
     * All links use bridge-page cloaking by default.
     */
    public function shortenPublic(
        string $originalUrl,
        ?int $userId = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
        ?string $pageTitle = null,
        ?string $thumbnailUrl = null,
        ?string $source = null,
    ): array {
        $source = ShortLink::normalizeSource($source, ShortLink::SOURCE_WEB);
        $normalizedUrl = ShortLink::normalizeUrl($originalUrl);
        if ($normalizedUrl === '') {
            return ['success' => false, 'message' => 'Invalid URL'];
        }

        $parsed = parse_url($normalizedUrl);
        $path = $parsed['path'] ?? '';
        if (preg_match('#^/s/[a-zA-Z0-9]+$#', $path)) {
            return [
                'success' => true,
                'short_url' => $normalizedUrl,
                'short_code' => '',
                'original_url' => $normalizedUrl,
                'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
                'existing' => true,
            ];
        }

        $existing = $this->findExistingShortLink($normalizedUrl, $userId, $userAgent, $source);

        if ($existing) {
            $this->ensureBridgeMode($existing);

            if (! $existing->page_title || ! $existing->thumbnail_url) {
                $existing->ensureLinkPreview($this->linkPreview);
            }

            return $this->formatSuccessResponse($existing, true);
        }

        $shortCode = ShortLink::generateUniqueCode(6);
        $preview = $this->resolvePreview($normalizedUrl, $pageTitle, $thumbnailUrl);

        $shortLink = ShortLink::create([
            'user_id' => $userId,
            'short_code' => $shortCode,
            'original_url' => $normalizedUrl,
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'bridge_delay_seconds' => 0,
            'page_title' => $preview['page_title'],
            'thumbnail_url' => $preview['thumbnail_url'],
            'source' => $source,
            'user_agent' => $userAgent ? substr($userAgent, 0, 65535) : null,
            'ip_address' => $ipAddress,
        ]);

        return $this->formatSuccessResponse($shortLink, false);
    }

    protected function ensureBridgeMode(ShortLink $shortLink): void
    {
        if ($shortLink->redirect_mode !== ShortLink::REDIRECT_BRIDGE) {
            $shortLink->update(['redirect_mode' => ShortLink::REDIRECT_BRIDGE]);
            $shortLink->refresh();
        }
    }

    /**
     * @return array{page_title: ?string, thumbnail_url: ?string}
     */
    protected function resolvePreview(string $url, ?string $pageTitle, ?string $thumbnailUrl): array
    {
        if ($pageTitle !== null || $thumbnailUrl !== null) {
            $fetched = $this->linkPreview->fetch($url);

            return [
                'page_title' => $pageTitle ?? $fetched['page_title'],
                'thumbnail_url' => $thumbnailUrl ?? $fetched['thumbnail_url'],
            ];
        }

        return $this->linkPreview->fetch($url);
    }

    /**
     * @return array<string, mixed>
     */
    public function linkDetails(ShortLink $shortLink): array
    {
        return [
            'success' => true,
            'id' => $shortLink->id,
            'short_url' => $this->buildShortUrl($shortLink->short_code),
            'short_code' => $shortLink->short_code,
            'original_url' => $shortLink->original_url,
            'redirect_mode' => $shortLink->redirect_mode ?? ShortLink::REDIRECT_BRIDGE,
            'bridge_delay_seconds' => (int) ($shortLink->bridge_delay_seconds ?? 0),
            'page_title' => $shortLink->page_title,
            'thumbnail_url' => $shortLink->thumbnail_url,
            'source' => $shortLink->source,
            'clicks' => (int) $shortLink->clicks,
            'user_id' => $shortLink->user_id,
            'created_at' => $shortLink->created_at?->toIso8601String(),
            'updated_at' => $shortLink->updated_at?->toIso8601String(),
        ];
    }

    protected function formatSuccessResponse(ShortLink $shortLink, bool $existing): array
    {
        return array_merge($this->linkDetails($shortLink), ['existing' => $existing]);
    }

    protected function findExistingShortLink(
        string $normalizedUrl,
        ?int $userId,
        ?string $userAgent,
        string $source,
    ): ?ShortLink {
        $query = ShortLink::query()
            ->where('original_url', $normalizedUrl)
            ->where(function ($q) use ($source) {
                $q->where('source', $source);
                if ($source === ShortLink::SOURCE_WEB) {
                    $q->orWhereNull('source');
                }
            });

        if ($userId !== null) {
            return $query->where('user_id', $userId)->first();
        }

        if ($userAgent !== null && $userAgent !== '') {
            return $query->whereNull('user_id')->where('user_agent', $userAgent)->first();
        }

        return $query->whereNull('user_id')->whereNull('user_agent')->first();
    }

    public function buildShortUrl(string $shortCode): string
    {
        return rtrim(config('app.url'), '/').'/s/'.$shortCode;
    }
}
