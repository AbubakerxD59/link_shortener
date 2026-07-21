@extends('layouts.app')

@section('title', 'Pending Branded Domains — Admin')

@push('styles')
@include('branded-domains.partials.styles')
<style>
    .admin-instructions {
        background: var(--accent-soft);
        border: 1px solid rgba(109, 40, 217, 0.15);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
    }

    .admin-instructions h2 {
        font-size: 1.0625rem;
        margin: 0 0 0.75rem;
    }

    .admin-instructions ol {
        margin: 0;
        padding-left: 1.25rem;
        color: var(--ink-soft);
        font-size: 0.9375rem;
        line-height: 1.7;
    }

    .admin-instructions li + li {
        margin-top: 0.5rem;
    }

    .admin-instructions code {
        font-size: 0.875em;
        background: rgba(12, 12, 16, 0.06);
        padding: 0.1em 0.35em;
        border-radius: 4px;
    }

    .admin-meta {
        font-size: 0.8125rem;
        color: var(--muted);
        line-height: 1.45;
    }

    .admin-meta strong {
        color: var(--ink-soft);
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<section class="branded-domains-page">
    <div class="container">
        <div class="page-header">
            <h1>Pending branded domains</h1>
            <p>Domains waiting for Hostinger setup and activation. Park each hostname on your main site, then mark it active.</p>
        </div>

        @if (session('domain_status'))
            <div class="domain-alert domain-alert-success">{{ session('domain_status') }}</div>
        @endif

        <div class="admin-instructions">
            <h2>How to activate a domain on Hostinger</h2>
            <ol>
                <li>Sign in to <strong>Hostinger hPanel</strong> for the account that hosts <code>{{ $cnameTarget }}</code>.</li>
                <li>Open the website for <code>{{ $cnameTarget }}</code> (your main shortener site).</li>
                <li>Go to <strong>Domains → Parked Domains</strong> (sometimes labeled <strong>Parked Domains</strong> under the site).</li>
                <li>Add the branded hostname exactly as listed below (e.g. <code>go.customer.com</code>) and save.</li>
                <li>Confirm the customer’s CNAME points to <code>{{ $cnameTarget }}</code>. If they use Cloudflare, SSL/TLS mode should be <strong>Flexible</strong> on shared hosting.</li>
                <li>Test with <code>curl -I https://their-domain/</code> — you should not see Error 525 or a TLS failure.</li>
                <li>Back here, click <strong>Mark active</strong> so the user can use the domain for short links.</li>
            </ol>
        </div>

        <div class="panel-card" style="margin-bottom: 0;">
            <h2>Awaiting activation <span style="color: var(--muted); font-weight: 500;">({{ $pendingDomains->count() }})</span></h2>

            @if ($pendingDomains->isEmpty())
                <div class="empty-state">
                    <h3>No pending domains</h3>
                    <p>All registered branded domains are active, or none have been submitted yet.</p>
                </div>
            @else
                <div class="domains-table-wrap">
                    <table class="domains-table">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Type</th>
                                <th>Owner</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingDomains as $domain)
                                <tr>
                                    <td>
                                        <code>{{ $domain->domain }}</code>
                                        <div class="admin-meta" style="margin-top: 0.35rem;">
                                            Park this hostname on <code>{{ $cnameTarget }}</code>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($domain->isApex())
                                            <span class="date-muted">Main domain</span>
                                        @else
                                            <span class="date-muted">Subdomain</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-meta">
                                            <strong>{{ $domain->user?->name ?? 'Unknown' }}</strong><br>
                                            {{ $domain->user?->email }}
                                        </div>
                                    </td>
                                    <td class="date-muted">
                                        {{ $domain->created_at?->format('M j, Y') }}<br>
                                        {{ $domain->created_at?->format('g:i A') }}
                                    </td>
                                    <td>
                                        <div class="domain-actions-cell">
                                            <form method="POST" action="{{ route('admin.branded-domains.activate', $domain) }}" onsubmit="return confirm('Mark {{ $domain->domain }} as active? Only do this after parking it on Hostinger.');">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">Mark active</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
