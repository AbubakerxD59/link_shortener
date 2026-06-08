<?php

namespace App\Services;

class SslProbe
{
    /** @var list<int> Cloudflare origin SSL errors — TLS to edge works but origin handshake failed */
    private const CLOUDFLARE_ORIGIN_SSL_ERRORS = [525, 526, 522];

    public function domainHasWorkingHttps(string $domain, int $timeoutSeconds = 8): bool
    {
        $domain = strtolower(trim($domain));

        if ($domain === '') {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'header' => "Host: {$domain}\r\nUser-Agent: LinkShortenerSslProbe/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $domain,
                'SNI_enabled' => true,
            ],
        ]);

        @file_get_contents('https://'.$domain.'/', false, $context);

        $statusCode = $this->responseStatusCode($http_response_header ?? null);

        if ($statusCode === null) {
            return false;
        }

        if (in_array($statusCode, self::CLOUDFLARE_ORIGIN_SSL_ERRORS, true)) {
            return false;
        }

        // 2xx/3xx or 404 (app reachable). 403 often means the host is not parked on shared hosting.
        return ($statusCode >= 200 && $statusCode < 400) || $statusCode === 404;
    }

    /**
     * @param  list<string>|null  $headers
     */
    protected function responseStatusCode(?array $headers): ?int
    {
        if ($headers === null || $headers === []) {
            return null;
        }

        if (preg_match('/^HTTP\/\d(?:\.\d)?\s+(\d{3})/', $headers[0], $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
