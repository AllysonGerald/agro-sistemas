<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropriedadeController;

/*
|--------------------------------------------------------------------------
| Propriedades Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento de propriedades rurais.
| Todas as rotas são protegidas por autenticação.
|
*/

Route::middleware('auth:sanctum')->prefix('propriedades')->group(function () {
    Route::get('/', [PropriedadeController::class, 'index'])->name('propriedades.index');
    Route::post('/', [PropriedadeController::class, 'store'])->name('propriedades.store');
    Route::get('/relatorio-municipios', [PropriedadeController::class, 'relatorioMunicipios'])->name('propriedades.relatorio.municipios');
    Route::get('/exportar-excel', [PropriedadeController::class, 'exportarExcel'])->name('propriedades.exportar.excel');
    Route::get('/validar-area', [PropriedadeController::class, 'validarArea'])->name('propriedades.validar.area');
    Route::get('/{id}', [PropriedadeController::class, 'show'])->name('propriedades.show');
    Route::put('/{id}', [PropriedadeController::class, 'update'])->name('propriedades.update');
    Route::delete('/{id}', [PropriedadeController::class, 'destroy'])->name('propriedades.destroy');
});
