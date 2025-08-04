<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::controller(LandingController::class)->group(function () {
    Route::get('/', 'index')->name('landing');
    Route::post('/redeem', 'redeem')->name('redeem')->middleware('throttle:redeem');
    Route::get('/result/{code}', 'result')->name('result');
});

// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
