<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Rotas para autenticação de usuários: registro, login, logout e perfil.
| Algumas rotas são públicas (register/login) e outras protegidas.
|
*/

Route::prefix('auth')->group(function () {
    // Rotas públicas de autenticação
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Rotas de recuperação de senha (públicas)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot.password');
    Route::post('/validate-reset-token', [AuthController::class, 'validateResetToken'])->name('auth.validate.reset.token');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset.password');

    // Rotas protegidas de autenticação usando middleware personalizado
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('/revoke-all', [AuthController::class, 'revokeAll'])->name('auth.revoke.all');
    });
});
