<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropriedadeRequest;
use App\Http\Requests\UpdatePropriedadeRequest;
use App\Models\Propriedade;
use App\Services\PropriedadeService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Propriedades
 */
class PropriedadeController extends Controller
{
    public function __construct(
        private PropriedadeService $propriedadeService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['nome', 'municipio', 'uf', 'produtor_id', 'area_min', 'area_max']);

            // Adiciona suporte para busca geral
            if ($request->has('search') && !empty($request->get('search'))) {
                $filters['search'] = $request->get('search');
            }

            $perPage = $request->get('per_page', 15);

            $propriedades = $this->propriedadeService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $propriedades->items(),
                    'current_page' => $propriedades->currentPage(),
                    'last_page' => $propriedades->lastPage(),
                    'per_page' => $propriedades->perPage(),
                    'total' => $propriedades->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StorePropriedadeRequest $request): JsonResponse
    {
        try {
            $propriedade = $this->propriedadeService->create($request->validated());

            // Registrar atividade
            $this->atividadeService->registrarPropriedade($propriedade, 'create');

            return response()->json([
                'success' => true,
                'message' => 'Propriedade criada com sucesso',
                'data' => $propriedade->load(['produtor', 'unidadesProducao', 'rebanhos'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $propriedade = $this->propriedadeService->findById($id);

            if (!$propriedade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $propriedade
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdatePropriedadeRequest $request, int $id): JsonResponse
    {
        try {
            $propriedade = Propriedade::find($id);

            if (!$propriedade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }

            $this->propriedadeService->update($propriedade, $request->validated());

            // Registrar atividade
            $this->atividadeService->registrarPropriedade($propriedade, 'update');

            return response()->json([
                'success' => true,
                'message' => 'Propriedade atualizada com sucesso',
                'data' => $propriedade->fresh()->load(['produtor', 'unidadesProducao', 'rebanhos'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $propriedade = Propriedade::find($id);

            if (!$propriedade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propriedade não encontrada'
                ], 404);
            }

            // Registrar atividade antes de excluir
            $this->atividadeService->registrarPropriedade($propriedade, 'delete');

            $this->propriedadeService->delete($propriedade);

            return response()->json([
                'success' => true,
                'message' => 'Propriedade excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function relatorioMunicipios(): JsonResponse
    {
        try {
            $dados = $this->propriedadeService->getByMunicipio();

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

    public function exportarExcel(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['municipio', 'uf', 'produtor_id']);
            $dados = $this->propriedadeService->exportData($filters);

            // Implementaremos a exportação Excel depois
            return response()->json([
                'success' => true,
                'data' => $dados,
                'message' => 'Dados preparados para exportação Excel'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar dados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validarArea(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');

            if (!$propriedadeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID da propriedade é obrigatório'
                ], 400);
            }

            $validacao = $this->propriedadeService->validateAreaConsistency($propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $validacao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na validação',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
