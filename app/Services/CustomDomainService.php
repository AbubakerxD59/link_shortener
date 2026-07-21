<?php

namespace App\Services;

use App\Models\CustomDomain;
use App\Models\ShortLink;
use App\Models\User;

class CustomDomainService
{
    public function __construct(
        protected DnsLookup $dnsLookup,
        protected SslProbe $sslProbe,
    ) {}

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

    public function dnsRecordName(CustomDomain $customDomain): string
    {
        if ($customDomain->isApex()) {
            return '@';
        }

        return $customDomain->subdomain_prefix ?? $this->subdomainPrefixFromDomain($customDomain->domain);
    }

    /**
     * @return list<array{type: string, name: string, value: string}>
     */
    public function dnsRecords(CustomDomain $customDomain): array
    {
        return [[
            'type' => 'CNAME',
            'name' => $this->dnsRecordName($customDomain),
            'value' => $this->cnameTarget(),
        ]];
    }

    /**
     * @return array{
     *     verified: bool,
     *     dns_ok: bool,
     *     ssl_ok: bool,
     *     dns_records: list<array{type: string, name: string, value: string}>,
     *     message: string
     * }
     */
    public function verify(CustomDomain $customDomain): array
    {
        $dnsOk = $this->subdomainRoutesToApp($customDomain->domain);
        $sslOk = $dnsOk && $this->domainHasWorkingHttps($customDomain->domain);

        if ($dnsOk) {
            $customDomain->update(['verified_at' => now()]);
        }

        return [
            'verified' => $dnsOk,
            'dns_ok' => $dnsOk,
            'ssl_ok' => $sslOk,
            'dns_records' => $this->dnsRecords($customDomain),
            'message' => $this->verificationMessage($dnsOk, $sslOk),
        ];
    }

    /**
     * Mark a branded domain active after ops has parked it on the host.
     */
    public function activate(CustomDomain $customDomain): CustomDomain
    {
        $customDomain->update(['verified_at' => now()]);

        return $customDomain->refresh();
    }

    public function domainHasWorkingHttps(string $domain): bool
    {
        if (config('custom_domains.scheme', 'https') !== 'https') {
            return true;
        }

        return $this->sslProbe->domainHasWorkingHttps(CustomDomain::normalizeHost($domain));
    }

    public function subdomainRoutesToApp(string $domain, ?string $cnameTarget = null): bool
    {
        $cnameTarget = $cnameTarget ?? $this->cnameTarget();
        $domain = CustomDomain::normalizeHost($domain);

        foreach ($this->dnsLookup->cnameTargets($domain) as $target) {
            if ($this->hostnamesMatch($target, $cnameTarget)) {
                return true;
            }
        }

        return false;
    }

    public function buildShortUrl(string $shortCode, ?CustomDomain $customDomain = null): string
    {
        if ($customDomain?->isVerified()) {
            $scheme = config('custom_domains.scheme', 'https');

            return rtrim($scheme.'://'.$customDomain->domain, '/').'/'.$shortCode;
        }

        return rtrim(config('app.url'), '/').'/'.$shortCode;
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
        $dnsRecords = $this->dnsRecords($customDomain);
        $exampleShortUrl = rtrim(config('custom_domains.scheme', 'https').'://'.$customDomain->domain, '/').'/abc123';

        $verified = $customDomain->isVerified();
        $sslOk = $verified ? $this->domainHasWorkingHttps($customDomain->domain) : false;

        return [
            'domain' => $customDomain->domain,
            'domain_type' => $customDomain->domain_type,
            'base_domain' => $customDomain->base_domain,
            'verified' => $verified,
            'ssl_ok' => $sslOk,
            'dns_records' => $dnsRecords,
            'example_short_url' => $exampleShortUrl,
            'cname_target' => $this->cnameTarget(),
        ];
    }

    public function assignDomain(
        User $user,
        string $baseDomain,
        string $domainType,
        ?string $subdomainPrefix = null
    ): CustomDomain {
        $baseDomain = CustomDomain::normalizeHost($baseDomain);
        $domainType = $domainType === CustomDomain::TYPE_APEX
            ? CustomDomain::TYPE_APEX
            : CustomDomain::TYPE_SUBDOMAIN;

        if (! $this->isValidBaseDomain($baseDomain)) {
            throw new \InvalidArgumentException('Enter a valid domain name (e.g. yourbrand.com).');
        }

        if ($domainType === CustomDomain::TYPE_SUBDOMAIN) {
            $subdomainPrefix = strtolower(trim((string) $subdomainPrefix));

            if (! $this->isValidSubdomainPrefix($subdomainPrefix)) {
                throw new \InvalidArgumentException('Enter a valid subdomain label (e.g. go, links, or shrtlnk).');
            }

            $domain = $subdomainPrefix.'.'.$baseDomain;
        } else {
            $domain = $baseDomain;
            $subdomainPrefix = null;
        }

        if ($this->isReservedHost($domain)) {
            throw new \InvalidArgumentException('Enter a custom hostname, not this app\'s default domain or an IP address.');
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
            'domain_type' => $domainType,
            'base_domain' => $baseDomain,
            'subdomain_prefix' => $subdomainPrefix,
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

    protected function subdomainPrefixFromDomain(string $domain): string
    {
        $domain = CustomDomain::normalizeHost($domain);
        $dot = strpos($domain, '.');

        return $dot === false ? $domain : substr($domain, 0, $dot);
    }

    protected function hostnamesMatch(string $left, string $right): bool
    {
        $left = CustomDomain::normalizeHost($left);
        $right = CustomDomain::normalizeHost($right);

        return $left === $right;
    }

    protected function isValidBaseDomain(string $host): bool
    {
        return (bool) preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
            $host
        );
    }

    protected function isValidSubdomainPrefix(string $prefix): bool
    {
        return (bool) preg_match('/^(?=.{1,63}$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i', $prefix);
    }

    protected function verificationMessage(bool $dnsOk, bool $sslOk): string
    {
        if ($dnsOk && $sslOk) {
            return 'Domain verified. Your short links are ready to use.';
        }

        if ($dnsOk) {
            return 'DNS verified. HTTPS is not working yet — configure SSL for this domain before sharing links.';
        }

        return 'DNS record not detected yet. Add the CNAME record below, wait for propagation, then refresh.';
    }
}
