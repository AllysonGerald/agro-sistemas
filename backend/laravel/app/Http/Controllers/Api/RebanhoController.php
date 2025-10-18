<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRebanhoRequest;
use App\Http\Requests\UpdateRebanhoRequest;
use App\Models\Rebanho;
use App\Services\RebanhoService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Rebanhos
 */
class RebanhoController extends Controller
{
    public function __construct(
        private RebanhoService $rebanhoService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['especie', 'finalidade', 'propriedade_id', 'quantidade_min', 'municipio', 'produtor_id']);

            // Adiciona suporte para busca geral
            if ($request->has('search') && !empty($request->get('search'))) {
                $filters['search'] = $request->get('search');
            }

            $perPage = $request->get('per_page', 15);

            $rebanhos = $this->rebanhoService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $rebanhos->items(),
                    'current_page' => $rebanhos->currentPage(),
                    'last_page' => $rebanhos->lastPage(),
                    'per_page' => $rebanhos->perPage(),
                    'total' => $rebanhos->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rebanhos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreRebanhoRequest $request): JsonResponse
    {
        try {
            $rebanho = $this->rebanhoService->create($request->validated());

            // Registrar atividade
            $this->atividadeService->registrarRebanho($rebanho, 'create');

            return response()->json([
                'success' => true,
                'message' => 'Rebanho criado com sucesso',
                'data' => $rebanho->load('propriedade.produtor')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar rebanho',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $rebanho = $this->rebanhoService->findById($id);

            if (!$rebanho) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rebanho não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $rebanho
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rebanho',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateRebanhoRequest $request, int $id): JsonResponse
    {
        try {
            $rebanho = Rebanho::find($id);

            if (!$rebanho) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rebanho não encontrado'
                ], 404);
            }

            $this->rebanhoService->update($rebanho, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Rebanho atualizado com sucesso',
                'data' => $rebanho->fresh()->load('propriedade.produtor')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar rebanho',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $rebanho = Rebanho::find($id);

            if (!$rebanho) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rebanho não encontrado'
                ], 404);
            }

            $this->rebanhoService->delete($rebanho);

            return response()->json([
                'success' => true,
                'message' => 'Rebanho excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir rebanho',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function relatorioEspecies(): JsonResponse
    {
        try {
            $dados = $this->rebanhoService->getQuantidadeByEspecie();

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

    public function estatisticasEspecies(): JsonResponse
    {
        try {
            $stats = $this->rebanhoService->getEspecieStats();

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
            $rebanhos = $this->rebanhoService->getByPropriedade($propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $rebanhos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rebanhos da propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function porProdutor(int $produtorId): JsonResponse
    {
        try {
            $rebanhos = $this->rebanhoService->getByProdutor($produtorId);

            return response()->json([
                'success' => true,
                'data' => $rebanhos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rebanhos do produtor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarPorProdutor(int $produtorId)
    {
        try {
            $dados = $this->rebanhoService->exportDataByProdutor($produtorId);

            // Aqui implementaremos a exportação em PDF depois
            return response()->json([
                'success' => true,
                'data' => $dados,
                'message' => 'Dados preparados para exportação'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar dados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function desatualizados(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 90);
            $rebanhos = $this->rebanhoService->getRebanhoDesatualizado($days);

            return response()->json([
                'success' => true,
                'data' => $rebanhos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar rebanhos desatualizados',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
