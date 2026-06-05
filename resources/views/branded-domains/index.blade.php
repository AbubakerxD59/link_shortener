@extends('layouts.app')

@section('title', 'Branded Domains — ' . config('app.name'))

@push('styles')
@include('branded-domains.partials.styles')
@endpush

@section('content')
<section class="branded-domains-page">
    <div class="container">
        <div class="page-header">
            <h1>Branded domains</h1>
            <p>Add multiple custom hostnames for your short links. Each domain can be verified independently and one verified domain is used as the default for new links.</p>
        </div>

        @if (session('domain_status'))
            <div class="domain-alert domain-alert-success">{{ session('domain_status') }}</div>
        @endif

        <div class="panel-card">
            <h2>Add a domain</h2>
            <form method="POST" action="{{ route('branded-domains.store') }}">
                @csrf
                <label for="custom-domain-input" class="domain-label">Branded hostname</label>
                <div class="domain-input-row">
                    <input
                        type="text"
                        id="custom-domain-input"
                        name="domain"
                        class="domain-input"
                        placeholder="go.yourbrand.com"
                        value="{{ old('domain') }}"
                        required
                    >
                    <button type="submit" class="btn btn-primary">Add domain</button>
                </div>
                @error('domain')
                    <p class="domain-alert domain-alert-error" style="margin-top: 0.75rem;">{{ $message }}</p>
                @enderror
                <p class="domain-hint">Use a subdomain you control, such as <code>go</code>, <code>links</code>, or <code>s</code>.</p>
            </form>
        </div>

        <div class="panel-card" style="margin-bottom: 0;">
            <h2>Your domains <span style="color: var(--muted); font-weight: 500;">({{ $domains->count() }})</span></h2>

            @if ($domains->isEmpty())
                <div class="empty-state">
                    <h3>No branded domains yet</h3>
                    <p>Add your first hostname above, then open its details page for DNS connection steps.</p>
                </div>
            @else
                <div class="domains-table-wrap">
                    <table class="domains-table">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($domains as $domain)
                                <tr>
                                    <td><code>{{ $domain->domain }}</code></td>
                                    <td>
                                        @if ($domain->isVerified())
                                            <span class="badge badge-verified">Verified</span>
                                        @else
                                            <span class="badge badge-pending">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($domain->is_default && $domain->isVerified())
                                            <span class="badge badge-default">Default</span>
                                        @elseif ($domain->is_default)
                                            <span class="badge badge-pending">Default*</span>
                                        @else
                                            <span class="date-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="date-muted">
                                        {{ $domain->created_at?->format('M j, Y') }}<br>
                                        {{ $domain->created_at?->format('g:i A') }}
                                    </td>
                                    <td>
                                        <div class="domain-actions-cell">
                                            <a href="{{ route('branded-domains.show', $domain) }}" class="btn btn-outline btn-sm">Details</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="domain-hint" style="margin-top: 1rem;">* Pending domains marked as default will be used for new short links once verified.</p>
            @endif
        </div>
    </div>
</section>
@endsection
