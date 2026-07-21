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
            <p>Add multiple custom hostnames for your short links. After you submit a domain, our team reviews and activates it within about 24 hours. One activated domain can be set as the default for new links.</p>
        </div>

        @if (session('domain_status'))
            <div class="domain-alert domain-alert-success">{{ session('domain_status') }}</div>
        @endif

        <div class="panel-card">
            <h2>Add a domain</h2>
            <form method="POST" action="{{ route('branded-domains.store') }}" id="add-domain-form">
                @csrf

                <label for="base-domain-input" class="domain-label">Your domain</label>
                <input
                    type="text"
                    id="base-domain-input"
                    name="base_domain"
                    class="domain-input"
                    placeholder="yourbrand.com"
                    value="{{ old('base_domain') }}"
                    required
                >
                <p class="domain-hint">Enter the domain you own, without <code>http://</code> or <code>www</code>.</p>

                <fieldset class="domain-type-fieldset">
                    <legend class="domain-label">How do you want to use it?</legend>
                    <div class="domain-type-options">
                        <label class="domain-type-option">
                            <input
                                type="radio"
                                name="domain_type"
                                value="subdomain"
                                {{ old('domain_type', 'subdomain') === 'subdomain' ? 'checked' : '' }}
                            >
                            <span class="domain-type-card">
                                <strong>Use a subdomain</strong>
                                <span>Recommended — e.g. <code>go.yourbrand.com</code> or <code>shrtlnk.yourbrand.com</code></span>
                            </span>
                        </label>
                        <label class="domain-type-option">
                            <input
                                type="radio"
                                name="domain_type"
                                value="apex"
                                {{ old('domain_type') === 'apex' ? 'checked' : '' }}
                            >
                            <span class="domain-type-card">
                                <strong>Use the main domain</strong>
                                <span>Use <code>yourbrand.com</code> directly for short links</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div id="subdomain-prefix-wrap" class="subdomain-prefix-wrap">
                    <label for="subdomain-prefix-input" class="domain-label">Subdomain label</label>
                    <div class="subdomain-prefix-row">
                        <input
                            type="text"
                            id="subdomain-prefix-input"
                            name="subdomain_prefix"
                            class="domain-input subdomain-prefix-input"
                            placeholder="go"
                            value="{{ old('subdomain_prefix', 'go') }}"
                            pattern="[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?"
                        >
                        <span class="subdomain-suffix" id="subdomain-suffix">.yourbrand.com</span>
                    </div>
                    <p class="domain-hint">Popular choices: <code>go</code>, <code>links</code>, <code>shrtlnk</code></p>
                </div>

                <p class="domain-preview" id="domain-preview" aria-live="polite">
                    Your short links will use: <strong><code id="domain-preview-value">go.yourbrand.com</code></strong>
                </p>

                @error('domain')
                    <p class="domain-alert domain-alert-error" style="margin-top: 0.75rem;">{{ $message }}</p>
                @enderror

                <div class="domain-form-actions">
                    <button type="submit" class="btn btn-primary">Add domain</button>
                </div>
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
                                <th>Type</th>
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
                                        @if ($domain->isApex())
                                            <span class="date-muted">Main domain</span>
                                        @else
                                            <span class="date-muted">Subdomain</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($domain->isVerified())
                                            <span class="badge badge-verified">Active</span>
                                        @else
                                            <span class="badge badge-pending">Pending review</span>
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

@push('scripts')
<script>
(function() {
    var form = document.getElementById('add-domain-form');
    if (!form) return;

    var baseInput = document.getElementById('base-domain-input');
    var prefixInput = document.getElementById('subdomain-prefix-input');
    var prefixWrap = document.getElementById('subdomain-prefix-wrap');
    var suffixEl = document.getElementById('subdomain-suffix');
    var previewValue = document.getElementById('domain-preview-value');
    var typeRadios = form.querySelectorAll('input[name="domain_type"]');

    function selectedType() {
        var checked = form.querySelector('input[name="domain_type"]:checked');
        return checked ? checked.value : 'subdomain';
    }

    function normalizedBase() {
        return (baseInput.value || 'yourbrand.com')
            .trim()
            .toLowerCase()
            .replace(/^https?:\/\//, '')
            .replace(/^www\./, '')
            .replace(/\/.*$/, '');
    }

    function normalizedPrefix() {
        return (prefixInput.value || 'go').trim().toLowerCase();
    }

    function updateForm() {
        var base = normalizedBase();
        var isSubdomain = selectedType() === 'subdomain';

        prefixWrap.style.display = isSubdomain ? 'block' : 'none';
        prefixInput.required = isSubdomain;
        suffixEl.textContent = '.' + (base || 'yourbrand.com');

        previewValue.textContent = isSubdomain
            ? normalizedPrefix() + '.' + (base || 'yourbrand.com')
            : (base || 'yourbrand.com');
    }

    baseInput.addEventListener('input', updateForm);
    prefixInput.addEventListener('input', updateForm);
    typeRadios.forEach(function(radio) {
        radio.addEventListener('change', updateForm);
    });

    updateForm();
})();
</script>
@endpush
