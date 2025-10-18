<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
|
| Rotas para testes de funcionamento da API e conexão com banco.
| Podem ser removidas em produção.
|
*/

Route::prefix('teste')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'OK',
            'message' => 'API Agropecuária funcionando',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    })->name('api.health');

    Route::get('/database', function () {
        try {
            DB::connection()->getPdo();
            return response()->json([
                'status' => 'OK',
                'message' => 'Conexão com banco de dados funcionando',
                'database' => config('database.default')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Erro na conexão com banco de dados',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('api.database');
});
