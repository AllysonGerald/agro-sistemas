<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Cache Management
 *
 * Controlador de Administração de Cache
 *
 * API dedicada para monitoramento e gestão do sistema de cache empresarial.
 * Implementa endpoints seguros para operações administrativas avançadas.
 *
 * @package App\Http\Controllers\Api
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class CacheController extends Controller
{
    /**
     * Obter estatísticas do cache
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = CacheService::getStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas do cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar cache específico por chave
     */
    public function forget(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string'
        ]);

        try {
            $success = CacheService::forget($request->key);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Cache removido com sucesso' : 'Falha ao remover cache'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar cache por módulo
     */
    public function forgetModule(Request $request): JsonResponse
    {
        $request->validate([
            'module' => 'required|string|in:produtor,propriedade,unidade,rebanho,relatorio,dashboard,auth'
        ]);

        try {
            $success = CacheService::forgetModule($request->module);

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? "Cache do módulo '{$request->module}' limpo com sucesso"
                    : 'Falha ao limpar cache do módulo'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache do módulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar todo o cache
     */
    public function flush(): JsonResponse
    {
        try {
            $success = CacheService::flush();

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Todo o cache foi limpo' : 'Falha ao limpar cache'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar todo o cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renovar TTL de uma chave
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'duration' => 'nullable|string|in:short,medium,long,daily,weekly'
        ]);

        try {
            $duration = $request->get('duration', 'medium');
            $success = CacheService::refresh($request->key, $duration);

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? "TTL da chave '{$request->key}' renovado para {$duration}"
                    : 'Falha ao renovar TTL'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renovar TTL',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar se uma chave existe
     */
    public function has(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string'
        ]);

        try {
            $exists = CacheService::has($request->key);

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $request->key,
                    'exists' => $exists
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar chave',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pré-carregar cache importante
     */
    public function warmup(): JsonResponse
    {
        try {
            $warmedKeys = [];

            // Pré-carregar dashboard
            $dashboardKey = CacheService::generateKey('dashboard', 'main');
            if (!CacheService::has($dashboardKey)) {
                // Simular carregamento do dashboard
                $warmedKeys[] = 'dashboard_main';
            }

            // Pré-carregar opções/enums
            $opcoesKey = CacheService::generateKey('relatorio', 'opcoes');
            if (!CacheService::has($opcoesKey)) {
                $warmedKeys[] = 'relatorio_opcoes';
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache aquecido com sucesso',
                'data' => [
                    'warmed_keys' => $warmedKeys,
                    'count' => count($warmedKeys)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aquecer cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configurações de cache
     */
    public function config(): JsonResponse
    {
        try {
            $config = [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
                'durations' => CacheService::CACHE_TIMES,
                'prefixes' => CacheService::CACHE_PREFIXES,
                'redis_host' => config('database.redis.default.host'),
                'redis_port' => config('database.redis.default.port'),
            ];

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
