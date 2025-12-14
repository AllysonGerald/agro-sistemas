<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    echo "API is running";
});

// Rota dummy de login (não será usada pois o sistema é SPA)
Route::get('/login', function () {
    return response()->json([
        'message' => 'Esta é uma API. Use /api/v1/auth/login para autenticação'
    ], 401);
})->name('login');

Route::get('/teste', function () {
    // return view('welcome');
    return view('emails.password-reset');
});
