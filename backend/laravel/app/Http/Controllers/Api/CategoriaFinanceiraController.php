<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriaFinanceira;
use App\Services\CategoriaFinanceiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Categorias Financeiras
 */
class CategoriaFinanceiraController extends Controller
{
    public function __construct(private CategoriaFinanceiraService $categoriaService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['tipo', 'ativo']);
            $categorias = $this->categoriaService->getAll($filters);

            return response()->json(['success' => true, 'data' => $categorias]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'cor' => 'nullable|string',
                'icone' => 'nullable|string',
                'tipo' => 'required|in:receita,despesa',
                'categoria_pai_id' => 'nullable|exists:categorias_financeiras,id',
                'ativo' => 'boolean',
                'descricao' => 'nullable|string',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $categoria = $this->categoriaService->create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Categoria criada', 'data' => $categoria], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaService->findById($id);
            if (!$categoria) return response()->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);
            return response()->json(['success' => true, 'data' => $categoria]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $categoria = CategoriaFinanceira::find($id);
            if (!$categoria) return response()->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|required|string|max:255',
                'tipo' => 'sometimes|required|in:receita,despesa',
                'ativo' => 'boolean',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $this->categoriaService->update($categoria, $validator->validated());
            return response()->json(['success' => true, 'message' => 'Categoria atualizada', 'data' => $categoria->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $categoria = CategoriaFinanceira::find($id);
            if (!$categoria) return response()->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);
            $this->categoriaService->delete($categoria);
            return response()->json(['success' => true, 'message' => 'Categoria excluída']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
