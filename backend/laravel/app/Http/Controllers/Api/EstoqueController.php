<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estoque;
use App\Services\EstoqueService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Estoque
 */
class EstoqueController extends Controller
{
    public function __construct(
        private EstoqueService $estoqueService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search',
                'categoria',
                'status',
                'propriedade_id'
            ]);

            $perPage = $request->get('per_page', 15);

            $estoque = $this->estoqueService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $estoque->items(),
                    'current_page' => $estoque->currentPage(),
                    'last_page' => $estoque->lastPage(),
                    'per_page' => $estoque->perPage(),
                    'total' => $estoque->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estoque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'codigo' => 'nullable|string|unique:estoque,codigo',
                'marca' => 'nullable|string',
                'categoria' => 'required|in:racao,suplemento,medicamento,vacina,vermifugo,equipamento,outro',
                'quantidade' => 'required|numeric|min:0',
                'unidade_medida' => 'required|string',
                'quantidade_minima' => 'nullable|numeric|min:0',
                'valor_unitario' => 'nullable|numeric|min:0',
                'data_compra' => 'nullable|date',
                'data_validade' => 'nullable|date',
                'lote' => 'nullable|string',
                'propriedade_id' => 'required|exists:propriedades,id',
                'localizacao_fisica' => 'nullable|string',
                'fornecedor' => 'nullable|string',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $item = $this->estoqueService->create($validator->validated());

            $this->atividadeService->registrar([
                'tipo' => 'estoque_criado',
                'descricao' => "Item {$item->nome} adicionado ao estoque",
                'modelo' => Estoque::class,
                'modelo_id' => $item->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item adicionado ao estoque com sucesso',
                'data' => $item->load('propriedade')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar item ao estoque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->estoqueService->findById($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = Estoque::find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|required|string|max:255',
                'codigo' => 'nullable|string|unique:estoque,codigo,' . $id,
                'marca' => 'nullable|string',
                'categoria' => 'sometimes|required|in:racao,suplemento,medicamento,vacina,vermifugo,equipamento,outro',
                'quantidade' => 'sometimes|required|numeric|min:0',
                'unidade_medida' => 'sometimes|required|string',
                'quantidade_minima' => 'nullable|numeric|min:0',
                'valor_unitario' => 'nullable|numeric|min:0',
                'data_compra' => 'nullable|date',
                'data_validade' => 'nullable|date',
                'lote' => 'nullable|string',
                'propriedade_id' => 'sometimes|required|exists:propriedades,id',
                'localizacao_fisica' => 'nullable|string',
                'fornecedor' => 'nullable|string',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->estoqueService->update($item, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Item atualizado com sucesso',
                'data' => $item->fresh()->load('propriedade')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = Estoque::find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado'
                ], 404);
            }

            $nome = $item->nome;
            
            $this->estoqueService->delete($item);

            $this->atividadeService->registrar([
                'tipo' => 'estoque_excluido',
                'descricao' => "Item {$nome} excluído do estoque",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function baixoEstoque(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            
            $items = $this->estoqueService->getBaixoEstoque($propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar items com baixo estoque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function vencidos(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            
            $items = $this->estoqueService->getVencidos($propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar items vencidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function porVencer(Request $request): JsonResponse
    {
        try {
            $dias = $request->get('dias', 30);
            $propriedadeId = $request->get('propriedade_id');
            
            $items = $this->estoqueService->getPorVencer($dias, $propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar items por vencer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarEntrada(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantidade' => 'required|numeric|min:0.01',
                'valor_unitario' => 'nullable|numeric|min:0',
                'data_compra' => 'nullable|date',
                'data_validade' => 'nullable|date',
                'lote' => 'nullable|string',
                'fornecedor' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->estoqueService->registrarEntrada($id, $request->quantidade, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Entrada registrada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarSaida(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantidade' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->estoqueService->registrarSaida($id, $request->quantidade);

            return response()->json([
                'success' => true,
                'message' => 'Saída registrada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
