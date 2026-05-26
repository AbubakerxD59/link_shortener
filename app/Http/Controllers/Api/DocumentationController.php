<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentationController extends Controller
{
    public function index(): View
    {
        return view('docs.api', [
            'appUrl' => rtrim(config('app.url'), '/'),
        ]);
    }

    public function markdown(): RedirectResponse
    {
        return redirect()->route('docs');
    }

    public function openapi(): BinaryFileResponse
    {
        $path = base_path('docs/openapi.yaml');

        if (! is_readable($path)) {
            abort(404, 'OpenAPI specification not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/yaml',
        ]);
    }
}
