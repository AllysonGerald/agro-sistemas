<?php

use App\Http\Controllers\Api\EstoqueController;
use Illuminate\Support\Facades\Route;

// Rotas de estoque
Route::middleware(['auth:sanctum'])->group(function () {
    // Rotas específicas DEVEM vir ANTES do apiResource
    Route::get('estoque/baixo-estoque', [EstoqueController::class, 'baixoEstoque']);
    Route::get('estoque/vencidos', [EstoqueController::class, 'vencidos']);
    Route::get('estoque/por-vencer', [EstoqueController::class, 'porVencer']);
    Route::post('estoque/{id}/entrada', [EstoqueController::class, 'registrarEntrada']);
    Route::post('estoque/{id}/saida', [EstoqueController::class, 'registrarSaida']);
    
    // Resource routes
    Route::apiResource('estoque', EstoqueController::class);
});

