<?php

use App\Http\Controllers\Api\ManejoController;
use Illuminate\Support\Facades\Route;

// Rotas de manejo
Route::middleware(['auth:sanctum'])->group(function () {
    // Rotas específicas DEVEM vir ANTES do apiResource
    Route::get('manejos/animal/{animal_id}', [ManejoController::class, 'porAnimal']);
    Route::get('manejos/tipo/{tipo}', [ManejoController::class, 'porTipo']);
    Route::get('manejos/agenda', [ManejoController::class, 'agenda']);
    Route::get('manejos/pendentes', [ManejoController::class, 'pendentes']);
    
    // Resource routes
    Route::apiResource('manejos', ManejoController::class);
});

