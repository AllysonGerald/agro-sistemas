<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UnidadeProducaoController;

/*
|--------------------------------------------------------------------------
| Unidades de Produção Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento de unidades de produção.
| Todas as rotas são protegidas por autenticação.
|
*/

Route::middleware('auth:sanctum')->prefix('unidades-producao')->group(function () {
    Route::get('/', [UnidadeProducaoController::class, 'index'])->name('unidades.index');
    Route::post('/', [UnidadeProducaoController::class, 'store'])->name('unidades.store');
    Route::get('/relatorio-area-cultura', [UnidadeProducaoController::class, 'relatorioAreaPorCultura'])->name('unidades.relatorio.area.cultura');
    Route::get('/estatisticas-culturas', [UnidadeProducaoController::class, 'estatisticasCulturas'])->name('unidades.estatisticas.culturas');
    Route::get('/propriedade/{propriedadeId}', [UnidadeProducaoController::class, 'porPropriedade'])->name('unidades.propriedade');
    Route::get('/{id}', [UnidadeProducaoController::class, 'show'])->name('unidades.show');
    Route::put('/{id}', [UnidadeProducaoController::class, 'update'])->name('unidades.update');
    Route::delete('/{id}', [UnidadeProducaoController::class, 'destroy'])->name('unidades.destroy');
});
