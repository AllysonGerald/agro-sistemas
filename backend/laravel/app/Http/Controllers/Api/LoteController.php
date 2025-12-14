<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Services\LoteService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Lotes
 */
class LoteController extends Controller
{
    public function __construct(
        private LoteService $loteService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search',
                'status',
                'finalidade',
                'propriedade_id'
            ]);

            $perPage = $request->get('per_page', 15);

            $lotes = $this->loteService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $lotes->items(),
                    'current_page' => $lotes->currentPage(),
                    'last_page' => $lotes->lastPage(),
                    'per_page' => $lotes->perPage(),
                    'total' => $lotes->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar lotes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'codigo' => 'required|string|unique:lotes,codigo',
                'finalidade' => 'nullable|in:engorda,reproducao,cria,recria,terminacao',
                'status' => 'nullable|in:ativo,finalizado,em_formacao',
                'data_formacao' => 'nullable|date',
                'data_prevista_venda' => 'nullable|date',
                'propriedade_id' => 'required|exists:propriedades,id',
                'pasto_id' => 'nullable|exists:pastos,id',
                'quantidade_animais' => 'nullable|integer|min:0',
                'peso_medio_inicial' => 'nullable|numeric|min:0',
                'peso_medio_atual' => 'nullable|numeric|min:0',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lote = $this->loteService->create($validator->validated());

            $this->atividadeService->registrar([
                'tipo' => 'lote_criado',
                'descricao' => "Lote {$lote->nome} criado",
                'modelo' => Lote::class,
                'modelo_id' => $lote->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lote criado com sucesso',
                'data' => $lote->load(['propriedade', 'pasto'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar lote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $lote = $this->loteService->findById($id);

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $lote
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar lote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $lote = Lote::find($id);

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|required|string|max:255',
                'codigo' => 'sometimes|required|string|unique:lotes,codigo,' . $id,
                'finalidade' => 'nullable|in:engorda,reproducao,cria,recria,terminacao',
                'status' => 'nullable|in:ativo,finalizado,em_formacao',
                'data_formacao' => 'nullable|date',
                'data_prevista_venda' => 'nullable|date',
                'propriedade_id' => 'sometimes|required|exists:propriedades,id',
                'pasto_id' => 'nullable|exists:pastos,id',
                'quantidade_animais' => 'nullable|integer|min:0',
                'peso_medio_inicial' => 'nullable|numeric|min:0',
                'peso_medio_atual' => 'nullable|numeric|min:0',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->loteService->update($lote, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Lote atualizado com sucesso',
                'data' => $lote->fresh()->load(['propriedade', 'pasto'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar lote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $lote = Lote::find($id);

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            $nome = $lote->nome;
            
            $this->loteService->delete($lote);

            $this->atividadeService->registrar([
                'tipo' => 'lote_excluido',
                'descricao' => "Lote {$nome} excluído",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lote excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir lote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function animais(int $id): JsonResponse
    {
        try {
            $lote = Lote::with('animais')->find($id);

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $lote->animais
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar animais do lote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adicionarAnimal(Request $request, int $id): JsonResponse
    {
        try {
            $lote = Lote::find($id);

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'animal_id' => 'required|exists:animais,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->loteService->adicionarAnimal($lote, $request->animal_id);

            return response()->json([
                'success' => true,
                'message' => 'Animal adicionado ao lote com sucesso',
                'data' => $lote->fresh()->load('animais')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function removerAnimal(Request $request, int $id): JsonResponse
    {
        try {
            $lote = Lote::find($id);

            if (!$lote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'animal_id' => 'required|exists:animais,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->loteService->removerAnimal($lote, $request->animal_id);

            return response()->json([
                'success' => true,
                'message' => 'Animal removido do lote com sucesso',
                'data' => $lote->fresh()->load('animais')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function relatorio(int $id): JsonResponse
    {
        try {
            $relatorio = $this->loteService->getRelatorio($id);

            if (empty($relatorio)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $relatorio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
