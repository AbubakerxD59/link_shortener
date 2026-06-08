<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\CustomDomainService;
use App\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected UrlShortenerService $urlShortener,
        protected CustomDomainService $customDomains,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $links = ShortLink::query()
            ->with('customDomain')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $links->getCollection()->transform(
            fn (ShortLink $link) => $this->urlShortener->linkDetails($link)
        );

        return view('dashboard', [
            'links' => $links,
            'domainOptions' => $this->customDomains->shortenDomainOptions($user),
        ]);
    }

    public function destroy(Request $request, ShortLink $shortLink): JsonResponse
    {
        if ($shortLink->user_id !== $request->user()->id) {
            abort(403);
        }

        $shortLink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Link deleted.',
        ]);
    }
}
