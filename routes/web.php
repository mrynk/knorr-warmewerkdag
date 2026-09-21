<?php

declare(strict_types=1);

use App\Http\Controllers\LandingController;
use App\Http\Controllers\RewardConfirmationPreviewController;
use Illuminate\Support\Facades\Route;

Route::controller(LandingController::class)->group(function (): void {
    Route::get('/', 'index')->name('landing');
    Route::post('/redeem', 'store')->name('redeem')->middleware('throttle:redeem');
    Route::get('/result/{code}', 'show')->name('result');
});

Route::get('/email/{code}', RewardConfirmationPreviewController::class)->name('email.preview');
