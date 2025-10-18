<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RebanhoController;

/*
|--------------------------------------------------------------------------
| Rebanhos Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento de rebanhos.
| Todas as rotas são protegidas por autenticação.
|
*/

Route::middleware('auth:sanctum')->prefix('rebanhos')->group(function () {
    Route::get('/', [RebanhoController::class, 'index'])->name('rebanhos.index');
    Route::post('/', [RebanhoController::class, 'store'])->name('rebanhos.store');
    Route::get('/relatorio-especies', [RebanhoController::class, 'relatorioEspecies'])->name('rebanhos.relatorio.especies');
    Route::get('/estatisticas-especies', [RebanhoController::class, 'estatisticasEspecies'])->name('rebanhos.estatisticas.especies');
    Route::get('/propriedade/{propriedadeId}', [RebanhoController::class, 'porPropriedade'])->name('rebanhos.propriedade');
    Route::get('/produtor/{produtorId}', [RebanhoController::class, 'porProdutor'])->name('rebanhos.produtor');
    Route::get('/produtor/{produtorId}/exportar-pdf', [RebanhoController::class, 'exportarPorProdutor'])->name('rebanhos.exportar.pdf');
    Route::get('/desatualizados', [RebanhoController::class, 'desatualizados'])->name('rebanhos.desatualizados');
    Route::get('/{id}', [RebanhoController::class, 'show'])->name('rebanhos.show');
    Route::put('/{id}', [RebanhoController::class, 'update'])->name('rebanhos.update');
    Route::delete('/{id}', [RebanhoController::class, 'destroy'])->name('rebanhos.destroy');
});
