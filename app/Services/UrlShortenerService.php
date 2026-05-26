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
    ): array {
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

        $existing = $this->findExistingShortLink($normalizedUrl, $userId, $userAgent);

        if ($existing) {
            $this->ensureBridgeMode($existing);

            if (! $existing->page_title || ! $existing->thumbnail_url) {
                $existing->ensureLinkPreview($this->linkPreview);
            }

            return $this->formatSuccessResponse($existing, true);
        }

        $shortCode = ShortLink::generateUniqueCode(6);
        $preview = $this->linkPreview->fetch($normalizedUrl);

        $shortLink = ShortLink::create([
            'user_id' => $userId,
            'short_code' => $shortCode,
            'original_url' => $normalizedUrl,
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'bridge_delay_seconds' => 5,
            'page_title' => $preview['page_title'],
            'thumbnail_url' => $preview['thumbnail_url'],
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

    protected function formatSuccessResponse(ShortLink $shortLink, bool $existing): array
    {
        return [
            'success' => true,
            'short_url' => $this->buildShortUrl($shortLink->short_code),
            'short_code' => $shortLink->short_code,
            'original_url' => $shortLink->original_url,
            'redirect_mode' => ShortLink::REDIRECT_BRIDGE,
            'page_title' => $shortLink->page_title,
            'thumbnail_url' => $shortLink->thumbnail_url,
            'existing' => $existing,
        ];
    }

    protected function findExistingShortLink(
        string $normalizedUrl,
        ?int $userId,
        ?string $userAgent,
    ): ?ShortLink {
        $query = ShortLink::query()->where('original_url', $normalizedUrl);

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
