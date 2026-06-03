@extends('layouts.app')

@section('title', 'API Documentation — ' . config('app.name'))

@push('styles')
<style>
    .docs-hero {
        position: relative;
        padding: 2rem 0 3rem;
        text-align: center;
        overflow: hidden;
    }

    .docs-hero::before {
        content: '';
        position: absolute;
        width: min(480px, 90vw);
        height: min(480px, 90vw);
        top: -100px;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 50%;
        filter: blur(80px);
        background: radial-gradient(circle, rgba(167, 139, 250, 0.4) 0%, transparent 70%);
        pointer-events: none;
    }

    .docs-hero .container { position: relative; z-index: 1; }

    .docs-hero h1 {
        font-size: clamp(2rem, 5vw, 2.75rem);
        margin: 0 0 1rem;
    }

    .docs-hero h1 .gradient {
        display: block;
        background: linear-gradient(120deg, var(--accent) 0%, var(--accent-light) 50%, #6366f1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .docs-hero-lead {
        color: var(--muted);
        max-width: 560px;
        margin: 0 auto 1.5rem;
        font-size: 1.0625rem;
        line-height: 1.7;
    }

    .docs-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.625rem;
        justify-content: center;
    }

    .docs-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        padding-bottom: 4rem;
    }

    @media (min-width: 900px) {
        .docs-layout {
            grid-template-columns: 220px minmax(0, 1fr);
            align-items: start;
        }
    }

    .docs-sidebar {
        position: sticky;
        top: 5.5rem;
    }

    .docs-nav-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 1rem;
        box-shadow: var(--shadow-sm);
    }

    .docs-nav-title {
        font-family: var(--font-display);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--muted);
        margin: 0 0 0.75rem;
        padding: 0 0.5rem;
    }

    .docs-nav a {
        display: block;
        padding: 0.5rem 0.625rem;
        font-size: 0.875rem;
        color: var(--ink-soft);
        text-decoration: none;
        border-radius: 10px;
        transition: background 0.15s, color 0.15s;
    }

    .docs-nav a:hover {
        background: var(--surface);
        color: var(--accent);
    }

    .docs-main { min-width: 0; }

    .doc-section {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--shadow-sm);
    }

    .doc-section h2 {
        font-size: 1.375rem;
        margin: 0 0 1rem;
        padding-top: 0.25rem;
        scroll-margin-top: 5.5rem;
    }

    .doc-section h3 {
        font-size: 1.0625rem;
        margin: 1.5rem 0 0.75rem;
    }

    .doc-section p,
    .doc-section li {
        color: var(--ink-soft);
        font-size: 0.9375rem;
    }

    .doc-section > p { margin: 0 0 1rem; }

    .doc-section ul { margin: 0 0 1rem; padding-left: 1.25rem; }

    .endpoint-card {
        border: 1px solid var(--line);
        border-radius: var(--radius);
        overflow: hidden;
        margin: 1rem 0;
    }

    .endpoint-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.125rem;
        background: var(--surface);
        border-bottom: 1px solid var(--line);
    }

    .method {
        font-family: ui-monospace, monospace;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        color: #fff;
    }

    .method-post { background: var(--accent); }
    .method-get { background: var(--success); }

    .endpoint-path {
        font-family: ui-monospace, 'Cascadia Code', monospace;
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--ink);
    }

    .endpoint-body { padding: 1.125rem; }

    .doc-table-wrap {
        overflow-x: auto;
        margin: 1rem 0;
        border: 1px solid var(--line);
        border-radius: var(--radius);
    }

    .doc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .doc-table th,
    .doc-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid var(--line);
    }

    .doc-table th {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted);
        background: var(--surface);
    }

    .doc-table tr:last-child td { border-bottom: none; }

    .doc-table code {
        font-family: ui-monospace, monospace;
        font-size: 0.8125rem;
        background: var(--surface);
        padding: 0.125rem 0.375rem;
        border-radius: 4px;
        color: var(--accent);
    }

    .code-block {
        position: relative;
        margin: 1rem 0;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--line);
    }

    .code-block-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.875rem;
        background: #18181b;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    .code-block-label {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #a1a1aa;
    }

    .code-copy {
        font-family: var(--font-body);
        font-size: 0.75rem;
        font-weight: 500;
        color: #e4e4e7;
        background: rgba(255,255,255,0.08);
        border: none;
        padding: 0.25rem 0.625rem;
        border-radius: 6px;
        cursor: pointer;
    }

    .code-copy:hover { background: rgba(255,255,255,0.14); }

    .code-block pre {
        margin: 0;
        padding: 1rem 1.125rem;
        overflow-x: auto;
        background: #0c0c10;
        color: #e4e4e7;
        font-family: ui-monospace, 'Cascadia Code', monospace;
        font-size: 0.8125rem;
        line-height: 1.6;
    }

    .status-pill {
        display: inline-block;
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.125rem 0.5rem;
        border-radius: 6px;
        margin-right: 0.5rem;
    }

    .status-201 { background: var(--success-soft); color: var(--success); }
    .status-200 { background: var(--accent-soft); color: var(--accent); }
    .status-422 { background: #fef2f2; color: var(--danger); }

    .callout {
        padding: 1rem 1.125rem;
        border-radius: var(--radius);
        background: var(--accent-soft);
        border: 1px solid rgba(109, 40, 217, 0.12);
        font-size: 0.875rem;
        color: var(--ink-soft);
        margin: 1rem 0;
    }

    .callout strong { color: var(--accent); }
</style>
@endpush

@section('content')
    <section class="docs-hero">
        <div class="container">
            <p class="section-label" style="display:flex;justify-content:center;">Developers</p>
            <h1>
                API
                <span class="gradient">Documentation</span>
            </h1>
            <p class="docs-hero-lead">
                Integrate {{ config('app.name') }} into your app. Create cloaked short links with bridge-page previews via JSON or form POST.
            </p>
            <div class="docs-hero-actions">
                <a href="{{ route('home') }}" class="btn btn-outline">Back to shortener</a>
                <a href="{{ route('docs.openapi') }}" class="btn btn-primary" download>OpenAPI spec</a>
            </div>
        </div>
    </section>

    <div class="container docs-layout">
        <aside class="docs-sidebar">
            <div class="docs-nav-card">
                <p class="docs-nav-title">On this page</p>
                <nav class="docs-nav" aria-label="Documentation sections">
                    <a href="#overview">Overview</a>
                    <a href="#authentication">Authentication</a>
                    <a href="#create-link-api">Create link (API)</a>
                    <a href="#get-link-api">Get link details (API)</a>
                    <a href="#create-link-web">Create link (web)</a>
                    <a href="#visit-link">Visit short link</a>
                    <a href="#responses">Responses</a>
                    <a href="#errors">Errors</a>
                    <a href="#notes">Behavior notes</a>
                </nav>
            </div>
        </aside>

        <div class="docs-main">
            <section class="doc-section" id="overview">
                <h2>Overview</h2>
                <p>Base URL: <code>{{ $appUrl }}</code></p>
                <p>JSON endpoints accept <code>Content-Type: application/json</code> or <code>application/x-www-form-urlencoded</code>.</p>

                <div class="doc-table-wrap">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Path</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="method method-post">POST</span></td>
                                <td><code>/api/links</code></td>
                                <td><strong>Recommended</strong> — store a short link (no CSRF)</td>
                            </tr>
                            <tr>
                                <td><span class="method method-get">GET</span></td>
                                <td><code>/api/links/{code}</code></td>
                                <td>Get link details and click count</td>
                            </tr>
                            <tr>
                                <td><span class="method method-post">POST</span></td>
                                <td><code>/shorten</code></td>
                                <td>Web UI shorten (CSRF required from browser)</td>
                            </tr>
                            <tr>
                                <td><span class="method method-get">GET</span></td>
                                <td><code>/s/{code}</code></td>
                                <td>Bridge cloaking page, then destination</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="callout">
                    <strong>Link cloaking:</strong> Set <code>cloak: true</code> (default) for a bridge page with preview; set <code>cloak: false</code> for an instant 302 redirect to the destination.
                </div>
            </section>

            <section class="doc-section" id="authentication">
                <h2>Authentication</h2>
                <p>No API keys or tokens are required by default. If you expose the API publicly, protect <code>/api/*</code> at the firewall or API gateway level.</p>
            </section>

            <section class="doc-section" id="create-link-api">
                <h2>Create short link (API)</h2>
                <p>Create a new short link or return an existing one when the same URL was already shortened (see deduplication).</p>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/api/links</span>
                    </div>
                    <div class="endpoint-body">
                        <h3>Body parameters</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>original_url</code></td>
                                        <td>string (URL)</td>
                                        <td>Yes</td>
                                        <td>Destination URL (max 2048 chars)</td>
                                    </tr>
                                    <tr>
                                        <td><code>user_id</code></td>
                                        <td>integer</td>
                                        <td>No</td>
                                        <td>Associate with user (min 1)</td>
                                    </tr>
                                    <tr>
                                        <td><code>user_agent</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Stored on record; defaults to request User-Agent</td>
                                    </tr>
                                    <tr>
                                        <td><code>ip_address</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Valid IPv4/IPv6; defaults to client IP</td>
                                    </tr>
                                    <tr>
                                        <td><code>page_title</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Override bridge preview title (max 500)</td>
                                    </tr>
                                    <tr>
                                        <td><code>thumbnail_url</code></td>
                                        <td>string (URL)</td>
                                        <td>No</td>
                                        <td>Override bridge preview image</td>
                                    </tr>
                                    <tr>
                                        <td><code>source</code></td>
                                        <td>string</td>
                                        <td>No</td>
                                        <td>Origin label (e.g. <code>api</code>, <code>engagyo</code>). Defaults to <code>api</code></td>
                                    </tr>
                                    <tr>
                                        <td><code>cloak</code></td>
                                        <td>boolean</td>
                                        <td>No</td>
                                        <td><code>true</code> = bridge page (default). <code>false</code> = direct 302 redirect</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Example request (JSON)</h3>
                        @include('docs.partials.code-block', [
                            'label' => 'cURL',
                            'code' => 'curl -X POST "' . $appUrl . '/api/links" \\
  -H "Accept: application/json" \\
  -H "Content-Type: application/json" \\
  -d \'{
    "original_url": "https://example.com/blog/post",
    "user_id": 42,
    "user_agent": "MyIntegration/1.0",
    "ip_address": "203.0.113.10"
  }\'',
                        ])

                        <h3>Success response</h3>
                        <p>
                            <span class="status-pill status-201">201</span> New link created
                            <span class="status-pill status-200">200</span> Existing link returned
                        </p>
                        @include('docs.partials.code-block', [
                            'label' => 'JSON',
                            'code' => '{
  "success": true,
  "id": 15,
  "short_url": "' . $appUrl . '/s/a1B2c3",
  "short_code": "a1B2c3",
  "original_url": "https://example.com/blog/post",
  "redirect_mode": "bridge",
  "page_title": "Example Blog Post",
  "thumbnail_url": "https://example.com/og-image.jpg",
  "source": "api",
  "clicks": 0,
  "existing": false,
  "created_at": "2026-05-26T14:30:00+00:00"
}',
                        ])
                    </div>
                </div>
            </section>

            <section class="doc-section" id="get-link-api">
                <h2>Get link details (API)</h2>
                <p>Fetch a short link by its code, including the current <code>clicks</code> count.</p>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/api/links/{code}</span>
                    </div>
                    <div class="endpoint-body">
                        <h3>Path parameters</h3>
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>code</code></td>
                                        <td>string</td>
                                        <td>Short link code (e.g. <code>abc123</code>)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3>Example</h3>
                        @include('docs.partials.code-block', [
                            'label' => 'cURL',
                            'code' => 'curl -X GET "' . $appUrl . '/api/links/abc123" \\
  -H "Accept: application/json"',
                        ])

                        <h3>Success response</h3>
                        <p><span class="status-pill status-200">200</span> Link found</p>
                        @include('docs.partials.code-block', [
                            'label' => 'JSON',
                            'code' => '{
  "success": true,
  "id": 15,
  "short_url": "' . $appUrl . '/s/abc123",
  "short_code": "abc123",
  "original_url": "https://example.com/blog/post",
  "redirect_mode": "bridge",
  "bridge_delay_seconds": 0,
  "page_title": "Example Blog Post",
  "thumbnail_url": "https://example.com/og-image.jpg",
  "source": "api",
  "clicks": 42,
  "user_id": null,
  "created_at": "2026-05-26T14:30:00+00:00",
  "updated_at": "2026-05-26T16:05:00+00:00"
}',
                        ])

                        <h3>Not found</h3>
                        <p><span class="status-pill status-422">404</span></p>
                        @include('docs.partials.code-block', [
                            'label' => 'JSON',
                            'code' => '{
  "success": false,
  "message": "Short link not found."
}',
                        ])
                    </div>
                </div>
            </section>

            <section class="doc-section" id="create-link-web">
                <h2>Create short link (web)</h2>
                <p>Used by the homepage. Same logic as <code>/api/links</code> with fewer parameters.</p>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="method method-post">POST</span>
                        <span class="endpoint-path">/shorten</span>
                    </div>
                    <div class="endpoint-body">
                        <div class="doc-table-wrap">
                            <table class="doc-table">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>original_url</code></td>
                                        <td>Yes</td>
                                        <td>Destination URL</td>
                                    </tr>
                                    <tr>
                                        <td><code>user_agent</code></td>
                                        <td>No</td>
                                        <td>Defaults to browser User-Agent</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p>Browser requests must include <code>X-CSRF-TOKEN</code> (from the page meta tag).</p>
                    </div>
                </div>
            </section>

            <section class="doc-section" id="visit-link">
                <h2>Visit short link</h2>
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="method method-get">GET</span>
                        <span class="endpoint-path">/s/{code}</span>
                    </div>
                    <div class="endpoint-body">
                        <p>Returns HTML bridge page (not JSON). Increments <code>clicks</code> by 1. <code>{code}</code> is alphanumeric.</p>
                        <p><span class="status-pill status-200">200</span> Bridge page &nbsp; <span class="status-pill status-422">404</span> Unknown code</p>
                    </div>
                </div>
            </section>

            <section class="doc-section" id="responses">
                <h2>Response fields</h2>
                <div class="doc-table-wrap">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>success</code></td><td><code>true</code> on success</td></tr>
                            <tr><td><code>id</code></td><td>Database ID</td></tr>
                            <tr><td><code>short_url</code></td><td>Full shareable URL</td></tr>
                            <tr><td><code>short_code</code></td><td>Code used in <code>/s/{code}</code></td></tr>
                            <tr><td><code>original_url</code></td><td>Normalized destination</td></tr>
                            <tr><td><code>redirect_mode</code></td><td><code>bridge</code> or <code>direct</code></td></tr>
                            <tr><td><code>cloaked</code></td><td><code>true</code> when bridge page is used</td></tr>
                            <tr><td><code>page_title</code></td><td>Bridge preview title</td></tr>
                            <tr><td><code>thumbnail_url</code></td><td>Preview image URL (nullable)</td></tr>
                            <tr><td><code>source</code></td><td>Where the link was created (<code>web</code>, <code>api</code>, or custom)</td></tr>
                            <tr><td><code>clicks</code></td><td>Visit count</td></tr>
                            <tr><td><code>user_id</code></td><td>Owner user ID (nullable)</td></tr>
                            <tr><td><code>updated_at</code></td><td>Last update (ISO 8601)</td></tr>
                            <tr><td><code>existing</code></td><td><code>true</code> on create when link already existed</td></tr>
                            <tr><td><code>created_at</code></td><td>ISO 8601 timestamp</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="doc-section" id="errors">
                <h2>Errors</h2>
                <p><span class="status-pill status-422">422</span> Validation failed or invalid URL</p>
                @include('docs.partials.code-block', [
                    'label' => 'Validation error',
                    'code' => '{
  "message": "The original url field must be a valid URL.",
  "errors": {
    "original_url": ["The original url field must be a valid URL."]
  }
}',
                ])
                @include('docs.partials.code-block', [
                    'label' => 'Business error',
                    'code' => '{
  "success": false,
  "message": "Invalid URL"
}',
                ])
            </section>

            <section class="doc-section" id="notes">
                <h2>Behavior notes</h2>
                <h3>URL normalization</h3>
                <ul>
                    <li>Trims whitespace and adds <code>https://</code> if missing</li>
                    <li>Lowercases hostname; removes trailing slash on path</li>
                </ul>
                <h3>Link preview</h3>
                <p>On create, the server may fetch Open Graph tags from the destination. Failures fall back to hostname as title with no thumbnail.</p>
                <h3>Deduplication</h3>
                <p>Same <code>original_url</code>, <code>source</code>, and <code>cloak</code> setting returns an existing link when scoped by <code>user_id</code>, <code>user_agent</code>, or the anonymous bucket.</p>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    document.querySelectorAll('.code-copy').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var pre = btn.closest('.code-block').querySelector('pre');
            var text = pre ? pre.textContent : '';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    if (window.showToast) showToast('Copied to clipboard!', 'success');
                });
            }
        });
    });
})();
</script>
@endpush
