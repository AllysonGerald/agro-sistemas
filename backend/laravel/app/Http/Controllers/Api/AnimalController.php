<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Services\AnimalService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Animais
 */
class AnimalController extends Controller
{
    public function __construct(
        private AnimalService $animalService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search',
                'sexo',
                'situacao',
                'raca',
                'propriedade_id',
                'lote_id',
                'rebanho_id',
                'categoria_atual',
                'peso_min',
                'peso_max'
            ]);

            $perPage = $request->get('per_page', 15);

            $animais = $this->animalService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $animais->items(),
                    'current_page' => $animais->currentPage(),
                    'last_page' => $animais->lastPage(),
                    'per_page' => $animais->perPage(),
                    'total' => $animais->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar animais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'identificacao' => 'required|string|unique:animais,identificacao',
                'nome_numero' => 'nullable|string',
                'sexo' => 'required|in:macho,femea',
                'raca' => 'nullable|string',
                'categoria_atual' => 'nullable|in:bezerro,bezerra,novilho,novilha,boi,vaca,touro',
                'situacao' => 'nullable|in:ativo,vendido,morto,transferido',
                'data_nascimento' => 'nullable|date',
                'data_entrada' => 'nullable|date',
                'peso_entrada' => 'nullable|numeric|min:0',
                'peso_atual' => 'nullable|numeric|min:0',
                'origem_materna' => 'nullable|string',
                'origem_paterna' => 'nullable|string',
                'rebanho_id' => 'nullable|exists:rebanhos,id',
                'propriedade_id' => 'required|exists:propriedades,id',
                'lote_id' => 'nullable|exists:lotes,id',
                'finalidade_lote' => 'nullable|string',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $animal = $this->animalService->create($validator->validated());

            // Registrar atividade
            $this->atividadeService->registrar([
                'tipo' => 'cadastro_animal',
                'descricao' => "Animal {$animal->identificacao} cadastrado",
                'modelo' => Animal::class,
                'modelo_id' => $animal->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Animal cadastrado com sucesso',
                'data' => $animal->load(['rebanho', 'propriedade', 'lote'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar animal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $animal = $this->animalService->findById($id);

            if (!$animal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Animal não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $animal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar animal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $animal = Animal::find($id);

            if (!$animal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Animal não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'identificacao' => 'sometimes|required|string|unique:animais,identificacao,' . $id,
                'nome_numero' => 'nullable|string',
                'sexo' => 'sometimes|required|in:macho,femea',
                'raca' => 'nullable|string',
                'categoria_atual' => 'nullable|in:bezerro,bezerra,novilho,novilha,boi,vaca,touro',
                'situacao' => 'nullable|in:ativo,vendido,morto,transferido',
                'data_nascimento' => 'nullable|date',
                'data_entrada' => 'nullable|date',
                'peso_entrada' => 'nullable|numeric|min:0',
                'peso_atual' => 'nullable|numeric|min:0',
                'origem_materna' => 'nullable|string',
                'origem_paterna' => 'nullable|string',
                'rebanho_id' => 'nullable|exists:rebanhos,id',
                'propriedade_id' => 'sometimes|required|exists:propriedades,id',
                'lote_id' => 'nullable|exists:lotes,id',
                'finalidade_lote' => 'nullable|string',
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->animalService->update($animal, $validator->validated());

            // Registrar atividade
            $this->atividadeService->registrar([
                'tipo' => 'atualizacao_animal',
                'descricao' => "Animal {$animal->identificacao} atualizado",
                'modelo' => Animal::class,
                'modelo_id' => $animal->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Animal atualizado com sucesso',
                'data' => $animal->fresh()->load(['rebanho', 'propriedade', 'lote'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar animal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $animal = Animal::find($id);

            if (!$animal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Animal não encontrado'
                ], 404);
            }

            $identificacao = $animal->identificacao;
            
            $this->animalService->delete($animal);

            // Registrar atividade
            $this->atividadeService->registrar([
                'tipo' => 'exclusao_animal',
                'descricao' => "Animal {$identificacao} excluído",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Animal excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir animal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function historico(int $id): JsonResponse
    {
        try {
            $historico = $this->animalService->getHistorico($id);

            if (empty($historico)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Animal não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $historico
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar histórico',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function manejos(int $id): JsonResponse
    {
        try {
            $animal = Animal::with('manejos')->find($id);

            if (!$animal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Animal não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $animal->manejos()->orderBy('data', 'desc')->get()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar manejos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFoto(Request $request, int $id): JsonResponse
    {
        try {
            $animal = Animal::find($id);

            if (!$animal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Animal não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fotoUrl = $this->animalService->uploadFoto($animal, $request->file('foto'));

            return response()->json([
                'success' => true,
                'message' => 'Foto enviada com sucesso',
                'data' => [
                    'foto_url' => $fotoUrl,
                    'animal' => $animal->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar foto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticas(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            
            $estatisticas = $this->animalService->getEstatisticas($propriedadeId);

            return response()->json([
                'success' => true,
                'data' => $estatisticas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
