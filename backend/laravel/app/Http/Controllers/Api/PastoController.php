<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasto;
use App\Services\PastoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Pastos
 */
class PastoController extends Controller
{
    public function __construct(private PastoService $pastoService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'propriedade_id']);
            $perPage = $request->get('per_page', 15);
            $pastos = $this->pastoService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $pastos->items(),
                    'current_page' => $pastos->currentPage(),
                    'last_page' => $pastos->lastPage(),
                    'per_page' => $pastos->perPage(),
                    'total' => $pastos->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao buscar pastos', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string',
                'codigo' => 'required|string|unique:pastos,codigo',
                'area_hectares' => 'required|numeric|min:0',
                'tipo_pastagem' => 'nullable|string',
                'qualidade' => 'nullable|in:excelente,boa,regular,ruim,degradada',
                'status' => 'nullable|in:disponivel,ocupado,em_reforma,em_descanso',
                'capacidade_animais' => 'nullable|integer|min:0',
                'animais_atual' => 'nullable|integer|min:0',
                'propriedade_id' => 'required|exists:propriedades,id',
                'tem_agua' => 'boolean',
                'tem_sombra' => 'boolean',
                'tem_cocho' => 'boolean',
                'tem_saleiro' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $pasto = $this->pastoService->create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Pasto criado com sucesso', 'data' => $pasto], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $pasto = $this->pastoService->findById($id);
            if (!$pasto) return response()->json(['success' => false, 'message' => 'Pasto não encontrado'], 404);
            return response()->json(['success' => true, 'data' => $pasto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $pasto = Pasto::find($id);
            if (!$pasto) return response()->json(['success' => false, 'message' => 'Pasto não encontrado'], 404);

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|required|string',
                'codigo' => 'sometimes|required|string|unique:pastos,codigo,' . $id,
                'area_hectares' => 'sometimes|required|numeric|min:0',
                'tipo_pastagem' => 'nullable|string',
                'capacidade_animais' => 'nullable|integer|min:0',
                'propriedade_id' => 'sometimes|required|exists:propriedades,id',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $this->pastoService->update($pasto, $validator->validated());
            return response()->json(['success' => true, 'message' => 'Pasto atualizado', 'data' => $pasto->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $pasto = Pasto::find($id);
            if (!$pasto) return response()->json(['success' => false, 'message' => 'Pasto não encontrado'], 404);
            $this->pastoService->delete($pasto);
            return response()->json(['success' => true, 'message' => 'Pasto excluído']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function disponiveis(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            $pastos = $this->pastoService->getDisponiveis($propriedadeId);
            return response()->json(['success' => true, 'data' => $pastos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function iniciarDescanso(Request $request, int $id): JsonResponse
    {
        try {
            $pasto = Pasto::find($id);
            if (!$pasto) return response()->json(['success' => false, 'message' => 'Pasto não encontrado'], 404);

            $validator = Validator::make($request->all(), [
                'data_prevista_liberacao' => 'required|date|after:today',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $this->pastoService->iniciarDescanso($pasto, $request->data_prevista_liberacao);
            return response()->json(['success' => true, 'message' => 'Descanso iniciado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function liberar(int $id): JsonResponse
    {
        try {
            $pasto = Pasto::find($id);
            if (!$pasto) return response()->json(['success' => false, 'message' => 'Pasto não encontrado'], 404);
            $this->pastoService->liberar($pasto);
            return response()->json(['success' => true, 'message' => 'Pasto liberado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
