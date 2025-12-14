<?php

use App\Http\Controllers\Api\PastoController;
use Illuminate\Support\Facades\Route;

// Rotas de pastos
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('pastos', PastoController::class);
    
    // Rotas adicionais
    Route::get('pastos/disponiveis', [PastoController::class, 'disponiveis']);
    Route::post('pastos/{pasto}/iniciar-descanso', [PastoController::class, 'iniciarDescanso']);
    Route::post('pastos/{pasto}/liberar', [PastoController::class, 'liberar']);
});

