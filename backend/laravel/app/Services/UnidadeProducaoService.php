<?php

namespace App\Services;

use App\Models\UnidadeProducao;
use App\Enums\CropTypeEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UnidadeProducaoService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = UnidadeProducao::query()->with(['propriedade.produtor']);

        // Busca geral em múltiplos campos
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $normalizedSearch = $this->normalizeString($searchTerm);

            $query->where(function ($q) use ($searchTerm, $normalizedSearch) {
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                // Busca normal
                $q->where('nome_cultura', $operator, "%{$searchTerm}%")
                  ->orWhere('area_total_ha', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('coordenadas_geograficas', $operator, "%{$searchTerm}%")
                  ->orWhereRaw("coordenadas_geograficas::text LIKE ?", ["%{$searchTerm}%"])

                  // Busca por cultura_label usando CASE WHEN
                  ->orWhereRaw("CASE
                    WHEN nome_cultura = 'cafe' THEN 'Café'
                    WHEN nome_cultura = 'cana_de_acucar' THEN 'Cana-de-açúcar'
                    WHEN nome_cultura = 'laranja_pera' THEN 'Laranja Pera'
                    WHEN nome_cultura = 'laranja_lima' THEN 'Laranja Lima'
                    WHEN nome_cultura = 'limao' THEN 'Limão'
                    WHEN nome_cultura = 'caju' THEN 'Caju'
                    WHEN nome_cultura = 'manga' THEN 'Manga'
                    WHEN nome_cultura = 'coco' THEN 'Coco'
                    WHEN nome_cultura = 'mamao' THEN 'Mamão'
                    WHEN nome_cultura = 'banana' THEN 'Banana'
                    WHEN nome_cultura = 'abacaxi' THEN 'Abacaxi'
                    WHEN nome_cultura = 'maracuja' THEN 'Maracujá'
                    WHEN nome_cultura = 'acerola' THEN 'Acerola'
                    WHEN nome_cultura = 'graviola' THEN 'Graviola'
                    WHEN nome_cultura = 'jaca' THEN 'Jaca'
                    WHEN nome_cultura = 'abacate' THEN 'Abacate'
                    WHEN nome_cultura = 'melancia_crimson_sweet' THEN 'Melancia Crimson Sweet'
                    WHEN nome_cultura = 'melancia' THEN 'Melancia'
                    WHEN nome_cultura = 'melao' THEN 'Melão'
                    WHEN nome_cultura = 'goiaba_paluma' THEN 'Goiaba Paluma'
                    WHEN nome_cultura = 'goiaba' THEN 'Goiaba'
                    WHEN nome_cultura = 'siriguela' THEN 'Siriguela'
                    WHEN nome_cultura = 'pitanga' THEN 'Pitanga'
                    WHEN nome_cultura = 'umbu' THEN 'Umbu'
                    WHEN nome_cultura = 'caja' THEN 'Cajá'
                    WHEN nome_cultura = 'milho' THEN 'Milho'
                    WHEN nome_cultura = 'feijao_caupi' THEN 'Feijão Caupi'
                    WHEN nome_cultura = 'feijao_comum' THEN 'Feijão Comum'
                    WHEN nome_cultura = 'soja' THEN 'Soja'
                    WHEN nome_cultura = 'arroz' THEN 'Arroz'
                    WHEN nome_cultura = 'trigo' THEN 'Trigo'
                    WHEN nome_cultura = 'sorgo' THEN 'Sorgo'
                    WHEN nome_cultura = 'girassol' THEN 'Girassol'
                    WHEN nome_cultura = 'gergelim' THEN 'Gergelim'
                    WHEN nome_cultura = 'mandioca' THEN 'Mandioca'
                    WHEN nome_cultura = 'batata_doce' THEN 'Batata Doce'
                    WHEN nome_cultura = 'inhame' THEN 'Inhame'
                    WHEN nome_cultura = 'batata_inglesa' THEN 'Batata Inglesa'
                    WHEN nome_cultura = 'tomate' THEN 'Tomate'
                    WHEN nome_cultura = 'cebola' THEN 'Cebola'
                    WHEN nome_cultura = 'cenoura' THEN 'Cenoura'
                    WHEN nome_cultura = 'alface' THEN 'Alface'
                    WHEN nome_cultura = 'repolho' THEN 'Repolho'
                    WHEN nome_cultura = 'pimentao' THEN 'Pimentão'
                    WHEN nome_cultura = 'pepino' THEN 'Pepino'
                    WHEN nome_cultura = 'quiabo' THEN 'Quiabo'
                    WHEN nome_cultura = 'berinjela' THEN 'Berinjela'
                    WHEN nome_cultura = 'abobora' THEN 'Abóbora'
                    WHEN nome_cultura = 'capim_braquiaria' THEN 'Capim Braquiária'
                    WHEN nome_cultura = 'capim_tanzania' THEN 'Capim Tanzânia'
                    WHEN nome_cultura = 'capim_elefante' THEN 'Capim Elefante'
                    WHEN nome_cultura = 'alfafa' THEN 'Alfafa'
                    WHEN nome_cultura = 'palma_forrageira' THEN 'Palma Forrageira'
                    WHEN nome_cultura = 'leucena' THEN 'Leucena'
                    WHEN nome_cultura = 'algodao' THEN 'Algodão'
                    WHEN nome_cultura = 'sisal' THEN 'Sisal'
                    WHEN nome_cultura = 'hortela' THEN 'Hortelã'
                    WHEN nome_cultura = 'manjericao' THEN 'Manjericão'
                    WHEN nome_cultura = 'capim_santo' THEN 'Capim Santo'
                    WHEN nome_cultura = 'camomila' THEN 'Camomila'
                    ELSE nome_cultura
                  END {$operator} ?", ["%{$searchTerm}%"])

                  // Busca normalizada (sem acentos)
                  // ->orWhereRaw("unaccent(nome_cultura) ILIKE ?", ["%{$normalizedSearch}%"])
                  ->orWhereHas('propriedade', function ($subQ) use ($searchTerm, $normalizedSearch, $operator) {
                      $subQ->where('nome', $operator, "%{$searchTerm}%")
                           ->orWhere('municipio', $operator, "%{$searchTerm}%");
                           // ->orWhereRaw("unaccent(nome) ILIKE ?", ["%{$normalizedSearch}%"])
                           // ->orWhereRaw("unaccent(municipio) ILIKE ?", ["%{$normalizedSearch}%"]);
                  });
            });
        }

        if (!empty($filters['nome_cultura'])) {
            $query->byCultura($filters['nome_cultura']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->byPropriedade($filters['propriedade_id']);
        }

        if (!empty($filters['area_min'])) {
            $query->withMinArea($filters['area_min']);
        }

        if (!empty($filters['municipio'])) {
            $query->whereHas('propriedade', function ($q) use ($filters) {
                $q->byMunicipio($filters['municipio']);
            });
        }

        $unidades = $query->orderBy('nome_cultura')
                    ->orderBy('area_total_ha', 'desc')
                    ->paginate($perPage);

        // Adicionar cultura_label para cada unidade
        $unidades->getCollection()->transform(function ($unidade) {
            $enum = CropTypeEnum::tryFrom($unidade->nome_cultura);
            $unidade->cultura_label = $enum ? $enum->label() : $unidade->nome_cultura;
            return $unidade;
        });

        return $unidades;
    }

    public function findById(int $id): ?UnidadeProducao
    {
        $unidade = UnidadeProducao::with(['propriedade.produtor'])
                             ->find($id);

        if ($unidade) {
            $enum = CropTypeEnum::tryFrom($unidade->nome_cultura);
            $unidade->cultura_label = $enum ? $enum->label() : $unidade->nome_cultura;
        }

        return $unidade;
    }

    public function create(array $data): UnidadeProducao
    {
        return UnidadeProducao::create($data);
    }

    public function update(UnidadeProducao $unidade, array $data): bool
    {
        return $unidade->update($data);
    }

    public function delete(UnidadeProducao $unidade): bool
    {
        return $unidade->delete();
    }

    public function getByPropriedade(int $propriedadeId): Collection
    {
        return UnidadeProducao::byPropriedade($propriedadeId)
                             ->orderBy('area_total_ha', 'desc')
                             ->get();
    }

    public function getAreaByCultura(): Collection
    {
        return UnidadeProducao::selectRaw('nome_cultura, SUM(area_total_ha) as area_total')
                             ->groupBy('nome_cultura')
                             ->orderBy('area_total', 'desc')
                             ->get()
                             ->map(function ($item) {
                                 $enum = CropTypeEnum::tryFrom($item->nome_cultura);
                                 $item->cultura_label = $enum ? $enum->label() : $item->nome_cultura;
                                 return $item;
                             });
    }

    public function getAreaByMunicipio(): Collection
    {
        return UnidadeProducao::query()
                             ->join('propriedades', 'unidades_producao.propriedade_id', '=', 'propriedades.id')
                             ->selectRaw('propriedades.municipio, propriedades.uf, unidades_producao.nome_cultura, SUM(unidades_producao.area_total_ha) as area_total')
                             ->groupBy(['propriedades.municipio', 'propriedades.uf', 'unidades_producao.nome_cultura'])
                             ->orderBy('area_total', 'desc')
                             ->get();
    }

    public function getCulturaStats(): array
    {
        $stats = [];

        foreach (CropTypeEnum::cases() as $cultura) {
            $area = UnidadeProducao::byCultura($cultura->value)
                                  ->sum('area_total_ha');

            $unidades = UnidadeProducao::byCultura($cultura->value)
                                      ->count();

            $stats[] = [
                'cultura' => $cultura->value,
                'cultura_label' => $cultura->label(),
                'area_total' => $area,
                'total_unidades' => $unidades,
                'area_media' => $unidades > 0 ? $area / $unidades : 0
            ];
        }

        return collect($stats)->sortByDesc('area_total')->values()->toArray();
    }

    public function validateAreaLimits(array $data, ?int $excludeId = null): array
    {
        $propriedade = \App\Models\Propriedade::find($data['propriedade_id']);

        if (!$propriedade) {
            return ['valid' => false, 'message' => 'Propriedade não encontrada'];
        }

        $query = UnidadeProducao::where('propriedade_id', $data['propriedade_id']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $areaUsada = $query->sum('area_total_ha');
        $novaArea = $data['area_total_ha'];
        $areaTotal = $areaUsada + $novaArea;

        if ($areaTotal > $propriedade->area_total) {
            $areaDisponivel = $propriedade->area_total - $areaUsada;
            return [
                'valid' => false,
                'message' => "Área excede o limite da propriedade. Área disponível: {$areaDisponivel} ha"
            ];
        }

        return ['valid' => true];
    }

    public function getUnidadesByProdutor(int $produtorId): Collection
    {
        return UnidadeProducao::query()
                             ->whereHas('propriedade', function ($q) use ($produtorId) {
                                 $q->where('produtor_id', $produtorId);
                             })
                             ->with(['propriedade'])
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
