<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutorRuralRequest;
use App\Http\Requests\UpdateProdutorRuralRequest;
use App\Models\ProdutorRural;
use App\Services\ProdutorRuralService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Produtores Rurais
 */
class ProdutorRuralController extends Controller
{
    public function __construct(
        private ProdutorRuralService $produtorService,
        private AtividadeService $atividadeService
    ) {}

    /**
     * Listar produtores rurais
     *
     * Retorna uma lista paginada de produtores rurais com filtros opcionais
     *
     * @queryParam per_page int Número de itens por página (padrão: 15)
     * @queryParam nome string Filtrar por nome
     * @queryParam cpf_cnpj string Filtrar por CPF/CNPJ
     * @queryParam email string Filtrar por email
     * @queryParam municipio string Filtrar por município
     */

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['nome', 'cpf_cnpj', 'email', 'municipio']);

            // Adiciona suporte para busca geral
            if ($request->has('search') && !empty($request->get('search'))) {
                $filters['search'] = $request->get('search');
            }

            $perPage = $request->get('per_page', 15);

            $produtores = $this->produtorService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $produtores->items(),
                    'current_page' => $produtores->currentPage(),
                    'last_page' => $produtores->lastPage(),
                    'per_page' => $produtores->perPage(),
                    'total' => $produtores->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtores rurais',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreProdutorRuralRequest $request): JsonResponse
    {
        try {
            $produtor = $this->produtorService->create($request->validated());

            // Registrar atividade
            $this->atividadeService->registrarProdutor($produtor, 'create');

            return response()->json([
                'success' => true,
                'message' => 'Produtor rural criado com sucesso',
                'data' => $produtor->load('propriedades')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar produtor rural',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $produtor = $this->produtorService->findById($id);

            if (!$produtor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produtor rural não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $produtor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtor rural',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateProdutorRuralRequest $request, int $id): JsonResponse
    {
        try {
            $produtor = ProdutorRural::find($id);

            if (!$produtor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produtor rural não encontrado'
                ], 404);
            }

            $this->produtorService->update($produtor, $request->validated());

            // Registrar atividade
            $this->atividadeService->registrarProdutor($produtor, 'update');

            return response()->json([
                'success' => true,
                'message' => 'Produtor rural atualizado com sucesso',
                'data' => $produtor->fresh()->load('propriedades')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar produtor rural',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $produtor = ProdutorRural::find($id);

            if (!$produtor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produtor rural não encontrado'
                ], 404);
            }

            // Registrar atividade antes de excluir
            $this->atividadeService->registrarProdutor($produtor, 'delete');

            $this->produtorService->delete($produtor);

            return response()->json([
                'success' => true,
                'message' => 'Produtor rural excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir produtor rural',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->get('q', '');

            if (empty($term)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Termo de busca é obrigatório'
                ], 400);
            }

            $produtores = $this->produtorService->search($term);

            return response()->json([
                'success' => true,
                'data' => $produtores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na busca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function propriedades(int $id): JsonResponse
    {
        try {
            $propriedades = $this->produtorService->getPropriedadesByProdutor($id);

            return response()->json([
                'success' => true,
                'data' => $propriedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedades',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
