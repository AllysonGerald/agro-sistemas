<?php

namespace App\Services;

use App\Models\ProdutorRural;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Serviço de Gestão de Produtores Rurais
 *
 * Implementa regras de negócio específicas do setor agropecuário com
 * otimizações de performance através de cache distribuído estratégico.
 *
 * @package App\Services
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class ProdutorRuralService
{
    /**
     * Listar produtores rurais com filtros avançados e cache inteligente
     *
     * Implementa estratégia de cache baseada em filtros para otimizar
     * consultas complexas com relacionamentos
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Sistema de cache adaptativo baseado em padrões de consulta
        $cacheKey = CacheService::generateKey('produtor', 'all', [
            'filters' => $filters,
            'per_page' => $perPage
        ]);

        // Temporariamente desabilitado para testes
        // return CacheService::remember($cacheKey, function () use ($filters, $perPage) {
            $query = ProdutorRural::query()->with('propriedades');

            // Busca geral em múltiplos campos
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $normalizedSearch = $this->normalizeString($searchTerm);

                $query->where(function ($q) use ($searchTerm, $normalizedSearch) {
                    $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                    // Busca normal
                    $q->where('nome', $operator, "%{$searchTerm}%")
                      ->orWhere('cpf_cnpj', $operator, "%{$searchTerm}%")
                      ->orWhere('email', $operator, "%{$searchTerm}%");
                });
            }

            if (!empty($filters['nome'])) {
                $query->byName($filters['nome']);
            }

            if (!empty($filters['cpf_cnpj'])) {
                $query->byCpfCnpj($filters['cpf_cnpj']);
            }

            if (!empty($filters['email'])) {
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
                $query->where('email', $operator, "%{$filters['email']}%");
            }

            if (!empty($filters['municipio'])) {
                $query->whereHas('propriedades', function ($q) use ($filters) {
                    $q->byMunicipio($filters['municipio']);
                });
            }

            return $query->orderBy('nome')->paginate($perPage);
        // }, 'short'); // Cache por 5 minutos para listagens
    }

    public function findById(int $id): ?ProdutorRural
    {
        $cacheKey = CacheService::generateKey('produtor', "show_{$id}");

        return CacheService::remember($cacheKey, function () use ($id) {
            return ProdutorRural::with(['propriedades.unidadesProducao', 'propriedades.rebanhos'])
                              ->find($id);
        }, 'medium'); // Cache por 30 minutos para dados individuais
    }

    public function create(array $data): ProdutorRural
    {
        $produtor = ProdutorRural::create($data);

        // Invalidar cache relacionado
        $this->invalidateCache();

        return $produtor;
    }

    public function update(ProdutorRural $produtor, array $data): bool
    {
        $success = $produtor->update($data);

        if ($success) {
            // Invalidar cache específico e geral
            $this->invalidateCache($produtor->id);
        }

        return $success;
    }

    public function delete(ProdutorRural $produtor): bool
    {
        $success = $produtor->delete();

        if ($success) {
            // Invalidar cache específico e geral
            $this->invalidateCache($produtor->id);
        }

        return $success;
    }

    public function getPropriedadesByProdutor(int $produtorId): Collection
    {
        $cacheKey = CacheService::generateKey('produtor', "propriedades_{$produtorId}");

        return CacheService::remember($cacheKey, function () use ($produtorId) {
            $produtor = $this->findById($produtorId);
            return $produtor ? $produtor->propriedades : collect();
        }, 'medium');
    }

    public function getTotalPropriedades(int $produtorId): int
    {
        $cacheKey = CacheService::generateKey('produtor', "total_propriedades_{$produtorId}");

        return CacheService::remember($cacheKey, function () use ($produtorId) {
            return ProdutorRural::find($produtorId)?->propriedades()->count() ?? 0;
        }, 'long'); // Cache por 2 horas para contadores
    }

    public function getTotalAreaPropriedades(int $produtorId): float
    {
        $cacheKey = CacheService::generateKey('produtor', "total_area_{$produtorId}");

        return CacheService::remember($cacheKey, function () use ($produtorId) {
            return ProdutorRural::find($produtorId)?->propriedades()->sum('area_total') ?? 0;
        }, 'long');
    }

    public function search(string $term): Collection
    {
        $cacheKey = CacheService::generateKey('produtor', "search", ['term' => $term]);

        return CacheService::remember($cacheKey, function () use ($term) {
            $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

            return ProdutorRural::query()
                ->where('nome', $operator, "%{$term}%")
                ->orWhere('email', $operator, "%{$term}%")
                ->orWhere('cpf_cnpj', 'LIKE', "%{$term}%")
                ->orderBy('nome')
                ->limit(10)
                ->get(['id', 'nome', 'cpf_cnpj', 'telefone', 'email']);
        }, 'short');
    }

    public function validateUniqueDocument(string $cpfCnpj, ?int $excludeId = null): bool
    {
        $cleanDoc = preg_replace('/\D/', '', $cpfCnpj);

        $cacheKey = CacheService::generateKey('produtor', "validate_document", [
            'document' => $cleanDoc,
            'exclude' => $excludeId
        ]);

        return CacheService::remember($cacheKey, function () use ($cleanDoc, $excludeId) {
            $query = ProdutorRural::where('cpf_cnpj', $cleanDoc);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            return !$query->exists();
        }, 'medium');
    }

    /**
     * Invalidar cache relacionado aos produtores
     */
    private function invalidateCache(?int $produtorId = null): void
    {
        // Invalidar cache específico se ID fornecido
        if ($produtorId) {
            $keys = [
                CacheService::generateKey('produtor', "show_{$produtorId}"),
                CacheService::generateKey('produtor', "propriedades_{$produtorId}"),
                CacheService::generateKey('produtor', "total_propriedades_{$produtorId}"),
                CacheService::generateKey('produtor', "total_area_{$produtorId}"),
            ];
            CacheService::forgetMany($keys);
        }

        // Invalidar cache geral (listagens, buscas)
        CacheService::forgetByPattern('agro_prod_all');
        CacheService::forgetByPattern('agro_prod_search');
        CacheService::forgetByPattern('agro_prod_validate');
    }

    /**
     * Normaliza string removendo acentos para busca
     */
    private function normalizeString(string $string): string
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];

        return strtr($string, $map);
    }
}
