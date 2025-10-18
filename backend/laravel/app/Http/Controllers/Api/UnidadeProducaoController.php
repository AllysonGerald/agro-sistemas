<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnidadeProducaoRequest;
use App\Http\Requests\UpdateUnidadeProducaoRequest;
use App\Models\UnidadeProducao;
use App\Services\UnidadeProducaoService;
use App\Services\AtividadeService;
use App\Http\Resources\UnidadeProducaoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Unidades de Produção
 */
class UnidadeProducaoController extends Controller
{
    public function __construct(
        private UnidadeProducaoService $unidadeService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['nome_cultura', 'propriedade_id', 'area_min', 'municipio']);

            // Adiciona suporte para busca geral
            if ($request->has('search') && !empty($request->get('search'))) {
                $filters['search'] = $request->get('search');
            }

            $perPage = $request->get('per_page', 15);

            $unidades = $this->unidadeService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => UnidadeProducaoResource::collection($unidades->items()),
                    'current_page' => $unidades->currentPage(),
                    'last_page' => $unidades->lastPage(),
                    'per_page' => $unidades->perPage(),
                    'total' => $unidades->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar unidades de produção',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreUnidadeProducaoRequest $request): JsonResponse
    {
        try {
            // Validar se a área não excede o limite da propriedade
            $validacao = $this->unidadeService->validateAreaLimits($request->validated());

            if (!$validacao['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validacao['message']
                ], 422);
            }

            $unidade = $this->unidadeService->create($request->validated());

            // Registrar atividade
            $this->atividadeService->registrarUnidadeProducao($unidade, 'create');

            return response()->json([
                'success' => true,
                'message' => 'Unidade de produção criada com sucesso',
                'data' => $unidade->load('propriedade.produtor')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar unidade de produção',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $unidade = $this->unidadeService->findById($id);

            if (!$unidade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unidade de produção não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $unidade
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar unidade de produção',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateUnidadeProducaoRequest $request, int $id): JsonResponse
    {
        try {
            $unidade = UnidadeProducao::find($id);

            if (!$unidade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unidade de produção não encontrada'
                ], 404);
            }

            // Validar área excluindo a unidade atual
            $validacao = $this->unidadeService->validateAreaLimits($request->validated(), $id);

            if (!$validacao['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validacao['message']
                ], 422);
            }

            $this->unidadeService->update($unidade, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Unidade de produção atualizada com sucesso',
                'data' => $unidade->fresh()->load('propriedade.produtor')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar unidade de produção',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $unidade = UnidadeProducao::find($id);

            if (!$unidade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unidade de produção não encontrada'
                ], 404);
            }

            $this->unidadeService->delete($unidade);

            return response()->json([
                'success' => true,
                'message' => 'Unidade de produção excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir unidade de produção',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function relatorioAreaPorCultura(): JsonResponse
    {
        try {
            $dados = $this->unidadeService->getAreaByCultura();

            return response()->json([
                'success' => true,
                'data' => $dados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticasCulturas(): JsonResponse
    {
        try {
            $stats = $this->unidadeService->getCulturaStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function porPropriedade(int $propriedadeId): JsonResponse
    {
        try {
            $unidades = $this->unidadeService->getByPropriedade($propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $unidades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar unidades da propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
