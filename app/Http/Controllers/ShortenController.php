<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use App\Services\CustomDomainService;
use App\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortenController extends Controller
{
    public function __construct(
        protected UrlShortenerService $urlShortener,
        protected CustomDomainService $customDomains,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'original_url' => 'required|url|max:2048',
            'cloak' => 'sometimes|boolean',
            'custom_domain_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $customDomainId = null;

        if ($user && $request->filled('custom_domain_id')) {
            $customDomain = $this->customDomains->resolveVerifiedDomainForUser(
                $user,
                (int) $request->input('custom_domain_id')
            );

            if (! $customDomain) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select a valid verified branded domain.',
                ], 422);
            }

            $customDomainId = $customDomain->id;
        }

        $userAgent = $request->input('user_agent') ?: $request->userAgent();

        $result = $this->urlShortener->shortenPublic(
            $request->original_url,
            $user?->id,
            $userAgent,
            $request->ip(),
            null,
            null,
            ShortLink::SOURCE_WEB,
            $request->boolean('cloak', true),
            $customDomainId,
        );

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
