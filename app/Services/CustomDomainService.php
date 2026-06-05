<?php

namespace App\Services;

use App\Models\CustomDomain;
use App\Models\ShortLink;
use App\Models\User;

class CustomDomainService
{
    public function __construct(protected DnsLookup $dnsLookup) {}

    public function cnameTarget(): string
    {
        $target = config('custom_domains.cname_target');

        if (is_string($target) && $target !== '') {
            return CustomDomain::normalizeHost($target);
        }

        return CustomDomain::normalizeHost(
            (string) parse_url(config('app.url'), PHP_URL_HOST)
        );
    }

    public function appHost(): string
    {
        return $this->cnameTarget();
    }

    public function verificationTxtHost(string $domain): string
    {
        return config('custom_domains.verification_prefix').'.'.$domain;
    }

    public function verificationTxtValue(CustomDomain $customDomain): string
    {
        return 'shrtlnk-verify='.$customDomain->verification_token;
    }

    /**
     * @return array{
     *     verified: bool,
     *     txt_ok: bool,
     *     routing_ok: bool,
     *     txt_host: string,
     *     txt_value: string,
     *     cname_target: string,
     *     message: string
     * }
     */
    public function verify(CustomDomain $customDomain): array
    {
        $txtHost = $this->verificationTxtHost($customDomain->domain);
        $txtValue = $this->verificationTxtValue($customDomain);
        $cnameTarget = $this->cnameTarget();

        $txtOk = in_array($txtValue, $this->dnsLookup->txtRecords($txtHost), true);
        $routingOk = $this->domainRoutesToApp($customDomain->domain, $cnameTarget);

        $verified = $txtOk && $routingOk;

        if ($verified) {
            $customDomain->update(['verified_at' => now()]);
        }

        return [
            'verified' => $verified,
            'txt_ok' => $txtOk,
            'routing_ok' => $routingOk,
            'txt_host' => $txtHost,
            'txt_value' => $txtValue,
            'cname_target' => $cnameTarget,
            'message' => $this->verificationMessage($txtOk, $routingOk),
        ];
    }

    public function domainRoutesToApp(string $domain, ?string $cnameTarget = null): bool
    {
        $cnameTarget = $cnameTarget ?? $this->cnameTarget();
        $domain = CustomDomain::normalizeHost($domain);

        foreach ($this->dnsLookup->cnameTargets($domain) as $target) {
            if ($this->hostnamesMatch($target, $cnameTarget)) {
                return true;
            }
        }

        $appIps = $this->dnsLookup->aRecords($cnameTarget);
        $domainIps = $this->dnsLookup->aRecords($domain);

        if ($appIps !== [] && $domainIps !== [] && array_intersect($appIps, $domainIps) !== []) {
            return true;
        }

        return false;
    }

    public function buildShortUrl(string $shortCode, ?CustomDomain $customDomain = null): string
    {
        if ($customDomain?->isVerified()) {
            $scheme = config('custom_domains.scheme', 'https');

            return rtrim($scheme.'://'.$customDomain->domain, '/').'/s/'.$shortCode;
        }

        return rtrim(config('app.url'), '/').'/s/'.$shortCode;
    }

    public function buildShortUrlForLink(ShortLink $shortLink): string
    {
        $customDomain = $shortLink->relationLoaded('customDomain')
            ? $shortLink->customDomain
            : ($shortLink->custom_domain_id ? $shortLink->customDomain()->first() : null);

        return $this->buildShortUrl($shortLink->short_code, $customDomain);
    }

    public function linkDomainLabel(?CustomDomain $customDomain = null): string
    {
        if ($customDomain?->isVerified()) {
            return $customDomain->domain;
        }

        return $this->appHost();
    }

    /**
     * @return list<array{id: int|null, label: string, host: string, type: string, is_default?: bool}>
     */
    public function shortenDomainOptions(User $user): array
    {
        $options = [[
            'id' => null,
            'label' => $this->appHost(),
            'host' => $this->appHost(),
            'type' => 'default',
        ]];

        foreach (
            $user->customDomains()
                ->whereNotNull('verified_at')
                ->orderByDesc('is_default')
                ->orderBy('domain')
                ->get() as $domain
        ) {
            $options[] = [
                'id' => $domain->id,
                'label' => $domain->domain,
                'host' => $domain->domain,
                'type' => 'branded',
                'is_default' => $domain->is_default,
            ];
        }

        return $options;
    }

    public function resolveVerifiedDomainForUser(User $user, ?int $customDomainId): ?CustomDomain
    {
        if ($customDomainId === null) {
            return null;
        }

        return CustomDomain::query()
            ->where('id', $customDomainId)
            ->where('user_id', $user->id)
            ->whereNotNull('verified_at')
            ->first();
    }

    public function isReservedHost(string $host): bool
    {
        $host = CustomDomain::normalizeHost($host);

        return $host === '' || $host === $this->appHost() || filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @return array<string, mixed>
     */
    public function setupInstructions(CustomDomain $customDomain): array
    {
        $cnameTarget = $this->cnameTarget();
        $txtHost = $this->verificationTxtHost($customDomain->domain);
        $txtValue = $this->verificationTxtValue($customDomain);
        $appUrl = rtrim(config('app.url'), '/');
        $exampleShortUrl = rtrim(config('custom_domains.scheme', 'https').'://'.$customDomain->domain, '/').'/s/abc123';

        return [
            'domain' => $customDomain->domain,
            'verified' => $customDomain->isVerified(),
            'cname_target' => $cnameTarget,
            'txt_host' => $txtHost,
            'txt_value' => $txtValue,
            'example_short_url' => $exampleShortUrl,
            'app_url' => $appUrl,
            'app_host' => $this->appHost(),
        ];
    }

    public function assignDomain(User $user, string $domain): CustomDomain
    {
        $domain = CustomDomain::normalizeHost($domain);

        if ($this->isReservedHost($domain)) {
            throw new \InvalidArgumentException('Enter a custom hostname, not this app\'s default domain or an IP address.');
        }

        if (! $this->isValidHostname($domain)) {
            throw new \InvalidArgumentException('Enter a valid domain name (e.g. go.yourbrand.com).');
        }

        if (CustomDomain::query()->where('domain', $domain)->exists()) {
            $owner = CustomDomain::query()->where('domain', $domain)->value('user_id');
            $message = $owner === $user->id
                ? 'You have already added this domain.'
                : 'This domain is already registered by another account.';

            throw new \InvalidArgumentException($message);
        }

        $isFirst = ! CustomDomain::query()->where('user_id', $user->id)->exists();

        return CustomDomain::query()->create([
            'user_id' => $user->id,
            'domain' => $domain,
            'verification_token' => CustomDomain::generateVerificationToken(),
            'verified_at' => null,
            'is_default' => $isFirst,
        ]);
    }

    public function setAsDefault(User $user, CustomDomain $customDomain): void
    {
        if ($customDomain->user_id !== $user->id) {
            throw new \InvalidArgumentException('You do not own this domain.');
        }

        if (! $customDomain->isVerified()) {
            throw new \InvalidArgumentException('Only verified domains can be set as the default for new short links.');
        }

        CustomDomain::query()
            ->where('user_id', $user->id)
            ->update(['is_default' => false]);

        $customDomain->update(['is_default' => true]);
    }

    public function removeDomain(CustomDomain $customDomain): void
    {
        $userId = $customDomain->user_id;
        $wasDefault = $customDomain->is_default;

        $customDomain->delete();

        if ($wasDefault) {
            $nextDefault = CustomDomain::query()
                ->where('user_id', $userId)
                ->whereNotNull('verified_at')
                ->orderBy('id')
                ->first();

            $nextDefault?->update(['is_default' => true]);
        }
    }

    protected function hostnamesMatch(string $left, string $right): bool
    {
        $left = CustomDomain::normalizeHost($left);
        $right = CustomDomain::normalizeHost($right);

        return $left === $right;
    }

    protected function isValidHostname(string $host): bool
    {
        return (bool) preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
            $host
        );
    }

    protected function verificationMessage(bool $txtOk, bool $routingOk): string
    {
        if ($txtOk && $routingOk) {
            return 'Domain verified. Your short links will now use your branded domain.';
        }

        if (! $txtOk && ! $routingOk) {
            return 'DNS records not detected yet. Add the TXT and CNAME/A records below, wait for propagation, then verify again.';
        }

        if (! $txtOk) {
            return 'Ownership TXT record not found yet. Confirm the TXT host and value, then verify again.';
        }

        return 'Traffic routing record not found yet. Point your domain to '.$this->cnameTarget().' with CNAME or A record, then verify again.';
    }
}
