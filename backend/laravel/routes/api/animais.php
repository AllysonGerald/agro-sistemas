<?php

use App\Http\Controllers\Api\AnimalController;
use Illuminate\Support\Facades\Route;

// Rotas de animais (gestão individual)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('animais', AnimalController::class);
    
    // Rotas adicionais específicas
    Route::get('animais/{animal}/historico', [AnimalController::class, 'historico']);
    Route::get('animais/{animal}/manejos', [AnimalController::class, 'manejos']);
    Route::post('animais/{animal}/upload-foto', [AnimalController::class, 'uploadFoto']);
    Route::get('animais/estatisticas/geral', [AnimalController::class, 'estatisticas']);
});

