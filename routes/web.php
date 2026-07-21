<?php

use App\Http\Controllers\Admin\CustomDomainController as AdminCustomDomainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomDomainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShortenController;
use App\Http\Controllers\ShortLinkRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::delete('/links/{shortLink}', [DashboardController::class, 'destroy'])->name('links.destroy');

    Route::get('/branded-domains', [CustomDomainController::class, 'index'])->name('branded-domains.index');
    Route::post('/branded-domains', [CustomDomainController::class, 'store'])->name('branded-domains.store');
    Route::get('/branded-domains/{customDomain}', [CustomDomainController::class, 'show'])->name('branded-domains.show');
    Route::post('/branded-domains/{customDomain}/verify', [CustomDomainController::class, 'verify'])->name('branded-domains.verify');
    Route::post('/branded-domains/{customDomain}/default', [CustomDomainController::class, 'makeDefault'])->name('branded-domains.default');
    Route::delete('/branded-domains/{customDomain}', [CustomDomainController::class, 'destroy'])->name('branded-domains.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/branded-domains', [AdminCustomDomainController::class, 'index'])->name('branded-domains.index');
        Route::post('/branded-domains/{customDomain}/activate', [AdminCustomDomainController::class, 'activate'])->name('branded-domains.activate');
    });
});

Route::get('/api-docs', [DocumentationController::class, 'index'])->name('docs');
Route::get('/api-docs/openapi.yaml', [DocumentationController::class, 'openapi'])->name('docs.openapi');

Route::post('/shorten', [ShortenController::class, 'store'])->name('shorten');

Route::get('/{code}', ShortLinkRedirectController::class)
    ->name('short.redirect')
    ->where('code', '[a-zA-Z0-9]+');
