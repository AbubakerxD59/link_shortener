<?php

namespace App\Services;

use App\Models\ShortLink;

class UrlShortenerService
{
    public function __construct(protected LinkPreviewService $linkPreview) {}

    /**
     * Public shorten (matches Engagyo GeneralController::shortenPublic).
     */
    public function shortenPublic(
        string $originalUrl,
        ?int $userId = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
        ?string $pageTitle = null,
        ?string $thumbnailUrl = null,
        ?string $source = null,
        bool $cloaked = true,
    ): array {
        $source = ShortLink::normalizeSource($source, ShortLink::SOURCE_WEB);
        $redirectMode = ShortLink::redirectModeFromCloak($cloaked);
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
                'redirect_mode' => $redirectMode,
                'cloaked' => $cloaked,
                'existing' => true,
            ];
        }

        $existing = $this->findExistingShortLink($normalizedUrl, $userId, $userAgent, $source, $redirectMode);

        if ($existing) {
            if ($cloaked && (! $existing->page_title || ! $existing->thumbnail_url)) {
                $existing->ensureLinkPreview($this->linkPreview);
            }

            return $this->formatSuccessResponse($existing, true);
        }

        $shortCode = ShortLink::generateUniqueCode(6);
        $preview = $cloaked
            ? $this->resolvePreview($normalizedUrl, $pageTitle, $thumbnailUrl)
            : ['page_title' => null, 'thumbnail_url' => null];

        $shortLink = ShortLink::create([
            'user_id' => $userId,
            'short_code' => $shortCode,
            'original_url' => $normalizedUrl,
            'redirect_mode' => $redirectMode,
            'bridge_delay_seconds' => 0,
            'page_title' => $preview['page_title'],
            'thumbnail_url' => $preview['thumbnail_url'],
            'source' => $source,
            'user_agent' => $userAgent ? substr($userAgent, 0, 65535) : null,
            'ip_address' => $ipAddress,
        ]);

        return $this->formatSuccessResponse($shortLink, false);
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
            'url_cloak' => $shortLink->urlCloakValue(),
            'cloaked' => $shortLink->isCloaked(),
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
        string $redirectMode,
    ): ?ShortLink {
        $query = ShortLink::query()
            ->where('original_url', $normalizedUrl)
            ->where(function ($q) use ($redirectMode) {
                $q->where('redirect_mode', $redirectMode);
                if ($redirectMode === ShortLink::REDIRECT_DIRECT) {
                    $q->orWhereNull('redirect_mode');
                }
            })
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateShortLink(ShortLink $shortLink, array $data): array
    {
        $updates = [];
        $refetchPreview = false;
        $clearPreview = false;
        $explicitPreview = array_key_exists('page_title', $data) || array_key_exists('thumbnail_url', $data);

        $nextRedirectMode = $shortLink->redirect_mode ?? ShortLink::REDIRECT_BRIDGE;
        $nextSource = $shortLink->source ?? ShortLink::SOURCE_API;
        $nextUserId = $shortLink->user_id;

        if (array_key_exists('url_cloak', $data)) {
            $nextRedirectMode = ShortLink::redirectModeFromCloak(ShortLink::cloakedFromUrlCloak($data['url_cloak']));
            $updates['redirect_mode'] = $nextRedirectMode;
            $refetchPreview = $nextRedirectMode === ShortLink::REDIRECT_BRIDGE;
            $clearPreview = $nextRedirectMode === ShortLink::REDIRECT_DIRECT;
        }

        if (array_key_exists('original_url', $data)) {
            $normalizedUrl = ShortLink::normalizeUrl($data['original_url']);
            if ($normalizedUrl === '') {
                return ['success' => false, 'message' => 'Invalid URL'];
            }

            if ($this->urlConflictsWithAnotherLink($shortLink, $normalizedUrl, $nextRedirectMode, $nextSource, $nextUserId)) {
                return ['success' => false, 'message' => 'Another short link already uses this URL.'];
            }

            $updates['original_url'] = $normalizedUrl;
            if ($nextRedirectMode === ShortLink::REDIRECT_BRIDGE) {
                $refetchPreview = true;
            }
        }

        if (array_key_exists('page_title', $data)) {
            $updates['page_title'] = $data['page_title'];
        }

        if (array_key_exists('thumbnail_url', $data)) {
            $updates['thumbnail_url'] = $data['thumbnail_url'];
        }

        if (array_key_exists('source', $data)) {
            $nextSource = ShortLink::normalizeSource($data['source'], $shortLink->source ?? ShortLink::SOURCE_API);
            $updates['source'] = $nextSource;
        }

        if (array_key_exists('user_id', $data)) {
            $nextUserId = $data['user_id'];
            $updates['user_id'] = $nextUserId;
        }

        if ($updates === []) {
            return ['success' => false, 'message' => 'No valid fields to update.'];
        }

        $shortLink->update($updates);
        $shortLink->refresh();

        if ($clearPreview && ! $shortLink->isCloaked()) {
            $shortLink->update(['page_title' => null, 'thumbnail_url' => null]);
            $shortLink->refresh();
        } elseif ($refetchPreview && $shortLink->isCloaked() && ! $explicitPreview) {
            $preview = $this->linkPreview->fetch($shortLink->original_url);
            $shortLink->update([
                'page_title' => $preview['page_title'],
                'thumbnail_url' => $preview['thumbnail_url'],
            ]);
            $shortLink->refresh();
        }

        return $this->linkDetails($shortLink);
    }

    protected function urlConflictsWithAnotherLink(
        ShortLink $shortLink,
        string $normalizedUrl,
        ?string $redirectMode = null,
        ?string $source = null,
        ?int $userId = null,
    ): bool {
        $redirectMode = $redirectMode ?? $shortLink->redirect_mode ?? ShortLink::REDIRECT_BRIDGE;
        $source = $source ?? $shortLink->source ?? ShortLink::SOURCE_API;
        $userId = $userId ?? $shortLink->user_id;

        return ShortLink::query()
            ->where('original_url', $normalizedUrl)
            ->where('id', '!=', $shortLink->id)
            ->where(function ($q) use ($redirectMode) {
                $q->where('redirect_mode', $redirectMode);
                if ($redirectMode === ShortLink::REDIRECT_DIRECT) {
                    $q->orWhereNull('redirect_mode');
                }
            })
            ->where(function ($q) use ($source) {
                $q->where('source', $source);
                if ($source === ShortLink::SOURCE_WEB) {
                    $q->orWhereNull('source');
                }
            })
            ->when(
                $userId !== null,
                fn ($q) => $q->where('user_id', $userId),
                fn ($q) => $q->whereNull('user_id')
            )
            ->exists();
    }

    public function buildShortUrl(string $shortCode): string
    {
        return rtrim(config('app.url'), '/').'/s/'.$shortCode;
    }
}
