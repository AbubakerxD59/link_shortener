<?php

namespace App\Models;

use App\Services\LinkPreviewService;
use Illuminate\Database\Eloquent\Model;

class ShortLink extends Model
{
    public const REDIRECT_DIRECT = 'direct';

    public const REDIRECT_BRIDGE = 'bridge';

    public const REDIRECT_META = 'meta';

    /** @var list<string> */
    public const REDIRECT_MODES = [
        self::REDIRECT_DIRECT,
        self::REDIRECT_BRIDGE,
        self::REDIRECT_META,
    ];

    protected $fillable = [
        'user_id',
        'short_code',
        'original_url',
        'redirect_mode',
        'bridge_delay_seconds',
        'page_title',
        'thumbnail_url',
        'user_agent',
        'ip_address',
        'clicks',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'bridge_delay_seconds' => 'integer',
    ];

    public function isCloaked(): bool
    {
        return in_array($this->redirect_mode, [self::REDIRECT_BRIDGE, self::REDIRECT_META], true);
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
