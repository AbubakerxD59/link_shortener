<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomDomain extends Model
{
    public const TYPE_APEX = 'apex';

    public const TYPE_SUBDOMAIN = 'subdomain';

    protected $fillable = [
        'user_id',
        'domain',
        'domain_type',
        'base_domain',
        'subdomain_prefix',
        'verification_token',
        'verified_at',
        'is_default',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isApex(): bool
    {
        return $this->domain_type === self::TYPE_APEX;
    }

    public function isSubdomain(): bool
    {
        return $this->domain_type === self::TYPE_SUBDOMAIN;
    }

    public static function defaultForUser(int $userId): ?self
    {
        return self::query()
            ->where('user_id', $userId)
            ->whereNotNull('verified_at')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    public function statusLabel(): string
    {
        return $this->isVerified() ? 'Active' : 'Pending review';
    }

    public static function verifiedForHost(string $host): ?self
    {
        return self::query()
            ->where('domain', self::normalizeHost($host))
            ->whereNotNull('verified_at')
            ->first();
    }

    public static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('#^https?://#', '', $host) ?? $host;
        $host = rtrim($host, '/.');
        $host = explode('/', $host)[0];
        $host = explode(':', $host)[0];

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    public static function generateVerificationToken(): string
    {
        return Str::random(32);
    }
}
