<tr data-link-id="{{ $link['id'] }}" data-short-code="{{ $link['short_code'] }}">
    <td>
        <div class="link-preview">
            @if ($link['thumbnail_url'])
                <img src="{{ $link['thumbnail_url'] }}" alt="" width="40" height="40">
            @else
                <div class="link-preview-placeholder" aria-hidden="true">🔗</div>
            @endif
            <div>
                <p class="link-preview-title">{{ $link['page_title'] ?: $link['short_code'] }}</p>
                <p class="link-preview-id">#{{ $link['id'] }}</p>
            </div>
        </div>
    </td>
    <td class="short-url-cell">
        <div class="short-url-row">
            <span class="short-url-code">{{ $link['short_code'] }}</span>
            <button type="button" class="copy-btn" data-copy-url="{{ $link['short_url'] }}" aria-label="Copy short link" title="Copy link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="9" y="9" width="13" height="13" rx="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
            </button>
        </div>
        <p class="short-url-full">{{ $link['short_url'] }}</p>
    </td>
    <td>
        <span class="badge badge-source">{{ $link['link_domain'] ?? parse_url(config('app.url'), PHP_URL_HOST) }}</span>
    </td>
    <td>
        <div class="dest-url">
            <a href="{{ $link['original_url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['original_url'] }}</a>
        </div>
    </td>
    <td>
        <span class="clicks-count">{{ $link['clicks'] }}</span>
    </td>
    <td>
        @if ($link['cloaked'])
            <span class="badge badge-cloak-on">On</span>
        @else
            <span class="badge badge-cloak-off">Off</span>
        @endif
    </td>
    <td>
        <span class="badge badge-source">{{ $link['source'] ?? 'web' }}</span>
    </td>
    <td class="date-cell">
        @if ($link['created_at'])
            {{ \Carbon\Carbon::parse($link['created_at'])->format('M j, Y') }}<br>
            {{ \Carbon\Carbon::parse($link['created_at'])->format('g:i A') }}
        @else
            —
        @endif
    </td>
    <td class="date-cell">
        @if ($link['updated_at'])
            {{ \Carbon\Carbon::parse($link['updated_at'])->format('M j, Y') }}<br>
            {{ \Carbon\Carbon::parse($link['updated_at'])->format('g:i A') }}
        @else
            —
        @endif
    </td>
</tr>
