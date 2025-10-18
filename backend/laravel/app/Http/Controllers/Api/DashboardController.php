<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller da API do Dashboard
 *
 * Gerencia as requisições da API do dashboard delegando a lógica
 * de negócio para o DashboardService e formatando respostas
 * através do DashboardResource.
 *
 * @package App\Http\Controllers\Api
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private AtividadeService $atividadeService
    ) {}

    /**
     * Exibir dados principais do dashboard
     *
     * @group Dashboard API
     * @response 200 {
     *   "success": true,
     *   "message": "Dados do dashboard carregados com sucesso",
     *   "data": {
     *     "estatisticas": {},
     *     "graficos": {},
     *     "atividades_recentes": [],
     *     "metricas_periodo": {},
     *     "meta": {}
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $dados = $this->dashboardService->getDashboardData();

            return response()->json([
                'success' => true,
                'message' => 'Dados do dashboard carregados com sucesso',
                'data' => new DashboardResource($dados)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do dashboard',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter apenas estatísticas
     *
     * @group Dashboard API
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total_produtores": {"valor": 25, "crescimento": 12.5},
     *     "total_propriedades": {"valor": 40, "crescimento": 8.3},
     *     "total_unidades_producao": {"valor": 85, "crescimento": -2.1},
     *     "total_animais": {"valor": 32, "crescimento": 15.7}
     *   }
     * }
     */
    public function estatisticas(): JsonResponse
    {
        try {
            $estatisticas = $this->dashboardService->obterEstatisticas();

            return response()->json([
                'success' => true,
                'data' => $estatisticas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter dados para gráficos
     *
     * @group Dashboard API
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "producao_mensal": [],
     *     "distribuicao_propriedades": [],
     *     "evolucao_rebanho": [],
     *     "tipos_producao": []
     *   }
     * }
     */
    public function graficos(): JsonResponse
    {
        try {
            $graficos = $this->dashboardService->obterDadosGraficos();

            return response()->json([
                'success' => true,
                'data' => $graficos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados dos gráficos',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter atividades recentes
     *
     * @group Dashboard API
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "tipo": "produtor",
     *       "descricao": "Novo produtor cadastrado",
     *       "data": "2024-10-16T10:30:00Z",
     *       "icone": "fas fa-user-plus",
     *       "cor": "success"
     *     }
     *   ]
     * }
     */
    public function atividades(): JsonResponse
    {
        try {
            $atividades = $this->atividadeService->obterRecentes(10);

            return response()->json([
                'success' => true,
                'data' => $atividades
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar atividades recentes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter métricas do período
     *
     * @group Dashboard API
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "periodo_atual": {
     *       "inicio": "2024-10-01",
     *       "fim": "2024-10-31",
     *       "total_registros": 25
     *     },
     *     "periodo_anterior": {
     *       "inicio": "2024-09-01",
     *       "fim": "2024-09-30",
     *       "total_registros": 20
     *     },
     *     "comparacao": {
     *       "crescimento_absoluto": 5,
     *       "crescimento_percentual": 25.0
     *     }
     *   }
     * }
     */
    public function metricas(): JsonResponse
    {
        try {
            $metricas = $this->dashboardService->obterMetricasPeriodo();

            return response()->json([
                'success' => true,
                'data' => $metricas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar métricas do período',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}
