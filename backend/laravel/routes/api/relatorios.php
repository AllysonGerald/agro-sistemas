<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RelatorioController;

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
    Route::get('/produtores-rurais', [RelatorioController::class, 'produtoresRurais'])->name('relatorios.produtores.rurais');
    Route::get('/propriedades-rurais', [RelatorioController::class, 'propriedadesRurais'])->name('relatorios.propriedades.rurais');
    Route::get('/unidades-producao', [RelatorioController::class, 'unidadesProducao'])->name('relatorios.unidades.producao');
    Route::get('/rebanhos', [RelatorioController::class, 'rebanhos'])->name('relatorios.rebanhos');

    // Relatórios específicos
    Route::get('/dashboard', [RelatorioController::class, 'dashboard'])->name('relatorios.dashboard');
    Route::get('/propriedades-municipio', [RelatorioController::class, 'propriedadesPorMunicipio'])->name('relatorios.propriedades.municipio');
    Route::get('/animais-especie', [RelatorioController::class, 'animaisPorEspecie'])->name('relatorios.animais.especie');
    Route::get('/hectares-cultura', [RelatorioController::class, 'hectaresPorCultura'])->name('relatorios.hectares.cultura');
    Route::get('/rebanhos-produtor', [RelatorioController::class, 'rebanhosPorProdutor'])->name('relatorios.rebanhos.produtor');
    Route::get('/opcoes', [RelatorioController::class, 'opcoes'])->name('relatorios.opcoes');
    Route::get('/propriedades', [RelatorioController::class, 'buscarPropriedades'])->name('relatorios.propriedades');
    Route::post('/filtro-avancado', [RelatorioController::class, 'filtroAvancado'])->name('relatorios.filtro.avancado');

    // Rotas de exportação
    Route::get('/exportar/produtores-rurais', [RelatorioController::class, 'exportarProdutoresRurais'])->name('relatorios.exportar.produtores.rurais');
    Route::get('/exportar/propriedades-rurais', [RelatorioController::class, 'exportarPropriedadesRurais'])->name('relatorios.exportar.propriedades.rurais');
    Route::get('/exportar/unidades-producao', [RelatorioController::class, 'exportarUnidadesProducao'])->name('relatorios.exportar.unidades.producao');
    Route::get('/exportar/rebanhos', [RelatorioController::class, 'exportarRebanhos'])->name('relatorios.exportar.rebanhos');
    Route::get('/exportar/propriedades-municipio', [RelatorioController::class, 'exportarPropriedadesPorMunicipio'])->name('relatorios.exportar.propriedades.municipio');
    Route::get('/exportar/animais-especie', [RelatorioController::class, 'exportarAnimaisPorEspecie'])->name('relatorios.exportar.animais.especie');
    Route::get('/exportar/hectares-cultura', [RelatorioController::class, 'exportarHectaresPorCultura'])->name('relatorios.exportar.hectares.cultura');
    Route::get('/exportar/rebanhos-produtor', [RelatorioController::class, 'exportarRebanhosPorProdutor'])->name('relatorios.exportar.rebanhos.produtor');
});
