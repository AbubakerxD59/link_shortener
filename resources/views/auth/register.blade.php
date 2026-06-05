@extends('layouts.app')

@section('title', 'Sign up — ' . config('app.name'))

@push('styles')
<style>
    .auth-page {
        padding: 2.5rem 0 4rem;
    }

    .auth-card {
        max-width: 420px;
        margin: 0 auto;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
    }

    .auth-card h1 {
        font-size: 1.75rem;
        margin: 0 0 0.5rem;
        text-align: center;
    }

    .auth-lead {
        text-align: center;
        color: var(--muted);
        font-size: 0.9375rem;
        margin: 0 0 1.75rem;
    }

    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .form-field label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--ink-soft);
        margin-bottom: 0.375rem;
    }

    .form-field input[type="email"],
    .form-field input[type="password"],
    .form-field input[type="text"] {
        width: 100%;
        padding: 0.8125rem 0.875rem;
        font-family: var(--font-body);
        font-size: 1rem;
        border: 1px solid var(--line-strong);
        border-radius: var(--radius);
        background: var(--card);
        color: var(--ink);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-field input:focus {
        outline: none;
        border-color: var(--accent-light);
        box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1);
    }

    .form-error {
        margin-top: 0.375rem;
        font-size: 0.8125rem;
        color: var(--danger);
    }

    .form-alert {
        padding: 0.75rem 0.875rem;
        border-radius: var(--radius);
        background: #fef2f2;
        border: 1px solid rgba(220, 38, 38, 0.15);
        color: var(--danger);
        font-size: 0.875rem;
    }

    .auth-footer {
        margin-top: 1.5rem;
        text-align: center;
        font-size: 0.875rem;
        color: var(--muted);
    }

    .auth-footer a {
        color: var(--accent);
        font-weight: 600;
        text-decoration: none;
    }

    .auth-footer a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Create account</h1>
            <p class="auth-lead">Sign up to keep your links organized under your account.</p>

            @if ($errors->any())
                <div class="form-alert" style="margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-field">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                    >
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    >
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                    Sign up
                </button>
            </form>

            <p class="auth-footer">
                Already have an account?
                <a href="{{ route('login') }}">Log in</a>
            </p>
        </div>
    </div>
</section>
@endsection
