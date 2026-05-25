<?php

namespace App\Services;

use App\Models\ShortLink;

class UrlShortenerService
{
    /**
     * Public shorten (matches Engagyo GeneralController::shortenPublic).
     */
    public function shortenPublic(string $originalUrl, ?int $userId = null, ?string $userAgent = null, ?string $ipAddress = null): array
    {
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
                'existing' => true,
            ];
        }

        $existing = $this->findExistingShortLink($normalizedUrl, $userId, $userAgent);

        if ($existing) {
            return [
                'success' => true,
                'short_url' => $this->buildShortUrl($existing->short_code),
                'short_code' => $existing->short_code,
                'original_url' => $existing->original_url,
                'existing' => true,
            ];
        }

        $shortCode = ShortLink::generateUniqueCode(6);

        ShortLink::create([
            'user_id' => $userId,
            'short_code' => $shortCode,
            'original_url' => $normalizedUrl,
            'user_agent' => $userAgent ? substr($userAgent, 0, 65535) : null,
            'ip_address' => $ipAddress,
        ]);

        return [
            'success' => true,
            'short_url' => $this->buildShortUrl($shortCode),
            'short_code' => $shortCode,
            'original_url' => $normalizedUrl,
            'existing' => false,
        ];
    }

    protected function findExistingShortLink(string $normalizedUrl, ?int $userId, ?string $userAgent): ?ShortLink
    {
        if ($userId !== null) {
            return ShortLink::where('user_id', $userId)
                ->where('original_url', $normalizedUrl)
                ->first();
        }

        if ($userAgent !== null && $userAgent !== '') {
            return ShortLink::whereNull('user_id')
                ->where('user_agent', $userAgent)
                ->where('original_url', $normalizedUrl)
                ->first();
        }

        return ShortLink::whereNull('user_id')
            ->whereNull('user_agent')
            ->where('original_url', $normalizedUrl)
            ->first();
    }

    public function buildShortUrl(string $shortCode): string
    {
        return rtrim(config('app.url'), '/').'/s/'.$shortCode;
    }
}
