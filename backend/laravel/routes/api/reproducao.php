<?php

use App\Http\Controllers\Api\ReproducaoController;
use Illuminate\Support\Facades\Route;

// Rotas de reprodução
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('reproducoes', ReproducaoController::class);
    
    // Rotas adicionais
    Route::get('reproducoes/prenhas', [ReproducaoController::class, 'prenhas']);
    Route::get('reproducoes/partos-proximos', [ReproducaoController::class, 'partosProximos']);
    Route::get('reproducoes/estatisticas', [ReproducaoController::class, 'estatisticas']);
    Route::post('reproducoes/{reproducao}/registrar-parto', [ReproducaoController::class, 'registrarParto']);
});

