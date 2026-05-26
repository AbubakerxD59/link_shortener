<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\LinkPreviewService;
use Illuminate\Contracts\View\View;

class ShortLinkRedirectController extends Controller
{
    public function __construct(protected LinkPreviewService $linkPreview) {}

    public function __invoke(string $code): View
    {
        $shortLink = ShortLink::where('short_code', $code)->first();

        if (! $shortLink) {
            abort(404, 'Short link not found.');
        }

        $shortLink->increment('clicks');

        $destinationUrl = $shortLink->original_url;
        $destinationHost = parse_url($destinationUrl, PHP_URL_HOST) ?? $destinationUrl;
        $delaySeconds = 5;

        $shortLink->ensureLinkPreview($this->linkPreview);

        return view('redirect.bridge', [
            'destinationUrl' => $destinationUrl,
            'destinationHost' => $destinationHost,
            'delaySeconds' => $delaySeconds,
            'pageTitle' => $shortLink->displayTitle($destinationHost),
            'thumbnailUrl' => $shortLink->thumbnail_url,
        ]);
    }
}
