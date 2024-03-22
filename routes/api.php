<?php

use App\Http\Controllers\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/currencies', [CurrencyController::class, 'list'])->name('currencies');
    Route::get('/currencies/convert', [CurrencyController::class, 'convert'])->name('currencies.convert');
});
