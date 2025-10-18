<?php

namespace App\Services;

use App\Models\Propriedade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PropriedadeService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Propriedade::query()->with(['produtor', 'unidadesProducao', 'rebanhos']);

        // Busca geral em múltiplos campos
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $normalizedSearch = $this->normalizeString($searchTerm);

            $query->where(function ($q) use ($searchTerm, $normalizedSearch) {
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                // Busca normal
                $q->where('nome', $operator, "%{$searchTerm}%")
                  ->orWhere('municipio', $operator, "%{$searchTerm}%")
                  ->orWhere('uf', $operator, "%{$searchTerm}%")
                  ->orWhere('inscricao_estadual', $operator, "%{$searchTerm}%")

                  // Busca normalizada (sem acentos)
                  // ->orWhereRaw("unaccent(nome) ILIKE ?", ["%{$normalizedSearch}%"])
                  // ->orWhereRaw("unaccent(municipio) ILIKE ?", ["%{$normalizedSearch}%"])
                  ->orWhereHas('produtor', function ($subQ) use ($searchTerm, $normalizedSearch, $operator) {
                      $subQ->where('nome', $operator, "%{$searchTerm}%");
                           // ->orWhereRaw("unaccent(nome) ILIKE ?", ["%{$normalizedSearch}%"]);
                  });
            });
        }

        if (!empty($filters['nome'])) {
            $query->where('nome', 'ILIKE', "%{$filters['nome']}%");
        }

        if (!empty($filters['municipio'])) {
            $query->byMunicipio($filters['municipio']);
        }

        if (!empty($filters['uf'])) {
            $query->byUf($filters['uf']);
        }

        if (!empty($filters['produtor_id'])) {
            $query->byProdutor($filters['produtor_id']);
        }

        if (!empty($filters['area_min'])) {
            $query->where('area_total', '>=', $filters['area_min']);
        }

        if (!empty($filters['area_max'])) {
            $query->where('area_total', '<=', $filters['area_max']);
        }

        return $query->orderBy('nome')
                    ->paginate($perPage);
    }

    public function findById(int $id): ?Propriedade
    {
        return Propriedade::with(['produtor', 'unidadesProducao', 'rebanhos'])
                         ->find($id);
    }

    public function create(array $data): Propriedade
    {
        return Propriedade::create($data);
    }

    public function update(Propriedade $propriedade, array $data): bool
    {
        return $propriedade->update($data);
    }

    public function delete(Propriedade $propriedade): bool
    {
        return $propriedade->delete();
    }

    public function getByMunicipio(): Collection
    {
        return Propriedade::selectRaw('municipio, uf, COUNT(*) as total')
                         ->groupBy(['municipio', 'uf'])
                         ->orderBy('total', 'desc')
                         ->get();
    }

    public function getByProdutor(int $produtorId): Collection
    {
        return Propriedade::with(['unidadesProducao', 'rebanhos'])
                         ->byProdutor($produtorId)
                         ->get();
    }

    public function getTotalAreaByMunicipio(): Collection
    {
        return Propriedade::selectRaw('municipio, uf, SUM(area_total) as area_total')
                         ->groupBy(['municipio', 'uf'])
                         ->orderBy('area_total', 'desc')
                         ->get();
    }

    public function exportData(array $filters = []): Collection
    {
        $query = Propriedade::query()->with(['produtor', 'unidadesProducao', 'rebanhos']);

        if (!empty($filters['municipio'])) {
            $query->byMunicipio($filters['municipio']);
        }

        if (!empty($filters['uf'])) {
            $query->byUf($filters['uf']);
        }

        if (!empty($filters['produtor_id'])) {
            $query->byProdutor($filters['produtor_id']);
        }

        return $query->orderBy('municipio')
                    ->orderBy('nome')
                    ->get();
    }

    public function validateAreaConsistency(int $propriedadeId): array
    {
        $propriedade = $this->findById($propriedadeId);

        if (!$propriedade) {
            return ['valid' => false, 'message' => 'Propriedade não encontrada'];
        }

        $areaUnidades = $propriedade->unidadesProducao->sum('area_total_ha');

        if ($areaUnidades > $propriedade->area_total) {
            return [
                'valid' => false,
                'message' => 'A soma das áreas das unidades de produção excede a área total da propriedade'
            ];
        }

        return ['valid' => true];
    }

    public function getPropriedadesByUf(): array
    {
        return Propriedade::selectRaw('uf, COUNT(*) as total')
                         ->groupBy('uf')
                         ->orderBy('total', 'desc')
                         ->pluck('total', 'uf')
                         ->toArray();
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
