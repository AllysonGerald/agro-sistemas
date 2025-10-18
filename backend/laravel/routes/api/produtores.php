<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdutorRuralController;

/*
|--------------------------------------------------------------------------
| Produtores Rurais Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento de produtores rurais.
| Todas as rotas são protegidas por autenticação.
|
*/

Route::middleware('auth:sanctum')->prefix('produtores-rurais')->group(function () {
    Route::get('/', [ProdutorRuralController::class, 'index'])->name('produtores.index');
    Route::post('/', [ProdutorRuralController::class, 'store'])->name('produtores.store');
    Route::get('/buscar', [ProdutorRuralController::class, 'search'])->name('produtores.search');
    Route::get('/{id}', [ProdutorRuralController::class, 'show'])->name('produtores.show');
    Route::put('/{id}', [ProdutorRuralController::class, 'update'])->name('produtores.update');
    Route::delete('/{id}', [ProdutorRuralController::class, 'destroy'])->name('produtores.destroy');
    Route::get('/{id}/propriedades', [ProdutorRuralController::class, 'propriedades'])->name('produtores.propriedades');
});
