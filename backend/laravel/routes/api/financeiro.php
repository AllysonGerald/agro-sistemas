<?php

use App\Http\Controllers\Api\CategoriaFinanceiraController;
use App\Http\Controllers\Api\TransacaoFinanceiraController;
use Illuminate\Support\Facades\Route;

// Rotas do módulo financeiro
Route::middleware(['auth:sanctum'])->group(function () {
    // Categorias Financeiras
    Route::apiResource('categorias-financeiras', CategoriaFinanceiraController::class);
    
    // Rotas específicas de transações DEVEM vir ANTES do apiResource
    Route::get('transacoes/estatisticas/periodo', [TransacaoFinanceiraController::class, 'estatisticasPeriodo']);
    Route::get('transacoes/dashboard', [TransacaoFinanceiraController::class, 'dashboard']);
    Route::get('transacoes/relatorio/pdf', [TransacaoFinanceiraController::class, 'gerarPDF']);
    Route::get('transacoes/relatorio/excel', [TransacaoFinanceiraController::class, 'gerarExcel']);
    Route::get('transacoes/relatorio/csv', [TransacaoFinanceiraController::class, 'gerarCSV']);
    
    // Transações Financeiras
    Route::apiResource('transacoes', TransacaoFinanceiraController::class);
});

