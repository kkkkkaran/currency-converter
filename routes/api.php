<?php

use App\Http\Controllers\CurrenciesController;
use App\Http\Controllers\CurrencyReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, '__invoke'])->name('user');
    Route::get('/currencies', [CurrenciesController::class, 'list'])->name('currencies');
    Route::get('/currencies/convert', [CurrenciesController::class, 'convert'])->name('currencies.convert');
    Route::get('/currencies/reports', [CurrencyReportController::class, 'index'])->name('currencies.reports.list');
    Route::post('/currencies/reports', [CurrencyReportController::class, 'store'])->name('currencies.reports.store');
});
