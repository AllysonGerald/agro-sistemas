<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/estatisticas', [DashboardController::class, 'estatisticas']);
    Route::get('/grafico-evolucao', [DashboardController::class, 'graficoEvolucao']);
    Route::get('/grafico-financeiro', [DashboardController::class, 'graficoFinanceiro']);
    Route::get('/distribuicoes', [DashboardController::class, 'distribuicoes']);
    Route::get('/atividades-recentes', [DashboardController::class, 'atividadesRecentes']);
});
