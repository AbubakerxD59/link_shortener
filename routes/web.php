<?php

use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShortenController;
use App\Http\Controllers\ShortLinkRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/api-docs', [DocumentationController::class, 'index'])->name('docs');
Route::get('/api-docs/openapi.yaml', [DocumentationController::class, 'openapi'])->name('docs.openapi');

Route::post('/shorten', [ShortenController::class, 'store'])->name('shorten');

Route::get('/s/{code}', ShortLinkRedirectController::class)
    ->name('short.redirect')
    ->where('code', '[a-zA-Z0-9]+');
