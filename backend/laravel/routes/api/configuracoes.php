<?php

use App\Http\Controllers\Api\ConfiguracaoController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil do Usuário
    Route::prefix('perfil')->group(function () {
        Route::get('/', [PerfilController::class, 'show']);
        Route::put('/', [PerfilController::class, 'update']);
        Route::post('/alterar-senha', [PerfilController::class, 'alterarSenha']);
    });

    // Configurações
    Route::prefix('configuracoes')->group(function () {
        Route::get('/', [ConfiguracaoController::class, 'index']);
        Route::put('/sistema', [ConfiguracaoController::class, 'updateSistema']);
        Route::put('/notificacoes', [ConfiguracaoController::class, 'updateNotificacoes']);
    });

    // Empresa
    Route::prefix('empresa')->group(function () {
        Route::get('/', [EmpresaController::class, 'show']);
        Route::post('/', [EmpresaController::class, 'store']);
    });
});

