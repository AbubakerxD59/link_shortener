<?php

namespace App\Services;

class DnsLookup
{
    /**
     * @return list<string>
     */
    public function txtRecords(string $host): array
    {
        $records = @dns_get_record($host, DNS_TXT);

        if (! is_array($records)) {
            return [];
        }

        $values = [];

        foreach ($records as $record) {
            foreach ($record['entries'] ?? [$record['txt'] ?? ''] as $entry) {
                $entry = trim((string) $entry);
                if ($entry !== '') {
                    $values[] = $entry;
                }
            }
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    public function cnameTargets(string $host): array
    {
        $records = @dns_get_record($host, DNS_CNAME);

        if (! is_array($records)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (array $record) => rtrim(strtolower((string) ($record['target'] ?? '')), '.'),
            $records
        )));
    }

    /**
     * @return list<string>
     */
    public function aRecords(string $host): array
    {
        $records = @dns_get_record($host, DNS_A);

        if (! is_array($records)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (array $record) => (string) ($record['ip'] ?? ''),
            $records
        )));
    }
}
