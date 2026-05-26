<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortenController extends Controller
{
    public function __construct(protected UrlShortenerService $urlShortener) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'original_url' => 'required|url|max:2048',
        ]);

        $userAgent = $request->input('user_agent') ?: $request->userAgent();

        $result = $this->urlShortener->shortenPublic(
            $request->original_url,
            null,
            $userAgent,
            $request->ip(),
            null,
            null,
            ShortLink::SOURCE_WEB,
        );

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
