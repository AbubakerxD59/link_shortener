@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@push('styles')
    <style>
        .dashboard {
            padding: 2rem 0 4rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            margin: 0 0 0.5rem;
        }

        .dashboard-header p {
            margin: 0;
            color: var(--muted);
            font-size: 1rem;
        }

        .panel-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .panel-card h2 {
            font-size: 1.125rem;
            margin: 0 0 1.25rem;
        }

        .shortener-row {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            background: var(--surface);
            border-radius: var(--radius);
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
            padding: 0.875rem 0;
            font-family: var(--font-body);
            font-size: 1rem;
            border: none;
            outline: none;
            background: transparent;
            color: var(--ink);
        }

        .cloak-options {
            margin-top: 0.875rem;
            padding: 0.875rem 1rem;
            background: var(--surface);
            border-radius: var(--radius);
        }

        .cloak-toggle {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
        }

        .cloak-toggle input {
            width: 1.125rem;
            height: 1.125rem;
            margin: 0.125rem 0 0;
            accent-color: var(--accent);
            flex-shrink: 0;
        }

        .cloak-toggle-text strong {
            display: block;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.125rem;
        }

        .cloak-toggle-text span {
            font-size: 0.8125rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .domain-select-wrap {
            margin-top: 0.875rem;
            padding: 0.875rem 1rem;
            background: var(--surface);
            border-radius: var(--radius);
            text-align: left;
        }

        .domain-select-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--ink-soft);
            margin-bottom: 0.5rem;
        }

        .domain-select {
            width: 100%;
            padding: 0.75rem 0.875rem;
            font-family: var(--font-body);
            font-size: 0.9375rem;
            border: 1px solid var(--line-strong);
            border-radius: var(--radius);
            background: var(--card);
            color: var(--ink);
            cursor: pointer;
        }

        .domain-select:focus {
            outline: none;
            border-color: var(--accent-light);
            box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1);
        }

        .domain-select-hint {
            margin: 0.5rem 0 0;
            font-size: 0.8125rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .domain-select-hint a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }

        .domain-select-hint a:hover {
            text-decoration: underline;
        }

        .error-msg {
            color: var(--danger);
            font-size: 0.875rem;
            margin: 0.75rem 0 0;
            display: none;
        }

        .error-msg.visible {
            display: block;
        }

        .links-section h2 {
            font-size: 1.125rem;
            margin: 0 0 1rem;
        }

        .links-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            background: var(--card);
            box-shadow: var(--shadow-sm);
        }

        .links-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            min-width: 560px;
        }

        .links-table th,
        .links-table td {
            padding: 0.875rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        .links-table th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            background: var(--surface);
        }

        .links-table tbody tr:last-child td {
            border-bottom: none;
        }

        .links-table tbody tr:hover {
            background: var(--accent-soft);
        }

        .short-url-cell {
            min-width: 200px;
        }

        .short-url-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .short-url-link {
            font-family: ui-monospace, 'Cascadia Code', monospace;
            font-size: 0.8125rem;
            color: var(--accent);
            font-weight: 600;
            word-break: break-all;
            text-decoration: none;
            line-height: 1.45;
        }

        .short-url-link:hover {
            text-decoration: underline;
        }

        .copy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
            border: 1px solid var(--line-strong);
            border-radius: 8px;
            background: var(--card);
            color: var(--muted);
            cursor: pointer;
            transition: color 0.2s, background 0.2s, border-color 0.2s;
        }

        .copy-btn:hover {
            color: var(--accent);
            border-color: var(--accent-light);
            background: var(--accent-soft);
        }

        .copy-btn svg {
            width: 14px;
            height: 14px;
        }

        .link-actions-cell {
            width: 48px;
            text-align: center;
        }

        .delete-link-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
            border: 1px solid var(--line-strong);
            border-radius: 8px;
            background: var(--card);
            color: var(--muted);
            cursor: pointer;
            transition: color 0.2s, background 0.2s, border-color 0.2s;
        }

        .delete-link-btn:hover {
            color: var(--danger);
            border-color: rgba(220, 38, 38, 0.25);
            background: #fef2f2;
        }

        .delete-link-btn svg {
            width: 14px;
            height: 14px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-pill);
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-cloak-on {
            background: rgba(109, 40, 217, 0.1);
            color: var(--accent);
        }

        .badge-cloak-off {
            background: var(--surface);
            color: var(--muted);
        }

        .badge-source {
            background: var(--surface);
            color: var(--ink-soft);
            text-transform: lowercase;
        }

        .clicks-count {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1rem;
            color: var(--ink);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--muted);
        }

        .empty-state-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            opacity: 0.6;
        }

        .empty-state h3 {
            font-size: 1.125rem;
            color: var(--ink-soft);
            margin: 0 0 0.5rem;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.9375rem;
        }

        .pagination-wrap {
            margin-top: 1.25rem;
            display: flex;
            justify-content: center;
        }

        .pagination-wrap nav {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-wrap a,
        .pagination-wrap span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 0.5rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            text-decoration: none;
            color: var(--ink-soft);
            border: 1px solid var(--line);
            background: var(--card);
        }

        .pagination-wrap a:hover {
            border-color: var(--accent-light);
            color: var(--accent);
            background: var(--accent-soft);
        }

        .pagination-wrap span[aria-current="page"] {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .pagination-wrap span[aria-disabled="true"] {
            opacity: 0.45;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome, {{ auth()->user()->name }}</h1>
                <p>Shorten links and manage everything you've created in one place.</p>
            </div>

            <div class="panel-card">
                <h2>Shorten a link</h2>
                <div class="shortener-row">
                    <div class="input-wrap">
                        <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                        <input type="url" id="shorten-url-input" class="url-input"
                            placeholder="Paste your long URL here…" autocomplete="url">
                    </div>
                    <button type="button" class="btn btn-primary" id="btn-shorten" style="min-width: 148px;">
                        Shorten link
                    </button>
                </div>

                <div class="domain-select-wrap">
                    <label for="domain-select" class="domain-select-label">Redirect domain</label>
                    <select id="domain-select" class="domain-select" name="custom_domain_id">
                        @foreach ($domainOptions as $option)
                            <option value="{{ $option['id'] ?? '' }}">
                                @if ($option['type'] === 'default')
                                    {{ $option['label'] }}
                                @else
                                    {{ $option['label'] }}@if (!empty($option['is_default']))
                                    @endif
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if (count($domainOptions) === 1)
                        <p class="domain-select-hint">
                            Add a verified branded domain in
                            <a href="{{ route('branded-domains.index') }}">Branded Domains</a>
                            to use your own hostname.
                        </p>
                    @else
                        <p class="domain-select-hint">Choose which domain visitors will see in the short link URL.</p>
                    @endif
                </div>

                <div class="cloak-options">
                    <label class="cloak-toggle" for="cloak-enabled">
                        <input type="checkbox" id="cloak-enabled" name="cloak">
                        <span class="cloak-toggle-text">
                            <strong>Enable link cloaking</strong>
                            <span>When on, visitors see a preview page before continuing. When off, they go straight to the
                                destination.</span>
                        </span>
                    </label>
                </div>

                <p id="shorten-error" class="error-msg" role="alert"></p>
            </div>

            <div class="links-section">
                <h2>Your links <span id="links-count"
                        style="color: var(--muted); font-weight: 500;">({{ $links->total() }})</span></h2>

                <div class="links-table-wrap" id="links-table-wrap">
                    @if ($links->isEmpty())
                        <div class="empty-state" id="links-empty">
                            <div class="empty-state-icon" aria-hidden="true">🔗</div>
                            <h3>No links yet</h3>
                            <p>Shorten your first URL above and it will appear here.</p>
                        </div>
                    @else
                        <table class="links-table" id="links-table">
                            <thead>
                                <tr>
                                    <th>Short link</th>
                                    <th>Clicks</th>
                                    <th>Cloaking</th>
                                    <th>Source</th>
                                    <th aria-label="Actions"></th>
                                </tr>
                            </thead>
                            <tbody id="links-tbody">
                                @foreach ($links as $link)
                                    @include('dashboard.partials.link-row', ['link' => $link])
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                @if ($links->hasPages())
                    <div class="pagination-wrap">
                        @if ($links->onFirstPage())
                            <span aria-disabled="true">Previous</span>
                        @else
                            <a href="{{ $links->previousPageUrl() }}">Previous</a>
                        @endif

                        <span aria-current="page">Page {{ $links->currentPage() }} of {{ $links->lastPage() }}</span>

                        @if ($links->hasMorePages())
                            <a href="{{ $links->nextPageUrl() }}">Next</a>
                        @else
                            <span aria-disabled="true">Next</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function() {
            var shortenUrl = @json(route('shorten'));
            var linksDestroyBaseUrl = @json(url('links'));
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var btn = document.getElementById('btn-shorten');
            var input = document.getElementById('shorten-url-input');
            var errorEl = document.getElementById('shorten-error');
            var cloakEnabled = document.getElementById('cloak-enabled');
            var domainSelect = document.getElementById('domain-select');
            var linksTableWrap = document.getElementById('links-table-wrap');
            var linksTbody = document.getElementById('links-tbody');
            var linksEmpty = document.getElementById('links-empty');
            var linksCount = document.getElementById('links-count');

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
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function deleteButtonHtml(linkId) {
                return '<td class="link-actions-cell">' +
                    '<button type="button" class="delete-link-btn" data-link-id="' + escapeHtml(String(linkId)) +
                    '" aria-label="Delete link" title="Delete link">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">' +
                    '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>' +
                    '<path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>' +
                    '<path d="M10 11v6"/><path d="M14 11v6"/></svg></button></td>';
            }

            function buildRowHtml(link) {
                var cloakBadge = link.cloaked ?
                    '<span class="badge badge-cloak-on">On</span>' :
                    '<span class="badge badge-cloak-off">Off</span>';

                return '<tr data-link-id="' + escapeHtml(String(link.id)) + '" data-short-code="' + escapeHtml(link
                        .short_code) + '">' +
                    '<td class="short-url-cell"><div class="short-url-row">' +
                    '<a href="' + escapeHtml(link.short_url) + '" target="_blank" rel="noopener noreferrer" class="short-url-link">' +
                    escapeHtml(link.short_url) + '</a>' +
                    '<button type="button" class="copy-btn" data-copy-url="' + escapeHtml(link.short_url) +
                    '" aria-label="Copy short link" title="Copy link">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>' +
                    '</button></div></td>' +
                    '<td><span class="clicks-count">' + escapeHtml(String(link.clicks || 0)) + '</span></td>' +
                    '<td>' + cloakBadge + '</td>' +
                    '<td><span class="badge badge-source">' + escapeHtml(link.source || 'web') + '</span></td>' +
                    deleteButtonHtml(link.id) +
                    '</tr>';
            }

            function ensureTable() {
                if (linksTbody) return;

                if (linksEmpty) {
                    linksEmpty.remove();
                }

                linksTableWrap.innerHTML = '<table class="links-table" id="links-table">' +
                    '<thead><tr>' +
                    '<th>Short link</th><th>Clicks</th><th>Cloaking</th><th>Source</th>' +
                    '<th aria-label="Actions"></th>' +
                    '</tr></thead><tbody id="links-tbody"></tbody></table>';

                linksTbody = document.getElementById('links-tbody');
            }

            function upsertLinkRow(link) {
                ensureTable();

                var existing = linksTbody.querySelector('[data-link-id="' + link.id + '"]');
                var html = buildRowHtml(link);

                if (existing) {
                    existing.outerHTML = html;
                } else {
                    linksTbody.insertAdjacentHTML('afterbegin', html);
                    if (linksCount) {
                        var match = linksCount.textContent.match(/\((\d+)\)/);
                        var total = match ? parseInt(match[1], 10) + 1 : 1;
                        linksCount.textContent = '(' + total + ')';
                    }
                }
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

            function decrementLinksCount() {
                if (!linksCount) return;
                var match = linksCount.textContent.match(/\((\d+)\)/);
                if (!match) return;
                var total = Math.max(0, parseInt(match[1], 10) - 1);
                linksCount.textContent = '(' + total + ')';
            }

            function showEmptyLinksState() {
                if (!linksTableWrap) return;
                linksTableWrap.innerHTML =
                    '<div class="empty-state" id="links-empty">' +
                    '<div class="empty-state-icon" aria-hidden="true">🔗</div>' +
                    '<h3>No links yet</h3>' +
                    '<p>Shorten your first URL above and it will appear here.</p>' +
                    '</div>';
                linksTbody = null;
            }

            function deleteLinkRow(linkId) {
                if (!linksTbody) return;
                var row = linksTbody.querySelector('[data-link-id="' + linkId + '"]');
                if (!row) return;
                row.remove();
                decrementLinksCount();
                if (!linksTbody.children.length) {
                    showEmptyLinksState();
                }
            }

            document.addEventListener('click', function(e) {
                var copyBtn = e.target.closest('.copy-btn');
                if (copyBtn) {
                    var text = copyBtn.getAttribute('data-copy-url') || copyBtn.getAttribute('data-copy-text');
                    if (!text) return;
                    copyText(text)
                        .then(function() {
                            showToast('Copied to clipboard!', 'success');
                        })
                        .catch(function() {
                            showToast('Could not copy. Select and copy manually.', 'error');
                        });
                    return;
                }

                var deleteBtn = e.target.closest('.delete-link-btn');
                if (!deleteBtn) return;

                var linkId = deleteBtn.getAttribute('data-link-id');
                if (!linkId) return;

                if (!confirm('Delete this short link? This cannot be undone.')) {
                    return;
                }

                deleteBtn.disabled = true;

                fetch(linksDestroyBaseUrl + '/' + linkId, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return {
                                ok: res.ok,
                                data: data
                            };
                        });
                    })
                    .then(function(r) {
                        if (r.ok && r.data.success) {
                            deleteLinkRow(linkId);
                            showToast(r.data.message || 'Link deleted.', 'success');
                        } else {
                            showToast(r.data.message || 'Could not delete link.', 'error');
                            deleteBtn.disabled = false;
                        }
                    })
                    .catch(function() {
                        showToast('Network error. Please try again.', 'error');
                        deleteBtn.disabled = false;
                    });
            });

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
                            cloak: cloakEnabled.checked,
                            custom_domain_id: domainSelect && domainSelect.value ? parseInt(
                                domainSelect.value, 10) : null
                        })
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return {
                                ok: res.ok,
                                data: data
                            };
                        });
                    })
                    .then(function(r) {
                        if (r.data.success && r.data.short_url) {
                            upsertLinkRow(r.data);
                            input.value = '';
                            showToast(r.data.existing ? 'Existing short link returned.' : 'Link shortened!',
                                'success');
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
        })();
    </script>
@endpush
