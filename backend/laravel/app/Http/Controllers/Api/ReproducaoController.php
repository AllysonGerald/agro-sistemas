<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reproducao;
use App\Services\ReproducaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Reprodução
 */
class ReproducaoController extends Controller
{
    public function __construct(private ReproducaoService $reproducaoService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'tipo', 'femea_id', 'propriedade_id']);
            $perPage = $request->get('per_page', 15);
            $reproducoes = $this->reproducaoService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $reproducoes->items(),
                    'current_page' => $reproducoes->currentPage(),
                    'last_page' => $reproducoes->lastPage(),
                    'per_page' => $reproducoes->perPage(),
                    'total' => $reproducoes->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo' => 'required|in:monta_natural,inseminacao_artificial,fertilizacao_in_vitro',
                'femea_id' => 'required|exists:animais,id',
                'macho_id' => 'nullable|exists:animais,id',
                'touro_nome' => 'nullable|string',
                'raca_touro' => 'nullable|string',
                'data_cobertura' => 'nullable|date',
                'data_diagnostico' => 'nullable|date',
                'status' => 'nullable|in:aguardando_diagnostico,prenha,vazia,abortou,parto_realizado',
                'propriedade_id' => 'required|exists:propriedades,id',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $reproducao = $this->reproducaoService->create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Reprodução registrada', 'data' => $reproducao], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $reproducao = $this->reproducaoService->findById($id);
            if (!$reproducao) return response()->json(['success' => false, 'message' => 'Reprodução não encontrada'], 404);
            return response()->json(['success' => true, 'data' => $reproducao]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $reproducao = Reproducao::find($id);
            if (!$reproducao) return response()->json(['success' => false, 'message' => 'Reprodução não encontrada'], 404);

            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|required|in:aguardando_diagnostico,prenha,vazia,abortou,parto_realizado',
                'data_diagnostico' => 'nullable|date',
                'data_parto' => 'nullable|date',
                'numero_crias' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $this->reproducaoService->update($reproducao, $validator->validated());
            return response()->json(['success' => true, 'message' => 'Reprodução atualizada', 'data' => $reproducao->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $reproducao = Reproducao::find($id);
            if (!$reproducao) return response()->json(['success' => false, 'message' => 'Reprodução não encontrada'], 404);
            $this->reproducaoService->delete($reproducao);
            return response()->json(['success' => true, 'message' => 'Reprodução excluída']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function prenhas(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            $prenhas = $this->reproducaoService->getPrenhas($propriedadeId);
            return response()->json(['success' => true, 'data' => $prenhas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function partosProximos(Request $request): JsonResponse
    {
        try {
            $dias = $request->get('dias', 30);
            $propriedadeId = $request->get('propriedade_id');
            $partos = $this->reproducaoService->getPartosProximos($dias, $propriedadeId);
            return response()->json(['success' => true, 'data' => $partos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function estatisticas(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            $estatisticas = $this->reproducaoService->getEstatisticas($propriedadeId);
            return response()->json(['success' => true, 'data' => $estatisticas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function registrarParto(Request $request, int $id): JsonResponse
    {
        try {
            $reproducao = Reproducao::find($id);
            if (!$reproducao) return response()->json(['success' => false, 'message' => 'Reprodução não encontrada'], 404);

            $validator = Validator::make($request->all(), [
                'data_parto' => 'required|date',
                'numero_crias' => 'required|integer|min:1',
                'tipo_parto' => 'nullable|in:normal,cesariana,assistido',
                'dificuldade_parto' => 'nullable|in:facil,medio,dificil',
                'crias' => 'nullable|array',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $this->reproducaoService->registrarParto($reproducao, $validator->validated());
            return response()->json(['success' => true, 'message' => 'Parto registrado', 'data' => $reproducao->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
