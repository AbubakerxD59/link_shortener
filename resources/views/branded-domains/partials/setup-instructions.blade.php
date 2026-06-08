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
                    <p>Go to your domain registrar, sign in, and locate the domain DNS Manage section.</p>
                </div>
            </div>
        </article>

        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">2</span>
                <div>
                    <p>
                        Create a new type <strong>CNAME</strong> record with a name
                        <strong>'{{ $domainSetup['cname_name'] }}'</strong> (no quotes) and value
                        <strong>'{{ $domainSetup['cname_target'] }}'</strong> (no quotes). Save changes.
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
                <tr>
                    <td>CNAME</td>
                    <td><code>{{ $domainSetup['cname_name'] }}</code></td>
                    <td><code>{{ $domainSetup['cname_target'] }}</code></td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="setup-time-note">
        DNS changes can take up to a few hours to propagate.
        @unless ($domainSetup['verified'])
            Click <strong>Refresh</strong> below once the record is live.
        @endunless
    </p>

    @if ($domainSetup['verified'])
        <div class="domain-note domain-note-success">
            <strong>Your links will look like this:</strong>
            <code>{{ $domainSetup['example_short_url'] }}</code>
        </div>
    @endif
</div>
