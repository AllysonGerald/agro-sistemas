<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    // Rota principal do dashboard (retorna dados completos)
    Route::get('/', [DashboardController::class, 'index']);

    // Rotas específicas para cada seção
    Route::get('estatisticas', [DashboardController::class, 'estatisticas']);
    Route::get('graficos', [DashboardController::class, 'graficos']);
    Route::get('atividades', [DashboardController::class, 'atividades']);
    Route::get('metricas', [DashboardController::class, 'metricas']);

    // Backward compatibility (mantém a rota antiga)
    Route::get('resumo', [DashboardController::class, 'index']);
});
