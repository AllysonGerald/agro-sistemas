<?php

use App\Http\Controllers\Api\LoteController;
use Illuminate\Support\Facades\Route;

// Rotas de lotes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('lotes', LoteController::class);
    
    // Rotas adicionais
    Route::get('lotes/{lote}/animais', [LoteController::class, 'animais']);
    Route::post('lotes/{lote}/adicionar-animal', [LoteController::class, 'adicionarAnimal']);
    Route::post('lotes/{lote}/remover-animal', [LoteController::class, 'removerAnimal']);
    Route::get('lotes/{lote}/relatorio', [LoteController::class, 'relatorio']);
});

