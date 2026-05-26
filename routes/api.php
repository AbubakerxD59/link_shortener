<?php

use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/docs', [DocumentationController::class, 'markdown'])->name('api.docs.redirect');
Route::get('/docs/openapi.yaml', [DocumentationController::class, 'openapi'])->name('api.docs.openapi');

Route::post('/links', [ShortLinkController::class, 'store'])->name('api.links.store');
