<?php

namespace App\Http\Controllers;

use App\Models\CustomDomain;
use App\Services\CustomDomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomDomainController extends Controller
{
    public function __construct(protected CustomDomainService $customDomains) {}

    public function index(Request $request): View
    {
        $domains = $request->user()
            ->customDomains()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('branded-domains.index', [
            'domains' => $domains,
        ]);
    }

    public function show(Request $request, CustomDomain $customDomain): View
    {
        $this->authorizeDomain($request, $customDomain);

        return view('branded-domains.show', [
            'customDomain' => $customDomain,
            'domainSetup' => $this->customDomains->setupInstructions($customDomain),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'base_domain' => 'required|string|max:253',
            'domain_type' => 'required|in:apex,subdomain',
            'subdomain_prefix' => 'nullable|required_if:domain_type,subdomain|string|max:63',
        ]);

        try {
            $customDomain = $this->customDomains->assignDomain(
                $request->user(),
                $validated['base_domain'],
                $validated['domain_type'],
                $validated['subdomain_prefix'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['domain' => $e->getMessage()]);
        }

        $message = 'Domain submitted. It will take up to 24 hours for our team to review and activate it.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'domain' => $this->customDomains->setupInstructions($customDomain),
            ], 201);
        }

        return redirect()
            ->route('branded-domains.show', $customDomain)
            ->with('toast', [
                'message' => $message,
                'type' => 'success',
                'duration' => 6000,
            ])
            ->with('domain_status', $message);
    }

    public function verify(Request $request, CustomDomain $customDomain): JsonResponse|RedirectResponse
    {
        $this->authorizeDomain($request, $customDomain);

        $result = $this->customDomains->verify($customDomain);
        $customDomain->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['verified'],
                'message' => $result['message'],
                'checks' => [
                    'dns_ok' => $result['dns_ok'],
                    'ssl_ok' => $result['ssl_ok'],
                ],
                'domain' => $this->customDomains->setupInstructions($customDomain),
            ], $result['verified'] ? 200 : 422);
        }

        $flashKey = ! $result['dns_ok'] ? 'domain_error' : ($result['ssl_ok'] ? 'domain_status' : 'domain_warning');

        return redirect()
            ->route('branded-domains.show', $customDomain)
            ->with($flashKey, $result['message']);
    }

    public function makeDefault(Request $request, CustomDomain $customDomain): RedirectResponse
    {
        $this->authorizeDomain($request, $customDomain);

        try {
            $this->customDomains->setAsDefault($request->user(), $customDomain);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['domain' => $e->getMessage()]);
        }

        return back()->with('domain_status', $customDomain->domain.' is now the default domain for new short links.');
    }

    public function destroy(Request $request, CustomDomain $customDomain): JsonResponse|RedirectResponse
    {
        $this->authorizeDomain($request, $customDomain);

        $domainName = $customDomain->domain;
        $this->customDomains->removeDomain($customDomain);

        $message = 'Branded domain '.$domainName.' removed.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('branded-domains.index')
            ->with('domain_status', $message);
    }

    protected function authorizeDomain(Request $request, CustomDomain $customDomain): void
    {
        if ($customDomain->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
