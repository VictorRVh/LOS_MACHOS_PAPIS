<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;


Route::get('/google/redirect', [GoogleDriveController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleDriveController::class, 'handleGoogleCallback'])->name('google.callback');


Route::get(
    '/{any}',
    fn () => view('app')
)->where('any', '.*');