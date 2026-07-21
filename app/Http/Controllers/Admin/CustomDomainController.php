<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Services\CustomDomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomDomainController extends Controller
{
    public function __construct(protected CustomDomainService $customDomains) {}

    public function index(): View
    {
        $pendingDomains = CustomDomain::query()
            ->with('user:id,name,email')
            ->whereNull('verified_at')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.branded-domains.index', [
            'pendingDomains' => $pendingDomains,
            'cnameTarget' => $this->customDomains->cnameTarget(),
        ]);
    }

    public function activate(Request $request, CustomDomain $customDomain): RedirectResponse
    {
        if ($customDomain->isVerified()) {
            return redirect()
                ->route('admin.branded-domains.index')
                ->with('domain_status', $customDomain->domain.' is already active.');
        }

        $this->customDomains->activate($customDomain);

        return redirect()
            ->route('admin.branded-domains.index')
            ->with('domain_status', $customDomain->domain.' has been marked active.');
    }
}
