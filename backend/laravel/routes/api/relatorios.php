<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| Relatórios Routes
|--------------------------------------------------------------------------
|
| Rotas para geração de relatórios e estatísticas do sistema.
| Todas as rotas são protegidas por autenticação.
|
*/

Route::middleware('auth:sanctum')->prefix('relatorios')->group(function () {
    // Relatórios gerais por módulo
    Route::get('/produtores-rurais', [ReportController::class, 'produtoresRurais'])->name('relatorios.produtores.rurais');
    Route::get('/propriedades-rurais', [ReportController::class, 'propriedadesRurais'])->name('relatorios.propriedades.rurais');
    Route::get('/unidades-producao', [ReportController::class, 'unidadesProducao'])->name('relatorios.unidades.producao');
    Route::get('/rebanhos', [ReportController::class, 'rebanhos'])->name('relatorios.rebanhos');

    // Relatórios específicos
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('relatorios.dashboard');
    Route::get('/propriedades-municipio', [ReportController::class, 'propriedadesPorMunicipio'])->name('relatorios.propriedades.municipio');
    Route::get('/animais-especie', [ReportController::class, 'animaisPorEspecie'])->name('relatorios.animais.especie');
    Route::get('/hectares-cultura', [ReportController::class, 'hectaresPorCultura'])->name('relatorios.hectares.cultura');
    Route::get('/rebanhos-produtor', [ReportController::class, 'rebanhosPorProdutor'])->name('relatorios.rebanhos.produtor');
    Route::get('/opcoes', [ReportController::class, 'opcoes'])->name('relatorios.opcoes');
    Route::get('/propriedades', [ReportController::class, 'buscarPropriedades'])->name('relatorios.propriedades');
    Route::post('/filtro-avancado', [ReportController::class, 'filtroAvancado'])->name('relatorios.filtro.avancado');

    // Rotas de exportação
    Route::get('/exportar/produtores-rurais', [ReportController::class, 'exportarProdutoresRurais'])->name('relatorios.exportar.produtores.rurais');
    Route::get('/exportar/propriedades-rurais', [ReportController::class, 'exportarPropriedadesRurais'])->name('relatorios.exportar.propriedades.rurais');
    Route::get('/exportar/unidades-producao', [ReportController::class, 'exportarUnidadesProducao'])->name('relatorios.exportar.unidades.producao');
    Route::get('/exportar/rebanhos', [ReportController::class, 'exportarRebanhos'])->name('relatorios.exportar.rebanhos');
    Route::get('/exportar/propriedades-municipio', [ReportController::class, 'exportarPropriedadesPorMunicipio'])->name('relatorios.exportar.propriedades.municipio');
    Route::get('/exportar/animais-especie', [ReportController::class, 'exportarAnimaisPorEspecie'])->name('relatorios.exportar.animais.especie');
    Route::get('/exportar/hectares-cultura', [ReportController::class, 'exportarHectaresPorCultura'])->name('relatorios.exportar.hectares.cultura');
    Route::get('/exportar/rebanhos-produtor', [ReportController::class, 'exportarRebanhosPorProdutor'])->name('relatorios.exportar.rebanhos.produtor');
});
