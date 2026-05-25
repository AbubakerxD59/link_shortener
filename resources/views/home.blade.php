@extends('layouts.app')

@section('title', 'Free URL Shortener — ' . config('app.name'))

@push('styles')
<style>
    .hero {
        position: relative;
        padding: 2rem 0 5rem;
        text-align: center;
        overflow: hidden;
    }

    .hero::before,
    .hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
        z-index: 0;
    }

    .hero::before {
        width:  min(520px, 90vw);
        height: min(520px, 90vw);
        top: -120px;
        left: 50%;
        transform: translateX(-55%);
        background: radial-gradient(circle, rgba(167, 139, 250, 0.45) 0%, transparent 70%);
    }

    .hero::after {
        width: 320px;
        height: 320px;
        bottom: 0;
        right: -80px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
    }

    .hero .container {
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.875rem 0.375rem 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--ink-soft);
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-pill);
        box-shadow: var(--shadow-sm);
        animation: fadeUp 0.6s var(--ease-out) both;
    }

    .hero-badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 3px var(--success-soft);
    }

    .hero h1 {
        font-size: clamp(2.25rem, 6vw, 3.5rem);
        font-weight: 600;
        margin: 0 0 1.25rem;
        max-width: 720px;
        margin-left: auto;
        margin-right: auto;
        animation: fadeUp 0.6s var(--ease-out) 0.08s both;
    }

    .hero h1 .gradient {
        display: block;
        background: linear-gradient(120deg, var(--accent) 0%, var(--accent-light) 50%, #6366f1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-lead {
        color: var(--muted);
        max-width: 520px;
        margin: 0 auto 2.75rem;
        font-size: 1.125rem;
        line-height: 1.7;
        animation: fadeUp 0.6s var(--ease-out) 0.16s both;
    }

    .shortener-card {
        max-width: 680px;
        margin: 0 auto;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 0.5rem;
        box-shadow: var(--shadow-md);
        animation: fadeUp 0.7s var(--ease-out) 0.24s both;
    }

    .shortener-inner {
        padding: 1.25rem;
    }

    .shortener-row {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        background: var(--surface);
        border-radius: calc(var(--radius-lg) - 4px);
        padding: 0.5rem;
    }

    @media (min-width: 640px) {
        .shortener-row {
            flex-direction: row;
            align-items: stretch;
        }
    }

    .input-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0 0.875rem;
        background: var(--card);
        border: 1px solid transparent;
        border-radius: calc(var(--radius) - 2px);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-wrap:focus-within {
        border-color: var(--accent-light);
        box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1);
    }

    .input-icon {
        flex-shrink: 0;
        color: var(--muted);
        opacity: 0.7;
    }

    .url-input {
        flex: 1;
        min-width: 0;
        padding: 0.9375rem 0;
        font-family: var(--font-body);
        font-size: 1rem;
        border: none;
        outline: none;
        background: transparent;
        color: var(--ink);
    }

    .url-input::placeholder {
        color: #a1a1aa;
    }

    #btn-shorten {
        min-width: 148px;
        border-radius: calc(var(--radius) - 2px);
    }

    .result-box {
        margin-top: 0.5rem;
        padding: 1.25rem 1.25rem 1rem;
        border-radius: calc(var(--radius-lg) - 6px);
        background: linear-gradient(180deg, var(--success-soft) 0%, var(--card) 100%);
        border: 1px solid rgba(5, 150, 105, 0.15);
        display: none;
        animation: fadeUp 0.35s var(--ease-out) both;
    }

    .result-box.visible { display: block; }

    .result-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--success);
        margin: 0 0 0.875rem;
    }

    .result-label svg {
        flex-shrink: 0;
    }

    .result-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: stretch;
    }

    .result-output {
        flex: 1;
        min-width: 180px;
        padding: 0.75rem 1rem;
        font-family: ui-monospace, 'Cascadia Code', monospace;
        font-size: 0.875rem;
        border: 1px solid var(--line);
        border-radius: var(--radius);
        background: var(--card);
        color: var(--ink);
    }

    .error-msg {
        color: var(--danger);
        font-size: 0.875rem;
        margin: 0.75rem 1.25rem 0.5rem;
        padding: 0.625rem 0.875rem;
        background: #fef2f2;
        border-radius: var(--radius);
        display: none;
    }

    .error-msg.visible { display: block; }

    .hint {
        font-size: 0.8125rem;
        color: var(--muted);
        margin: 0.875rem 0 0;
        text-align: center;
    }

    .features {
        padding: 5rem 0;
        background: var(--ink);
        color: #fff;
        position: relative;
    }

    .features .section-label {
        color: var(--accent-light);
    }

    .features .section-label::before {
        background: var(--accent-light);
    }

    .features .section-title {
        color: #fff;
    }

    .features .section-subtitle {
        color: rgba(255, 255, 255, 0.55);
    }

    .feature-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .feature-grid { grid-template-columns: repeat(3, 1fr); }
    }

    .feature-card {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg);
        padding: 1.75rem 1.5rem;
        transition: transform 0.25s var(--ease-out), background 0.25s, border-color 0.25s;
    }

    .feature-card:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.07);
        border-color: rgba(167, 139, 250, 0.25);
    }

    .feature-card h3 {
        margin: 0 0 0.5rem;
        font-size: 1.0625rem;
        color: #fff;
    }

    .feature-card p {
        margin: 0;
        color: rgba(255, 255, 255, 0.55);
        font-size: 0.9375rem;
        line-height: 1.65;
    }

    .feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(145deg, rgba(124, 58, 237, 0.4), rgba(99, 102, 241, 0.2));
        border: 1px solid rgba(167, 139, 250, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        margin-bottom: 1.125rem;
    }

    .steps {
        padding: 5rem 0 4rem;
    }

    .steps-grid {
        display: grid;
        gap: 0;
        position: relative;
    }

    @media (min-width: 768px) {
        .steps-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .steps-grid::before {
            content: '';
            position: absolute;
            top: 28px;
            left: 16.66%;
            right: 16.66%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--line-strong) 15%, var(--line-strong) 85%, transparent);
        }
    }

    .step {
        text-align: center;
        padding: 1.5rem 1rem;
        position: relative;
    }

    .step-num {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--card);
        border: 1px solid var(--line);
        color: var(--accent);
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 1.125rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: var(--shadow-sm);
        position: relative;
        z-index: 1;
    }

    .step h3 {
        margin: 0 0 0.5rem;
        font-size: 1.0625rem;
    }

    .step p {
        margin: 0;
        color: var(--muted);
        font-size: 0.9375rem;
        max-width: 240px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <span class="hero-badge-dot" aria-hidden="true"></span>
                Free · No sign-up · Instant redirects
            </div>

            <h1>
                Shorten links
                <span class="gradient">in one click.</span>
            </h1>

            <p class="hero-lead">
                Turn long URLs into clean, shareable short links. Track clicks and redirect visitors instantly — powered by {{ config('app.name', 'ShrtLnk') }}.
            </p>

            <div class="shortener-card">
                <div class="shortener-inner">
                    <div class="shortener-row">
                        <div class="input-wrap">
                            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                            <input
                                type="url"
                                id="shorten-url-input"
                                class="url-input"
                                placeholder="Paste your long URL here…"
                                autocomplete="url"
                            >
                        </div>
                        <button type="button" class="btn btn-primary" id="btn-shorten">
                            Shorten link
                        </button>
                    </div>

                    <div id="shorten-result" class="result-box">
                        <p class="result-label" id="result-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            <span id="result-label-text">Your short link</span>
                        </p>
                        <div class="result-row">
                            <input type="text" id="short-url-output" class="result-output" readonly>
                            <button type="button" class="btn btn-outline" id="copy-short-url">Copy link</button>
                        </div>
                        <p class="hint">Opens your original URL with a fast redirect.</p>
                    </div>

                    <p id="shorten-error" class="error-msg" role="alert"></p>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <p class="section-label" style="display:flex;justify-content:center;">Features</p>
            <h2 class="section-title">Everything you need</h2>
            <p class="section-subtitle">Short links, click tracking, and instant redirects — built for speed and simplicity.</p>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔗</div>
                    <h3>Short, clean links</h3>
                    <p>Compact URLs you can drop anywhere — e.g. {{ parse_url(config('app.url'), PHP_URL_HOST) }}/s/abc123</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Instant redirect</h3>
                    <p>Visitors reach your destination immediately with a lightweight 302 redirect.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Click tracking</h3>
                    <p>Every visit increments your click count so you can see how links perform.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="steps">
        <div class="container">
            <p class="section-label" style="display:flex;justify-content:center;">How it works</p>
            <h2 class="section-title">Three simple steps</h2>
            <p class="section-subtitle">From long URL to shareable link in seconds.</p>
            <div class="steps-grid">
                <div class="step">
                    <span class="step-num">1</span>
                    <h3>Paste your URL</h3>
                    <p>Drop any http or https link into the field above.</p>
                </div>
                <div class="step">
                    <span class="step-num">2</span>
                    <h3>Get your short link</h3>
                    <p>We generate a unique code and show your shareable URL.</p>
                </div>
                <div class="step">
                    <span class="step-num">3</span>
                    <h3>Share anywhere</h3>
                    <p>Copy and use it in posts, emails, bios, or messages.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function() {
    var shortenUrl = @json(route('shorten'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var btn = document.getElementById('btn-shorten');
    var input = document.getElementById('shorten-url-input');
    var resultBox = document.getElementById('shorten-result');
    var resultLabelText = document.getElementById('result-label-text');
    var output = document.getElementById('short-url-output');
    var errorEl = document.getElementById('shorten-error');
    var copyBtn = document.getElementById('copy-short-url');

    function isValidUrl(s) {
        try {
            var u = new URL(s);
            return u.protocol === 'http:' || u.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }

    function showError(msg) {
        errorEl.textContent = msg || 'Something went wrong. Please try again.';
        errorEl.classList.add('visible');
        resultBox.classList.remove('visible');
    }

    function showResult(shortUrl, isExisting) {
        output.value = shortUrl;
        resultLabelText.textContent = isExisting
            ? 'You already shortened this URL'
            : 'Your short link is ready';
        errorEl.classList.remove('visible');
        resultBox.classList.remove('visible');
        void resultBox.offsetWidth;
        resultBox.classList.add('visible');
    }

    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function(resolve, reject) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                resolve();
            } catch (e) {
                reject(e);
            }
            document.body.removeChild(ta);
        });
    }

    btn.addEventListener('click', function() {
        var url = (input.value || '').trim();
        if (!url) {
            showError('Please enter a URL to shorten.');
            return;
        }
        if (!isValidUrl(url)) {
            showError('Please enter a valid URL (e.g. https://example.com).');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Shortening…';
        errorEl.classList.remove('visible');

        fetch(shortenUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                original_url: url,
                user_agent: navigator.userAgent || ''
            })
        })
        .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
        .then(function(r) {
            if (r.data.success && r.data.short_url) {
                showResult(r.data.short_url, r.data.existing === true);
                showToast(r.data.existing ? 'Existing short link returned.' : 'Link shortened!', 'success');
            } else {
                showError(r.data.message || 'Could not shorten link.');
            }
        })
        .catch(function() {
            showError('Network error. Please try again.');
        })
        .finally(function() {
            btn.disabled = false;
            btn.textContent = 'Shorten link';
        });
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') btn.click();
    });

    copyBtn.addEventListener('click', function() {
        if (!output.value) return;
        copyText(output.value)
            .then(function() { showToast('Copied to clipboard!', 'success'); })
            .catch(function() { showToast('Could not copy. Select and copy manually.', 'error'); });
    });
})();
</script>
@endpush
