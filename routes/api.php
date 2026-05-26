<?php

use App\Http\Controllers\Api\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::post('/links', [ShortLinkController::class, 'store'])->name('api.links.store');
