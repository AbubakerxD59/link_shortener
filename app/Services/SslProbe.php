<?php

namespace App\Services;

class SslProbe
{
    public function domainHasWorkingHttps(string $domain, int $timeoutSeconds = 8): bool
    {
        $domain = strtolower(trim($domain));

        if ($domain === '') {
            return false;
        }

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $domain,
                'SNI_enabled' => true,
            ],
        ]);

        $client = @stream_socket_client(
            'ssl://'.$domain.':443',
            $errno,
            $errstr,
            $timeoutSeconds,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($client === false) {
            return false;
        }

        fclose($client);

        return true;
    }
}
