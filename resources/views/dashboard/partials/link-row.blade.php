<tr data-link-id="{{ $link['id'] }}" data-short-code="{{ $link['short_code'] }}">
    <td class="short-url-cell">
        <div class="short-url-row">
            <a href="{{ $link['short_url'] }}" target="_blank" rel="noopener noreferrer" class="short-url-link">
                {{ $link['short_url'] }}
            </a>
            <button type="button" class="copy-btn" data-copy-url="{{ $link['short_url'] }}" aria-label="Copy short link" title="Copy link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="9" y="9" width="13" height="13" rx="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
            </button>
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
</tr>
