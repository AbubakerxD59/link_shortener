<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>{{ $pageTitle }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:600|dm-sans:400,500" rel="stylesheet">
    <style>
        :root {
            --ink: #0c0c10;
            --muted: #71717a;
            --accent: #6d28d9;
            --card: #ffffff;
            --surface: #f4f4f5;
            --radius: 14px;
            --font-display: 'Sora', system-ui, sans-serif;
            --font-body: 'DM Sans', system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: var(--font-body);
            color: var(--ink);
            background: linear-gradient(160deg, #fafaf9 0%, #f4f4f5 50%, #ede9fe 100%);
        }
        .card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            text-align: center;
            background: var(--card);
            border: 1px solid rgba(12, 12, 16, 0.08);
            border-radius: 22px;
            box-shadow: 0 4px 24px rgba(12, 12, 16, 0.06), 0 12px 48px rgba(109, 40, 217, 0.08);
        }
        .brand {
            font-family: var(--font-display);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--accent);
            margin: 0 0 1.25rem;
        }
        .preview {
            margin: 0 0 1.25rem;
            text-align: left;
            border: 1px solid rgba(12, 12, 16, 0.08);
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--surface);
        }
        .preview-thumb {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: contain;
            background: #e4e4e7;
        }
        .preview-body {
            padding: 1rem 1.125rem;
        }
        .preview-title {
            font-family: var(--font-display);
            font-size: 1.0625rem;
            font-weight: 600;
            margin: 0 0 0.375rem;
            line-height: 1.35;
            letter-spacing: -0.02em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .preview-host {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--muted);
            word-break: break-all;
        }
        h1 {
            font-family: var(--font-display);
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
            letter-spacing: -0.02em;
        }
        .lead {
            margin: 0 0 1.25rem;
            color: var(--muted);
            font-size: 0.875rem;
            line-height: 1.6;
        }
        .btn-continue {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-family: var(--font-body);
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
            text-decoration: none;
            background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);
            border-radius: var(--radius);
            box-shadow: 0 4px 14px rgba(109, 40, 217, 0.35);
        }
        .btn-continue:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(109, 40, 217, 0.4);
        }
    </style>
</head>
<body>
    <div class="card">
        <p class="brand">{{ config('app.name') }}</p>

        <article class="preview">
            @if ($thumbnailUrl)
                <img
                    class="preview-thumb"
                    src="{{ $thumbnailUrl }}"
                    alt=""
                    width="640"
                    height="360"
                    loading="eager"
                    referrerpolicy="no-referrer"
                >
            @endif
            <div class="preview-body">
                <h2 class="preview-title">{{ $pageTitle }}</h2>
                <p class="preview-host">{{ $destinationHost }}</p>
            </div>
        </article>

        <h1>Continue to this link?</h1>
        <p class="lead">You are leaving {{ parse_url(config('app.url'), PHP_URL_HOST) }}. Click Continue when you are ready.</p>
        <a class="btn-continue" id="continue-link" href="{{ $destinationUrl }}" rel="noopener noreferrer">Continue</a>
    </div>
</body>
</html>
