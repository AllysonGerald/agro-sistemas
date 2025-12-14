<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manejo;
use App\Services\ManejoService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Manejo
 */
class ManejoController extends Controller
{
    public function __construct(
        private ManejoService $manejoService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'tipo',
                'animal_id',
                'propriedade_id',
                'data_inicio',
                'data_fim'
            ]);

            $perPage = $request->get('per_page', 15);

            $manejos = $this->manejoService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $manejos->items(),
                    'current_page' => $manejos->currentPage(),
                    'last_page' => $manejos->lastPage(),
                    'per_page' => $manejos->perPage(),
                    'total' => $manejos->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar manejos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo' => 'required|in:pesagem,vacinacao,vermifugacao,curativo,castracao,descorna,marcacao,transferencia,exame,outro',
                'data' => 'required|date',
                'hora' => 'nullable|date_format:H:i',
                'animal_id' => 'required|exists:animais,id',
                'peso' => 'nullable|numeric|min:0',
                'produto_aplicado' => 'nullable|string',
                'dose' => 'nullable|string',
                'lote_produto' => 'nullable|string',
                'data_proxima_aplicacao' => 'nullable|date|after:data',
                'responsavel' => 'nullable|string',
                'veterinario' => 'nullable|string',
                'propriedade_id' => 'required|exists:propriedades,id',
                'observacoes' => 'nullable|string',
                'resultado' => 'nullable|in:sucesso,pendente,falha',
                'custo' => 'nullable|numeric|min:0',
                'fotos' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $manejo = $this->manejoService->create($validator->validated());

            $this->atividadeService->registrar([
                'tipo' => 'manejo_registrado',
                'descricao' => "Manejo de {$manejo->tipo} registrado",
                'modelo' => Manejo::class,
                'modelo_id' => $manejo->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Manejo registrado com sucesso',
                'data' => $manejo->load(['animal', 'propriedade'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar manejo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $manejo = $this->manejoService->findById($id);

            if (!$manejo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manejo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $manejo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar manejo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $manejo = Manejo::find($id);

            if (!$manejo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manejo não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'tipo' => 'sometimes|required|in:pesagem,vacinacao,vermifugacao,curativo,castracao,descorna,marcacao,transferencia,exame,outro',
                'data' => 'sometimes|required|date',
                'hora' => 'nullable|date_format:H:i',
                'animal_id' => 'sometimes|required|exists:animais,id',
                'peso' => 'nullable|numeric|min:0',
                'produto_aplicado' => 'nullable|string',
                'dose' => 'nullable|string',
                'lote_produto' => 'nullable|string',
                'data_proxima_aplicacao' => 'nullable|date',
                'responsavel' => 'nullable|string',
                'veterinario' => 'nullable|string',
                'propriedade_id' => 'sometimes|required|exists:propriedades,id',
                'observacoes' => 'nullable|string',
                'resultado' => 'nullable|in:sucesso,pendente,falha',
                'custo' => 'nullable|numeric|min:0',
                'fotos' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->manejoService->update($manejo, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Manejo atualizado com sucesso',
                'data' => $manejo->fresh()->load(['animal', 'propriedade'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar manejo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $manejo = Manejo::find($id);

            if (!$manejo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manejo não encontrado'
                ], 404);
            }

            $this->manejoService->delete($manejo);

            return response()->json([
                'success' => true,
                'message' => 'Manejo excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir manejo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function porAnimal(int $animalId): JsonResponse
    {
        try {
            $manejos = $this->manejoService->getPorAnimal($animalId);

            return response()->json([
                'success' => true,
                'data' => $manejos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar manejos do animal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function porTipo(string $tipo): JsonResponse
    {
        try {
            $manejos = Manejo::where('tipo', $tipo)
                ->with(['animal', 'propriedade'])
                ->orderBy('data', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $manejos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar manejos por tipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function agenda(Request $request): JsonResponse
    {
        try {
            $dataInicio = $request->get('data_inicio', now()->format('Y-m-d'));
            $dataFim = $request->get('data_fim', now()->addDays(30)->format('Y-m-d'));

            $agenda = $this->manejoService->getAgenda($dataInicio, $dataFim);

            return response()->json([
                'success' => true,
                'data' => $agenda
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar agenda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pendentes(): JsonResponse
    {
        try {
            $pendentes = $this->manejoService->getPendentes();

            return response()->json([
                'success' => true,
                'data' => $pendentes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar manejos pendentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
