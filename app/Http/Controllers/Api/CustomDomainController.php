<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Services\CustomDomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomDomainController extends Controller
{
    public function __construct(protected CustomDomainService $customDomains) {}

    /**
     * Create a pending branded domain for an Engagyo user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'engagyo_user_id' => 'required|integer|min:1',
            'base_domain' => 'required|string|max:253',
            'domain_type' => 'required|in:apex,subdomain',
            'subdomain_prefix' => 'nullable|required_if:domain_type,subdomain|string|max:63',
        ]);

        try {
            $customDomain = $this->customDomains->assignDomainForEngagyo(
                (int) $validated['engagyo_user_id'],
                $validated['base_domain'],
                $validated['domain_type'],
                $validated['subdomain_prefix'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            // Idempotent: return the existing Engagyo-owned domain so Engagyo can re-link.
            $existing = $this->findExistingEngagyoDomain(
                (int) $validated['engagyo_user_id'],
                $validated['base_domain'],
                $validated['domain_type'],
                $validated['subdomain_prefix'] ?? null
            );

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'id' => $existing->id,
                    'domain' => $existing->domain,
                    'domain_type' => $existing->domain_type,
                    'base_domain' => $existing->base_domain,
                    'subdomain_prefix' => $existing->subdomain_prefix,
                    'verified_at' => $existing->verified_at,
                    'is_default' => $existing->is_default,
                    'existing' => true,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'id' => $customDomain->id,
            'domain' => $customDomain->domain,
            'domain_type' => $customDomain->domain_type,
            'base_domain' => $customDomain->base_domain,
            'subdomain_prefix' => $customDomain->subdomain_prefix,
            'verified_at' => $customDomain->verified_at,
            'is_default' => $customDomain->is_default,
        ], 201);
    }

    protected function findExistingEngagyoDomain(
        int $engagyoUserId,
        string $baseDomain,
        string $domainType,
        ?string $subdomainPrefix
    ): ?CustomDomain {
        $baseDomain = CustomDomain::normalizeHost($baseDomain);
        $domain = $domainType === CustomDomain::TYPE_APEX
            ? $baseDomain
            : strtolower(trim((string) $subdomainPrefix)).'.'.$baseDomain;

        return CustomDomain::query()
            ->where('domain', CustomDomain::normalizeHost($domain))
            ->where('engagyo_user_id', $engagyoUserId)
            ->first();
    }

    /**
     * Mark an Engagyo-synced branded domain active after Hostinger parking.
     */
    public function activate(CustomDomain $customDomain): JsonResponse
    {
        if ($customDomain->engagyo_user_id === null) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is not managed via Engagyo.',
            ], 422);
        }

        if ($customDomain->isVerified()) {
            return response()->json([
                'success' => true,
                'message' => 'Domain is already active.',
                'id' => $customDomain->id,
                'domain' => $customDomain->domain,
                'verified_at' => $customDomain->verified_at?->toIso8601String(),
            ]);
        }

        $this->customDomains->activate($customDomain);

        return response()->json([
            'success' => true,
            'message' => 'Domain marked active.',
            'id' => $customDomain->id,
            'domain' => $customDomain->domain,
            'verified_at' => $customDomain->fresh()->verified_at?->toIso8601String(),
        ]);
    }

    /**
     * Remove an Engagyo-synced branded domain.
     */
    public function destroy(CustomDomain $customDomain): JsonResponse
    {
        if ($customDomain->engagyo_user_id === null) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is not managed via Engagyo.',
            ], 422);
        }

        $domainName = $customDomain->domain;
        $this->customDomains->removeDomain($customDomain);

        return response()->json([
            'success' => true,
            'message' => 'Branded domain '.$domainName.' removed.',
        ]);
    }
}
