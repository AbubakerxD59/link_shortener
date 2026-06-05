@php $subdomain = \Illuminate\Support\Str::before($domainSetup['domain'], '.'); @endphp

<div class="domain-setup">
    <div class="setup-intro">
        <h2>How to connect {{ $domainSetup['domain'] }}</h2>
        <p class="domain-setup-lead">
            You only need to add <strong>two small settings</strong> where you manage your domain
            (for example GoDaddy, Namecheap, Cloudflare, or Google Domains).
            This tells the internet that <strong>{{ $domainSetup['domain'] }}</strong> belongs to you
            and should point to our link shortener.
        </p>
        <p class="setup-time-note">After saving, wait 5–30 minutes (sometimes up to 24 hours), then click <strong>Check my setup</strong> below.</p>
    </div>

    <div class="plain-steps">
        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">1</span>
                <div>
                    <h3>Open your domain settings</h3>
                    <p>Log in to the website where you bought or manage your domain name. Look for a section called <strong>DNS</strong>, <strong>DNS Records</strong>, or <strong>Manage DNS</strong>.</p>
                </div>
            </div>
        </article>

        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">2</span>
                <div>
                    <h3>Add the first record (proves you own the domain)</h3>
                    <p>Click <strong>Add record</strong> or <strong>Add new record</strong>, then copy the values below into the matching boxes.</p>
                </div>
            </div>

            <div class="record-card">
                <div class="record-card-title">
                    <span class="dns-type">TXT</span>
                    <span>Ownership check</span>
                </div>
                <dl class="record-fields">
                    <div class="record-field">
                        <dt>Type</dt>
                        <dd>TXT</dd>
                    </div>
                    <div class="record-field">
                        <dt>Host / Name / Subdomain</dt>
                        <dd class="record-value-row">
                            <code class="dns-value">{{ $domainSetup['txt_host'] }}</code>
                            <button type="button" class="copy-btn" data-copy-text="{{ $domainSetup['txt_host'] }}" title="Copy host" aria-label="Copy host">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </dd>
                    </div>
                    <div class="record-field">
                        <dt>Value / Content / Points to</dt>
                        <dd class="record-value-row">
                            <code class="dns-value">{{ $domainSetup['txt_value'] }}</code>
                            <button type="button" class="copy-btn" data-copy-text="{{ $domainSetup['txt_value'] }}" title="Copy TXT value" aria-label="Copy TXT value">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </dd>
                    </div>
                </dl>
                <p class="record-tip">Some providers only want the short version in the Host field. If the full name does not work, try <code>_shrtlnk-verify</code> or <code>_shrtlnk-verify.{{ $subdomain }}</code> — check your provider’s help page for the exact format.</p>
            </div>
        </article>

        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">3</span>
                <div>
                    <h3>Add the second record (sends visitors to us)</h3>
                    <p>This connects <strong>{{ $domainSetup['domain'] }}</strong> to our service so your short links work on your brand.</p>
                </div>
            </div>

            <div class="record-card">
                <div class="record-card-title">
                    <span class="dns-type">CNAME</span>
                    <span>Traffic routing</span>
                </div>
                <dl class="record-fields">
                    <div class="record-field">
                        <dt>Type</dt>
                        <dd>CNAME</dd>
                    </div>
                    <div class="record-field">
                        <dt>Host / Name / Subdomain</dt>
                        <dd class="record-value-row">
                            <code class="dns-value">{{ $domainSetup['domain'] }}</code>
                            <button type="button" class="copy-btn" data-copy-text="{{ $domainSetup['domain'] }}" title="Copy domain" aria-label="Copy domain">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </dd>
                    </div>
                    <div class="record-field">
                        <dt>Value / Target / Points to</dt>
                        <dd class="record-value-row">
                            <code class="dns-value">{{ $domainSetup['cname_target'] }}</code>
                            <button type="button" class="copy-btn" data-copy-text="{{ $domainSetup['cname_target'] }}" title="Copy CNAME target" aria-label="Copy CNAME target">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </dd>
                    </div>
                </dl>
                <p class="record-tip">If your provider asks for only the subdomain part, enter <code>{{ $subdomain }}</code> instead of the full address.</p>
            </div>
        </article>

        <article class="plain-step">
            <div class="plain-step-head">
                <span class="setup-step-num">4</span>
                <div>
                    <h3>Save both records and wait a little</h3>
                    <p>Click <strong>Save</strong> in your domain provider. Changes are not instant — give it a few minutes, grab a coffee, then come back and press <strong>Check my setup</strong> at the bottom of this page.</p>
                </div>
            </div>
        </article>
    </div>

    <div class="domain-note domain-note-success">
        <strong>When everything works, your links will look like this:</strong>
        <code>{{ $domainSetup['example_short_url'] }}</code>
        <p>Instead of using our default website address, people will see <strong>{{ $domainSetup['domain'] }}</strong> in the link you share.</p>
    </div>

    <details class="help-details">
        <summary>Common questions &amp; tips</summary>
        <div class="help-details-body">
            <div class="faq-item">
                <strong>Which domain name should I use?</strong>
                <p>A subdomain works best — something like <code>go.yourbrand.com</code> or <code>links.yourbrand.com</code>. Avoid using just <code>yourbrand.com</code> unless your provider specifically supports it.</p>
            </div>
            <div class="faq-item">
                <strong>Verification failed?</strong>
                <p>Double-check you copied both records exactly, saved them, and waited long enough. Typos in the Value field are the most common issue.</p>
            </div>
            <div class="faq-item">
                <strong>Do I need the secure padlock (HTTPS)?</strong>
                <p>Yes, for a professional link. If you use Cloudflare, turn on the orange cloud proxy — it handles this for you. Otherwise, ask whoever hosts your website to enable SSL for <code>{{ $domainSetup['domain'] }}</code>.</p>
            </div>
            <div class="faq-item">
                <strong>Need help from someone technical?</strong>
                <p>Share this page with them. They should ensure <code>{{ $domainSetup['domain'] }}</code> is accepted by the web server and routes to <code>{{ $domainSetup['app_host'] }}</code>.</p>
            </div>
        </div>
    </details>
</div>
