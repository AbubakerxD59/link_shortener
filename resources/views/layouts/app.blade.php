<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ShrtLnk'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:500,600,700|dm-sans:400,500,600" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --ink: #0c0c10;
            --ink-soft: #3f3f46;
            --muted: #71717a;
            --line: rgba(12, 12, 16, 0.08);
            --line-strong: rgba(12, 12, 16, 0.12);
            --surface: #f4f4f5;
            --surface-warm: #fafaf9;
            --card: #ffffff;
            --accent: #6d28d9;
            --accent-mid: #7c3aed;
            --accent-light: #a78bfa;
            --accent-glow: rgba(109, 40, 217, 0.35);
            --accent-soft: rgba(109, 40, 217, 0.06);
            --success: #059669;
            --success-soft: #ecfdf5;
            --danger: #dc2626;
            --radius: 14px;
            --radius-lg: 22px;
            --radius-pill: 999px;
            --shadow-sm: 0 1px 2px rgba(12, 12, 16, 0.04);
            --shadow-md: 0 4px 24px rgba(12, 12, 16, 0.06), 0 12px 48px rgba(109, 40, 217, 0.08);
            --shadow-lg: 0 24px 64px rgba(12, 12, 16, 0.1), 0 8px 24px rgba(109, 40, 217, 0.12);
            --font-display: 'Sora', system-ui, sans-serif;
            --font-body: 'DM Sans', system-ui, sans-serif;
            --ease-out: cubic-bezier(0.22, 1, 0.36, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: var(--font-body);
            color: var(--ink);
            background: var(--surface-warm);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3 {
            font-family: var(--font-display);
            font-weight: 600;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .container {
            width: 100%;
            max-width: 1080px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 1rem 0;
        }

        .site-header .inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            padding: 0 1.25rem;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid var(--line);
            border-radius: var(--radius-pill);
            backdrop-filter: blur(16px) saturate(1.4);
            box-shadow: var(--shadow-sm);
        }

        .logo {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.0625rem;
            color: var(--ink);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            letter-spacing: -0.02em;
        }

        .logo-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(145deg, var(--accent-mid), var(--accent));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            box-shadow: 0 4px 14px var(--accent-glow);
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .header-nav a {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            padding: 0.5rem 0.875rem;
            border-radius: var(--radius-pill);
            transition: color 0.2s, background 0.2s;
        }

        .header-nav a:hover {
            color: var(--ink);
            background: var(--surface);
        }

        .header-nav a.active {
            color: var(--accent);
            background: var(--accent-soft);
        }

        .header-nav .user-greeting {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--ink-soft);
            padding: 0.5rem 0.875rem;
        }

        .header-nav .logout-form {
            display: inline;
            margin: 0;
        }

        .header-nav .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            color: var(--muted);
            background: none;
            border: none;
            padding: 0;
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: color 0.2s, background 0.2s;
        }

        .header-nav .logout-btn svg {
            width: 18px;
            height: 18px;
        }

        .header-nav .logout-btn:hover {
            color: var(--ink);
            background: var(--surface);
        }

        .site-footer {
            margin-top: auto;
            padding: 2.5rem 0 3rem;
            color: var(--muted);
            font-size: 0.8125rem;
            text-align: center;
        }

        .site-footer span {
            color: var(--ink-soft);
            font-weight: 500;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8125rem 1.375rem;
            font-family: var(--font-body);
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s var(--ease-out), box-shadow 0.2s var(--ease-out), background 0.2s;
            white-space: nowrap;
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-primary {
            background: linear-gradient(145deg, var(--accent-mid) 0%, var(--accent) 100%);
            color: #fff;
            box-shadow: 0 4px 16px var(--accent-glow);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px var(--accent-glow);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-outline {
            background: var(--card);
            color: var(--accent);
            border: 1px solid var(--line-strong);
        }

        .btn-outline:hover:not(:disabled) {
            border-color: var(--accent-light);
            background: var(--accent-soft);
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--accent);
            margin-bottom: 0.75rem;
        }

        .section-label::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent-mid);
        }

        .section-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin: 0 0 0.625rem;
            text-align: center;
        }

        .section-subtitle {
            text-align: center;
            color: var(--muted);
            font-size: 1.0625rem;
            max-width: 480px;
            margin: 0 auto 3rem;
            line-height: 1.65;
        }

        .toast {
            position: fixed;
            bottom: 1.75rem;
            left: 50%;
            transform: translateX(-50%) translateY(12px);
            padding: 0.875rem 1.25rem;
            border-radius: var(--radius-pill);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 100;
            opacity: 0;
            transition: opacity 0.25s var(--ease-out), transform 0.25s var(--ease-out);
            pointer-events: none;
            box-shadow: var(--shadow-lg);
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .toast.success { background: var(--ink); }
        .toast.error { background: var(--danger); }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container inner">
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-mark">SL</span>
                {{ config('app.name', 'ShrtLnk') }}
            </a>
            <nav class="header-nav" aria-label="Main">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) class="active" @endif>Home</a>
                <a href="{{ route('docs') }}" @if(request()->routeIs('docs')) class="active" @endif>API Docs</a>
                @auth
                    <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) class="active" @endif>Dashboard</a>
                    <a href="{{ route('branded-domains.index') }}" @if(request()->routeIs('branded-domains.*')) class="active" @endif>Branded Domains</a>
                    <span class="user-greeting">{{ auth()->user()->name }}</span>
                    <form class="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn" aria-label="Log out" title="Log out">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" @if(request()->routeIs('login')) class="active" @endif>Log in</a>
                    <a href="{{ route('register') }}" @if(request()->routeIs('register')) class="active" @endif>Sign up</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            &copy; {{ date('Y') }} <span>{{ config('app.name', 'ShrtLnk') }}</span>
        </div>
    </footer>

    <div id="toast" class="toast" role="status" aria-live="polite"></div>

    <script>
        window.showToast = function(message, type) {
            var el = document.getElementById('toast');
            el.textContent = message;
            el.className = 'toast show ' + (type || 'success');
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(function() {
                el.classList.remove('show');
            }, 3000);
        };
    </script>
    @stack('scripts')
</body>
</html>
