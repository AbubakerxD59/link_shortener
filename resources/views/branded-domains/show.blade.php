@extends('layouts.app')

@section('title', $customDomain->domain . ' — Branded Domains')

@push('styles')
@include('branded-domains.partials.styles')
@endpush

@section('content')
@php
    $domainReady = $customDomain->isVerified() && ($domainSetup['ssl_ok'] ?? false);
    $copyLines = collect($domainSetup['dns_records'])
        ->map(fn ($record) => 'TYPE: '.$record['type']."\n".'NAME: '.$record['name']."\n".'VALUE: '.$record['value'])
        ->implode("\n\n");
@endphp
<section class="branded-domains-page">
    <div class="container">
        <a href="{{ route('branded-domains.index') }}" class="back-link" aria-label="Back to branded domains">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/>
            </svg>
            All branded domains
        </a>

        <div class="page-header">
            <div class="domain-panel-header">
                <div>
                    <h1>{{ $customDomain->domain }}</h1>
                    <p class="domain-lead">
                        @if ($domainReady)
                            This branded domain is verified and ready for short links.
                        @else
                            Add one CNAME record at your domain registrar, then refresh to activate your branded short links.
                        @endif
                    </p>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: flex-end;">
                    @if ($customDomain->isVerified())
                        <span class="badge badge-verified">Verified</span>
                    @else
                        <span class="badge badge-pending">Pending verification</span>
                    @endif
                    @if ($customDomain->is_default)
                        <span class="badge badge-default">Default for new links</span>
                    @endif
                </div>
            </div>
        </div>

        @if (session('domain_status'))
            <div class="domain-alert domain-alert-success">{{ session('domain_status') }}</div>
        @endif

        @if (session('domain_error'))
            <div class="domain-alert domain-alert-error">{{ session('domain_error') }}</div>
        @endif

        @if (session('domain_warning'))
            <div class="domain-alert domain-alert-warning">{{ session('domain_warning') }}</div>
        @endif

        @if ($customDomain->isVerified() && ! ($domainSetup['ssl_ok'] ?? true))
            <div class="domain-alert domain-alert-warning">
                <strong>HTTPS is not working for {{ $customDomain->domain }}</strong>
                <p>DNS is correct, but browsers cannot open <code>https://{{ $customDomain->domain }}</code> yet. This is why you may see <em>ERR_SSL_PROTOCOL_ERROR</em>. Enable SSL for this hostname using one of the options below.</p>
            </div>
        @endif

        @error('domain')
            <div class="domain-alert domain-alert-error">{{ $message }}</div>
        @enderror

        <div class="panel-card">
            @include('branded-domains.partials.setup-instructions', ['domainSetup' => $domainSetup])

            <div class="domain-actions">
                @unless ($domainReady)
                    <button
                        type="button"
                        class="btn btn-outline"
                        data-copy-text="{{ e($copyLines) }}"
                    >Copy</button>
                @endunless

                @if (! $domainReady)
                    <form method="POST" action="{{ route('branded-domains.verify', $customDomain) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Refresh</button>
                    </form>
                @endif

                @if ($customDomain->isVerified() && ! $customDomain->is_default)
                    <form method="POST" action="{{ route('branded-domains.default', $customDomain) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline">Set as default</button>
                    </form>
                @endif

                <form
                    method="POST"
                    action="{{ route('branded-domains.destroy', $customDomain) }}"
                    onsubmit="return confirm('Remove {{ $customDomain->domain }}? Short links using this domain will revert to the default app URL.');"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline domain-remove-btn">Remove domain</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@include('branded-domains.partials.copy-script')
@endpush
