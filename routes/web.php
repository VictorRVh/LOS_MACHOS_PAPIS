<?php


use App\Http\Controllers\EstudianteDocumentoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;


Route::get('/google/redirect', [GoogleDriveController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [GoogleDriveController::class, 'handleGoogleCallback'])->name('google.callback');

Route::get(
    '/verificar-certificado/{codigo}',
    [EstudianteDocumentoController::class, 'verificarCertificado']
);

Route::get(
    '/{any}',
    fn () => view('app')
)->where('any', '.*');