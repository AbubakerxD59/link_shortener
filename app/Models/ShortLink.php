<?php

namespace App\Models;

use App\Services\LinkPreviewService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortLink extends Model
{
    public const REDIRECT_DIRECT = 'direct';

    public const REDIRECT_BRIDGE = 'bridge';

    public const REDIRECT_META = 'meta';

    public const SOURCE_WEB = 'web';

    public const SOURCE_API = 'api';

    /** @var list<string> */
    public const REDIRECT_MODES = [
        self::REDIRECT_DIRECT,
        self::REDIRECT_BRIDGE,
        self::REDIRECT_META,
    ];

    protected $fillable = [
        'user_id',
        'custom_domain_id',
        'short_code',
        'original_url',
        'redirect_mode',
        'bridge_delay_seconds',
        'page_title',
        'thumbnail_url',
        'source',
        'user_agent',
        'ip_address',
        'clicks',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'bridge_delay_seconds' => 'integer',
    ];

    public function customDomain(): BelongsTo
    {
        return $this->belongsTo(CustomDomain::class);
    }

    public function isCloaked(): bool
    {
        return ($this->redirect_mode ?? self::REDIRECT_BRIDGE) === self::REDIRECT_BRIDGE;
    }

    public static function redirectModeFromCloak(bool $cloaked): string
    {
        return $cloaked ? self::REDIRECT_BRIDGE : self::REDIRECT_DIRECT;
    }

    public static function cloakedFromUrlCloak(int|string|bool|null $urlCloak, bool $defaultCloaked = true): bool
    {
        if ($urlCloak === null || $urlCloak === '') {
            return $defaultCloaked;
        }

        if (is_bool($urlCloak)) {
            return $urlCloak;
        }

        return (int) $urlCloak === 1;
    }

    public function urlCloakValue(): int
    {
        return $this->isCloaked() ? 1 : 0;
    }

    public function displayTitle(?string $destinationHost = null): string
    {
        if ($this->page_title) {
            return $this->page_title;
        }

        return $destinationHost ?? parse_url($this->original_url, PHP_URL_HOST) ?? $this->original_url;
    }

    public function ensureLinkPreview(LinkPreviewService $previewService): self
    {
        if ($this->page_title && $this->thumbnail_url) {
            return $this;
        }

        $preview = $previewService->fetch($this->original_url);
        $updates = [];

        if (! $this->page_title && ! empty($preview['page_title'])) {
            $updates['page_title'] = $preview['page_title'];
        }

        if (! $this->thumbnail_url && ! empty($preview['thumbnail_url'])) {
            $updates['thumbnail_url'] = $preview['thumbnail_url'];
        }

        if ($updates !== []) {
            $this->update($updates);
            $this->refresh();
        }

        return $this;
    }

    public static function normalizeRedirectMode(?string $mode): string
    {
        $mode = strtolower(trim((string) $mode));

        return in_array($mode, self::REDIRECT_MODES, true) ? $mode : self::REDIRECT_BRIDGE;
    }

    public static function normalizeSource(?string $source, string $default = self::SOURCE_WEB): string
    {
        $source = strtolower(trim((string) $source));

        if ($source === '') {
            return $default;
        }

        $source = preg_replace('/[^a-z0-9_-]/', '', $source) ?? '';

        return $source !== '' ? substr($source, 0, 64) : $default;
    }

    public function getConnectionName(): ?string
    {
        if (config('engagyo.use_shared_database')) {
            return config('engagyo.database_connection');
        }

        return parent::getConnectionName();
    }

    public static function generateUniqueCode(int $length = 6): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('short_code', $code)->exists());

        return $code;
    }

    public static function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }
        $parsed = parse_url($url);
        if (! $parsed || empty($parsed['host'])) {
            return $url;
        }
        $scheme = strtolower($parsed['scheme'] ?? 'https');
        $host = strtolower($parsed['host']);
        $path = $parsed['path'] ?? '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?'.$parsed['query'] : '';
        $fragment = isset($parsed['fragment']) && $parsed['fragment'] !== '' ? '#'.$parsed['fragment'] : '';

        return $scheme.'://'.$host.$path.$query.$fragment;
    }
}
