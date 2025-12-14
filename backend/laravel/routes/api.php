<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas organizadas por módulos dentro do prefixo v1
Route::prefix('v1')->group(function () {

    // Rotas de autenticação
    require __DIR__.'/api/auth.php';

    // Rotas de dashboard
    require __DIR__.'/api/dashboard.php';

    // Rotas de produtores rurais
    require __DIR__.'/api/produtores.php';

    // Rotas de propriedades
    require __DIR__.'/api/propriedades.php';

    // Rotas de unidades de produção
    require __DIR__.'/api/unidades.php';

    // Rotas de rebanhos
    require __DIR__.'/api/rebanhos.php';

    // Rotas de relatórios
    require __DIR__.'/api/relatorios.php';

    // Rotas de gerenciamento de cache
    require __DIR__.'/api/cache.php';

    // Rotas de testes (podem ser removidas em produção)
    require __DIR__.'/api/testes.php';

    // === NOVOS MÓDULOS ===

    // Rotas de animais (gestão individual)
    require __DIR__.'/api/animais.php';

    // Rotas de lotes
    require __DIR__.'/api/lotes.php';

    // Rotas de pastos
    require __DIR__.'/api/pastos.php';

    // Rotas do módulo financeiro
    require __DIR__.'/api/financeiro.php';

    // Rotas de manejo
    require __DIR__.'/api/manejo.php';

    // Rotas de reprodução
    require __DIR__.'/api/reproducao.php';

    // Rotas de estoque
    require __DIR__.'/api/estoque.php';

    // Rotas de configurações do sistema
    require __DIR__.'/api/configuracoes.php';

});
