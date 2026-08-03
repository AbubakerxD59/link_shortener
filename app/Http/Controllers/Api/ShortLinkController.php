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
     * Params: original_url (required), url_cloak (0|1), user_id, user_agent, ip_address, page_title, thumbnail_url, source (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'original_url' => 'required|url|max:2048',
            'url_cloak' => 'nullable|integer|in:0,1',
            'user_id' => 'nullable|integer|min:1',
            'user_agent' => 'nullable|string|max:65535',
            'ip_address' => 'nullable|ip',
            'page_title' => 'nullable|string|max:500',
            'thumbnail_url' => 'nullable|url|max:2048',
            'source' => 'nullable|string|max:64',
            'cloak' => 'sometimes|boolean',
            'custom_domain_id' => 'nullable|integer|min:1',
        ]);

        $userAgent = $validated['user_agent'] ?? $request->userAgent();
        $ipAddress = $validated['ip_address'] ?? $request->ip();
        $cloaked = array_key_exists('url_cloak', $validated)
            ? ShortLink::cloakedFromUrlCloak($validated['url_cloak'])
            : ShortLink::cloakedFromUrlCloak($request->input('cloak'), true);

        $customDomainId = null;
        if (! empty($validated['custom_domain_id']) && ! empty($validated['user_id'])) {
            $customDomain = app(\App\Services\CustomDomainService::class)
                ->resolveVerifiedDomainForEngagyo(
                    (int) $validated['user_id'],
                    (int) $validated['custom_domain_id']
                );

            if (! $customDomain) {
                // Also allow ShrtLnk-native ownership (user_id + no engagyo_user_id).
                $customDomain = app(\App\Services\CustomDomainService::class)
                    ->resolveVerifiedDomainForUserId(
                        (int) $validated['user_id'],
                        (int) $validated['custom_domain_id']
                    );
            }

            if (! $customDomain) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select a valid verified branded domain.',
                ], 422);
            }

            $customDomainId = $customDomain->id;
        }

        $result = $this->urlShortener->shortenPublic(
            $validated['original_url'],
            $validated['user_id'] ?? null,
            $userAgent,
            $ipAddress,
            $validated['page_title'] ?? null,
            $validated['thumbnail_url'] ?? null,
            $validated['source'] ?? ShortLink::SOURCE_API,
            $cloaked,
            $customDomainId,
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

    /**
     * Update a short link (partial).
     *
     * Params: original_url, url_cloak (0|1), page_title, thumbnail_url, source, user_id (all optional)
     */
    public function update(Request $request, string $code): JsonResponse
    {
        $shortLink = ShortLink::where('short_code', $code)->first();

        if (! $shortLink) {
            return response()->json([
                'success' => false,
                'message' => 'Short link not found.',
            ], 404);
        }

        $validated = $request->validate([
            'original_url' => 'sometimes|url|max:2048',
            'url_cloak' => 'sometimes|integer|in:0,1',
            'user_id' => 'nullable|integer|min:1',
            'page_title' => 'nullable|string|max:500',
            'thumbnail_url' => 'nullable|url|max:2048',
            'source' => 'nullable|string|max:64',
            'cloak' => 'sometimes|boolean',
        ]);

        $payload = $this->buildUpdatePayload($request, $validated);

        if ($payload === []) {
            return response()->json([
                'success' => false,
                'message' => 'No fields to update.',
            ], 422);
        }

        $result = $this->urlShortener->updateShortLink($shortLink, $payload);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function buildUpdatePayload(Request $request, array $validated): array
    {
        $payload = [];

        foreach (['original_url', 'page_title', 'thumbnail_url', 'source', 'user_id'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }

        if (array_key_exists('url_cloak', $validated)) {
            $payload['url_cloak'] = $validated['url_cloak'];
        } elseif ($request->has('cloak')) {
            $payload['url_cloak'] = $request->boolean('cloak') ? 1 : 0;
        }

        return $payload;
    }
}
