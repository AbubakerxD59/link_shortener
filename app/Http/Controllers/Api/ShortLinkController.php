<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use App\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortLinkController extends Controller
{
    public function __construct(protected UrlShortenerService $urlShortener) {}

    /**
     * Store a short link (JSON body or form params).
     *
     * Params: original_url (required), user_id, user_agent, ip_address, page_title, thumbnail_url, source (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'original_url' => 'required|url|max:2048',
            'user_id' => 'nullable|integer|min:1',
            'user_agent' => 'nullable|string|max:65535',
            'ip_address' => 'nullable|ip',
            'page_title' => 'nullable|string|max:500',
            'thumbnail_url' => 'nullable|url|max:2048',
            'source' => 'nullable|string|max:64',
        ]);

        $userAgent = $validated['user_agent'] ?? $request->userAgent();
        $ipAddress = $validated['ip_address'] ?? $request->ip();

        $result = $this->urlShortener->shortenPublic(
            $validated['original_url'],
            $validated['user_id'] ?? null,
            $userAgent,
            $ipAddress,
            $validated['page_title'] ?? null,
            $validated['thumbnail_url'] ?? null,
            $validated['source'] ?? ShortLink::SOURCE_API,
        );

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result, $result['existing'] ? 200 : 201);
    }

    /**
     * Get short link details including click count.
     */
    public function show(string $code): JsonResponse
    {
        $shortLink = ShortLink::where('short_code', $code)->first();

        if (! $shortLink) {
            return response()->json([
                'success' => false,
                'message' => 'Short link not found.',
            ], 404);
        }

        return response()->json($this->urlShortener->linkDetails($shortLink));
    }
}
