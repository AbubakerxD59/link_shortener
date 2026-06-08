@php
    $primaryRecord = $domainSetup['dns_records'][0] ?? null;
    $sslOk = $domainSetup['ssl_ok'] ?? false;
    $verified = $domainSetup['verified'] ?? false;
@endphp

<div class="domain-setup">
    <div class="setup-intro">
        <h2>DNS setup required for {{ $domainSetup['domain'] }}</h2>
        <p class="domain-setup-lead">
            Add the record below at your domain registrar to activate your short links.
        </p>
    </div>

    <div class="domain-note domain-note-info">
        You can forward these instructions to a tech person in your company.
    </div>

    <div class="plain-steps">
        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">1</span>
                <div>
                    <p>Go to your domain registrar, sign in, and locate the DNS Manage section for <strong>{{ $domainSetup['base_domain'] }}</strong>.</p>
                </div>
            </div>
        </article>

        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">2</span>
                <div>
                    @if ($primaryRecord)
                        <p>
                            Create a new type <strong>{{ $primaryRecord['type'] }}</strong> record with a name
                            <strong>'{{ $primaryRecord['name'] }}'</strong> (no quotes) and value
                            <strong>'{{ $primaryRecord['value'] }}'</strong> (no quotes). Save changes.
                        </p>
                    @endif
                </div>
            </div>
        </article>

        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">3</span>
                <div>
                    <p><strong>Enable HTTPS</strong> so <code>https://{{ $domainSetup['domain'] }}</code> opens without a security error.</p>
                    <p class="record-tip" style="margin-top: 0.75rem;">
                        Short.io issues SSL certificates automatically. For this app, choose one option:
                    </p>
                    <ul class="ssl-options-list">
                        <li><strong>Cloudflare (recommended):</strong> If {{ $domainSetup['base_domain'] }} uses Cloudflare DNS, turn on the <strong>orange cloud proxy</strong> for the CNAME record above. Cloudflare provides free SSL for <code>{{ $domainSetup['domain'] }}</code>.</li>
                        <li><strong>Manual certificate:</strong> Issue a TLS certificate for <code>{{ $domainSetup['domain'] }}</code> on the web server that receives traffic for <code>{{ $domainSetup['cname_target'] }}</code>.</li>
                    </ul>
                </div>
            </div>
        </article>
    </div>

    <p class="dns-table-label">So you will see a new record:</p>

    <div class="dns-record-table-wrap">
        <table class="dns-record-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($domainSetup['dns_records'] as $record)
                    <tr>
                        <td>{{ $record['type'] }}</td>
                        <td><code>{{ $record['name'] }}</code></td>
                        <td><code>{{ $record['value'] }}</code></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="setup-time-note">
        DNS changes can take up to a few hours to propagate.
        @if (! $verified || ! $sslOk)
            Click <strong>Refresh</strong> below after DNS and HTTPS are configured.
        @endif
    </p>

    @if ($verified && $sslOk)
        <div class="domain-note domain-note-success">
            <strong>Your links will look like this:</strong>
            <code>{{ $domainSetup['example_short_url'] }}</code>
        </div>
    @elseif ($verified)
        <div class="domain-note domain-note-warning">
            <strong>DNS verified — waiting on HTTPS</strong>
            <p>Short links will use <code>{{ $domainSetup['example_short_url'] }}</code>, but visitors will see a browser error until step 3 is complete.</p>
        </div>
    @endif
</div>
