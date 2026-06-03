<?php

use App\Http\Controllers\Api\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::post('/links', [ShortLinkController::class, 'store'])->name('api.links.store');
Route::get('/links/{code}', [ShortLinkController::class, 'show'])
    ->name('api.links.show')
    ->where('code', '[a-zA-Z0-9]+');
Route::match(['put', 'patch'], '/links/{code}', [ShortLinkController::class, 'update'])
    ->name('api.links.update')
    ->where('code', '[a-zA-Z0-9]+');
