<?php

namespace App\Services;

use App\Models\Rebanho;
use App\Enums\AnimalSpeciesEnum;
use App\Enums\LivestockPurposeEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class RebanhoService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Rebanho::query()->with(['propriedade.produtor']);

        // Busca geral em múltiplos campos
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $normalizedSearch = $this->normalizeString($searchTerm);

            $query->where(function ($q) use ($searchTerm, $normalizedSearch) {
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                // Busca normal
                $q->where('especie', $operator, "%{$searchTerm}%")
                  ->orWhere('finalidade', $operator, "%{$searchTerm}%")

                  // Busca normalizada (sem acentos)
                  // ->orWhereRaw("unaccent(especie) ILIKE ?", ["%{$normalizedSearch}%"])
                  // ->orWhereRaw("unaccent(finalidade) ILIKE ?", ["%{$normalizedSearch}%"])
                  ->orWhereHas('propriedade', function ($subQ) use ($searchTerm, $normalizedSearch, $operator) {
                      $subQ->where('nome', $operator, "%{$searchTerm}%")
                           ->orWhere('municipio', $operator, "%{$searchTerm}%")
                           ->orWhere('uf', $operator, "%{$searchTerm}%")
                           // ->orWhereRaw("unaccent(nome) ILIKE ?", ["%{$normalizedSearch}%"])
                           // ->orWhereRaw("unaccent(municipio) ILIKE ?", ["%{$normalizedSearch}%"])
                           ->orWhereHas('produtor', function ($prodQ) use ($searchTerm, $normalizedSearch, $operator) {
                               $prodQ->where('nome', $operator, "%{$searchTerm}%");
                                    // ->orWhereRaw("unaccent(nome) ILIKE ?", ["%{$normalizedSearch}%"]);
                           });
                  });
            });
        }

        if (!empty($filters['especie'])) {
            $query->byEspecie($filters['especie']);
        }

        if (!empty($filters['finalidade'])) {
            $query->byFinalidade($filters['finalidade']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->byPropriedade($filters['propriedade_id']);
        }

        if (!empty($filters['quantidade_min'])) {
            $query->withMinQuantidade($filters['quantidade_min']);
        }

        if (!empty($filters['municipio'])) {
            $query->whereHas('propriedade', function ($q) use ($filters) {
                $q->byMunicipio($filters['municipio']);
            });
        }

        if (!empty($filters['produtor_id'])) {
            $query->whereHas('propriedade', function ($q) use ($filters) {
                $q->where('produtor_id', $filters['produtor_id']);
            });
        }

        return $query->orderBy('especie')
                    ->orderBy('quantidade', 'desc')
                    ->paginate($perPage);
    }

    public function findById(int $id): ?Rebanho
    {
        return Rebanho::with(['propriedade.produtor'])
                     ->find($id);
    }

    public function create(array $data): Rebanho
    {
        $data['data_atualizacao'] = $data['data_atualizacao'] ?? now();
        return Rebanho::create($data);
    }

    public function update(Rebanho $rebanho, array $data): bool
    {
        $data['data_atualizacao'] = now();
        return $rebanho->update($data);
    }

    public function delete(Rebanho $rebanho): bool
    {
        return $rebanho->delete();
    }

    public function getByPropriedade(int $propriedadeId): Collection
    {
        return Rebanho::byPropriedade($propriedadeId)
                     ->orderBy('especie')
                     ->orderBy('quantidade', 'desc')
                     ->get();
    }

    public function getByProdutor(int $produtorId): Collection
    {
        return Rebanho::query()
                     ->whereHas('propriedade', function ($q) use ($produtorId) {
                         $q->where('produtor_id', $produtorId);
                     })
                     ->with(['propriedade'])
                     ->orderBy('especie')
                     ->get();
    }

    public function getQuantidadeByEspecie(): Collection
    {
        return Rebanho::selectRaw('especie, SUM(quantidade) as total_animais')
                     ->groupBy('especie')
                     ->orderBy('total_animais', 'desc')
                     ->get()
                     ->map(function ($item) {
                         $enum = AnimalSpeciesEnum::tryFrom($item->especie);
                         $item->especie_label = $enum ? $enum->label() : $item->especie;
                         return $item;
                     });
    }

    public function getQuantidadeByFinalidade(): Collection
    {
        return Rebanho::selectRaw('finalidade, SUM(quantidade) as total_animais')
                     ->groupBy('finalidade')
                     ->orderBy('total_animais', 'desc')
                     ->get()
                     ->map(function ($item) {
                         $enum = LivestockPurposeEnum::tryFrom($item->finalidade);
                         $item->finalidade_label = $enum ? $enum->label() : $item->finalidade;
                         return $item;
                     });
    }

    public function getRebanhosByMunicipio(): Collection
    {
        return Rebanho::query()
                     ->join('propriedades', 'rebanhos.propriedade_id', '=', 'propriedades.id')
                     ->selectRaw('propriedades.municipio, propriedades.uf, rebanhos.especie, SUM(rebanhos.quantidade) as total_animais')
                     ->groupBy(['propriedades.municipio', 'propriedades.uf', 'rebanhos.especie'])
                     ->orderBy('total_animais', 'desc')
                     ->get();
    }

    public function getEspecieStats(): array
    {
        $stats = [];

        foreach (AnimalSpeciesEnum::cases() as $especie) {
            $total = Rebanho::byEspecie($especie->value)
                           ->sum('quantidade');

            $rebanhos = Rebanho::byEspecie($especie->value)
                              ->count();

            $propriedades = Rebanho::byEspecie($especie->value)
                                  ->distinct('propriedade_id')
                                  ->count('propriedade_id');

            $stats[] = [
                'especie' => $especie->value,
                'especie_label' => $especie->label(),
                'total_animais' => $total,
                'total_rebanhos' => $rebanhos,
                'total_propriedades' => $propriedades,
                'media_por_rebanho' => $rebanhos > 0 ? $total / $rebanhos : 0
            ];
        }

        return collect($stats)->sortByDesc('total_animais')->values()->toArray();
    }

    public function exportDataByProdutor(int $produtorId): Collection
    {
        return $this->getByProdutor($produtorId)
                   ->map(function ($rebanho) {
                       return [
                           'propriedade' => $rebanho->propriedade->nome,
                           'municipio' => $rebanho->propriedade->municipio,
                           'uf' => $rebanho->propriedade->uf,
                           'especie' => $rebanho->species_label,
                           'quantidade' => $rebanho->quantidade,
                           'finalidade' => $rebanho->purpose_label,
                           'data_atualizacao' => $rebanho->data_atualizacao->format('d/m/Y')
                       ];
                   });
    }

    public function getRebanhoDesatualizado(int $days = 90): Collection
    {
        return Rebanho::query()
                     ->with(['propriedade.produtor'])
                     ->where('data_atualizacao', '<', now()->subDays($days))
                     ->orderBy('data_atualizacao')
                     ->get();
    }

    public function getTotalAnimaisByProdutor(): Collection
    {
        return Rebanho::query()
                     ->join('propriedades', 'rebanhos.propriedade_id', '=', 'propriedades.id')
                     ->join('produtores_rurais', 'propriedades.produtor_id', '=', 'produtores_rurais.id')
                     ->selectRaw('produtores_rurais.id, produtores_rurais.nome, SUM(rebanhos.quantidade) as total_animais')
                     ->groupBy(['produtores_rurais.id', 'produtores_rurais.nome'])
                     ->orderBy('total_animais', 'desc')
                     ->get();
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
