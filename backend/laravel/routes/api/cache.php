<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CacheController;

/*
|--------------------------------------------------------------------------
| Cache Management Routes
|--------------------------------------------------------------------------
|
| Rotas para gerenciamento do sistema de cache.
| Todas as rotas são protegidas por autenticação.
|
*/

Route::middleware('auth:sanctum')->prefix('cache')->group(function () {
    // Estatísticas do cache
    Route::get('/stats', [CacheController::class, 'stats'])->name('cache.stats');

    // Configurações do cache
    Route::get('/config', [CacheController::class, 'config'])->name('cache.config');

    // Verificar se chave existe
    Route::post('/has', [CacheController::class, 'has'])->name('cache.has');

    // Remover cache específico
    Route::delete('/forget', [CacheController::class, 'forget'])->name('cache.forget');

    // Remover cache por módulo
    Route::delete('/module', [CacheController::class, 'forgetModule'])->name('cache.forget.module');

    // Limpar todo o cache
    Route::delete('/flush', [CacheController::class, 'flush'])->name('cache.flush');

    // Renovar TTL
    Route::put('/refresh', [CacheController::class, 'refresh'])->name('cache.refresh');

    // Aquecer cache
    Route::post('/warmup', [CacheController::class, 'warmup'])->name('cache.warmup');
});
