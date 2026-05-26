<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Redirecting…</title>
    @if ($delaySeconds > 0)
        <meta http-equiv="refresh" content="{{ $delaySeconds }};url={{ e($destinationUrl, false) }}">
    @endif
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
            color: #3f3f46;
            background: #fafaf9;
        }
        p { font-size: 0.9375rem; }
        a { color: #6d28d9; }
    </style>
</head>
<body>
    <p>Redirecting… <a href="{{ $destinationUrl }}" rel="noopener noreferrer">Click here</a> if you are not redirected.</p>
    <script>
        window.location.replace(@json($destinationUrl));
    </script>
</body>
</html>
