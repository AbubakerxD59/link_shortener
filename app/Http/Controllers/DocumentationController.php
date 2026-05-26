<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentationController extends Controller
{
    public function index(): View
    {
        return view('docs.api', [
            'appUrl' => rtrim(config('app.url'), '/'),
        ]);
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
