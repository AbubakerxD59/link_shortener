@php
    $primaryRecord = $domainSetup['dns_records'][0] ?? null;
    $sslOk = $domainSetup['ssl_ok'] ?? false;
    $verified = $domainSetup['verified'] ?? false;
    $fullyReady = $verified && $sslOk;
@endphp

<div class="domain-setup">
    @if ($fullyReady)
        <div class="setup-intro">
            <h2>{{ $domainSetup['domain'] }} is active</h2>
            <p class="domain-setup-lead">
                Your branded domain is ready. New short links can use this domain.
            </p>
        </div>

        <div class="domain-note domain-note-success">
            <strong>Your links will look like this:</strong>
            <code>{{ $domainSetup['example_short_url'] }}</code>
        </div>
    @else
        <div class="setup-intro">
            <h2>DNS setup required for {{ $domainSetup['domain'] }}</h2>
            <p class="domain-setup-lead">
                Complete steps 1 and 2 below at your domain registrar. Our team handles the rest.
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
                                <strong>'{{ $primaryRecord['value'] }}</strong> (no quotes). Save changes.
                            </p>
                        @endif
                    </div>
                </div>
            </article>

            <article class="plain-step">
                <div class="plain-step-head">
                    <span class="setup-step-num">3</span>
                    <div>
                        <p>
                            <strong>Our team will review your domain and activate it within 24 hours.</strong>
                            You only need to complete steps 1 and 2 on your side — no further action is required from you.
                        </p>
                        <p class="record-tip" style="margin-top: 0.75rem;">
                            Once activated, short links on <code>{{ $domainSetup['domain'] }}</code> will work automatically.
                        </p>
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
            DNS changes can take up to a few hours to propagate. After you finish steps 1 and 2, our team will review and activate your domain within 24 hours.
        </p>

        @if ($verified)
            <div class="domain-note domain-note-success">
                <strong>Domain activated</strong>
                <p>Short links will use <code>{{ $domainSetup['example_short_url'] }}</code>.</p>
            </div>
        @endif
    @endif
</div>
