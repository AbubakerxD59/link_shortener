<?php

namespace App\Http\Controllers;

use App\Models\CustomDomain;
use App\Models\ShortLink;
use App\Services\CustomDomainService;
use App\Services\LinkPreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShortLinkRedirectController extends Controller
{
    public function __construct(
        protected LinkPreviewService $linkPreview,
        protected CustomDomainService $customDomains,
    ) {}

    public function __invoke(Request $request, string $code): RedirectResponse|View
    {
        $shortLink = ShortLink::where('short_code', $code)->first();

        if (! $shortLink) {
            abort(404, 'Short link not found.');
        }

        $requestHost = CustomDomain::normalizeHost($request->getHost());
        $appHost = $this->customDomains->appHost();

        if ($shortLink->custom_domain_id) {
            $shortLink->loadMissing('customDomain');
            $linkDomain = $shortLink->customDomain;

            $ownerMatches = $linkDomain && (
                $linkDomain->engagyo_user_id !== null
                    ? (int) $shortLink->user_id === (int) $linkDomain->engagyo_user_id
                    : (int) $shortLink->user_id === (int) $linkDomain->user_id
            );

            if (
                ! $linkDomain
                || ! $linkDomain->isVerified()
                || $requestHost !== $linkDomain->domain
                || ! $ownerMatches
            ) {
                abort(404, 'Short link not found.');
            }
        } elseif ($requestHost !== $appHost) {
            abort(404, 'Short link not found.');
        }

        $shortLink->increment('clicks');

        $destinationUrl = $shortLink->original_url;

        if (! $shortLink->isCloaked()) {
            return redirect()->away($destinationUrl, 302);
        }

        $destinationHost = parse_url($destinationUrl, PHP_URL_HOST) ?? $destinationUrl;

        $shortLink->ensureLinkPreview($this->linkPreview);

        return view('redirect.bridge', [
            'destinationUrl' => $destinationUrl,
            'destinationHost' => $destinationHost,
            'pageTitle' => $shortLink->displayTitle($destinationHost),
            'thumbnailUrl' => $shortLink->thumbnail_url,
        ]);
    }
}
