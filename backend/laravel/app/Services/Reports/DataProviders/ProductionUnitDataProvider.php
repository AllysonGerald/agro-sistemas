<?php

namespace App\Services\Reports\DataProviders;

use App\Services\Reports\Contracts\ReportDataProviderInterface;
use App\Services\UnidadeProducaoService;
use App\Services\CacheService;
use Illuminate\Http\Request;

class ProductionUnitDataProvider implements ReportDataProviderInterface
{
    public function __construct(
        private UnidadeProducaoService $unidadeService
    ) {}

    public function getData($request): array
    {
        $filters = is_array($request) ? $request : $request->all();
        $cacheKey = CacheService::generateKey('crop_report', 'data', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            // Buscar unidades de produção com suas propriedades e produtores
            $unidades = \App\Models\UnidadeProducao::with(['propriedade.produtor'])
                ->when(isset($filters['search']), function ($query) use ($filters) {
                    $search = $filters['search'];
                    $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
                    return $query->where(function ($q) use ($search, $operator) {
                        $q->where('nome_cultura', $operator, "%{$search}%")
                          ->orWhereHas('propriedade', function ($propQuery) use ($search, $operator) {
                              $propQuery->where('nome', $operator, "%{$search}%")
                                       ->orWhere('municipio', $operator, "%{$search}%");
                          });
                    });
                })
                ->orderBy('nome_cultura')
                ->limit(1000)
                ->get()
                ->toArray();

            return $unidades;
        }, 'medium');
    }

    public function applyFilters(array $data, array $filters): array
    {
        if (empty($filters)) {
            return $data;
        }

        return collect($data)->filter(function ($item) use ($filters) {
            foreach ($filters as $key => $value) {
                if (isset($item[$key]) && $item[$key] != $value) {
                    return false;
                }
            }
            return true;
        })->values()->toArray();
    }

    public function formatData(array $data): array
    {
        return collect($data)->map(function ($item) {
            // Processar coordenadas geográficas
            $coordenadas = $item['coordenadas_geograficas'] ?? null;
            $latitude = '';
            $longitude = '';

            if ($coordenadas) {
                if (is_string($coordenadas)) {
                    $coords = json_decode($coordenadas, true);
                    $latitude = $coords['lat'] ?? $coords['latitude'] ?? '';
                    $longitude = $coords['lng'] ?? $coords['longitude'] ?? '';
                } elseif (is_array($coordenadas)) {
                    $latitude = $coordenadas['lat'] ?? $coordenadas['latitude'] ?? '';
                    $longitude = $coordenadas['lng'] ?? $coordenadas['longitude'] ?? '';
                }
            }

            return [
                'cultura' => $this->getCulturaLabel($item['nome_cultura'] ?? $item['cultura'] ?? ''),
                'area_ha' => number_format($item['area_total_ha'] ?? $item['area_total'] ?? 0, 2, ',', '.'),
                'coordenadas' => $latitude && $longitude ? "{$latitude}, {$longitude}" : '-',
                'propriedade' => $item['propriedade']['nome'] ?? '',
                'municipio' => $item['propriedade']['municipio'] ?? '',
                'data_cadastro' => isset($item['created_at']) ? date('d/m/Y', strtotime($item['created_at'])) : '',
            ];
        })->toArray();
    }

    public function getMetadata(array $data): array
    {
        return [
            'total_culturas' => count($data),
            'total_hectares' => collect($data)->sum('area_total_ha') ?: collect($data)->sum('area_total'),
            'culturas_unicas' => collect($data)->pluck('nome_cultura')->unique()->count(),
        ];
    }

    private function getCulturaLabel(string $cultura): string
    {
        $culturas = [
            'soja' => 'Soja',
            'milho' => 'Milho',
            'cafe' => 'Café',
            'cana_acucar' => 'Cana-de-açúcar',
            'algodao' => 'Algodão',
            'arroz' => 'Arroz',
            'feijao' => 'Feijão',
            'trigo' => 'Trigo',
            'batata_inglesa' => 'Batata Inglesa',
            'melancia_crimson_sweet' => 'Melancia Crimson Sweet',
            'capim_santo' => 'Capim Santo',
            'hortela' => 'Hortelã',
            'leucena' => 'Leucena',
            'limao' => 'Limão',
            'mandioca' => 'Mandioca',
            'caju' => 'Caju',
            'pitanga' => 'Pitanga',
        ];

        // Se não estiver no mapeamento, remover underscores e capitalizar
        if (!isset($culturas[$cultura])) {
            return ucwords(str_replace('_', ' ', $cultura));
        }

        return $culturas[$cultura];
    }

    public function getDataByPropriedadeNome($request): array
    {
        $filters = is_array($request) ? $request : $request->all();
        $cacheKey = CacheService::generateKey('crop_report', 'by_propriedade_nome', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            $query = \App\Models\UnidadeProducao::with(['propriedade.produtor']);

            // Aplicar filtros de busca se existirem
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $normalizedSearch = $this->normalizeString($searchTerm);
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                $query->where(function ($q) use ($searchTerm, $normalizedSearch, $operator) {
                    $q->where('nome_cultura', $operator, "%{$searchTerm}%")
                      ->orWhereHas('propriedade', function ($propQuery) use ($searchTerm, $normalizedSearch, $operator) {
                          $propQuery->where('nome', $operator, "%{$searchTerm}%")
                                   ->orWhere('municipio', $operator, "%{$searchTerm}%");
                      });
                });
            }

            // Agrupar por nome da propriedade
            $dados = $query->selectRaw('
                propriedades.nome as propriedade_nome,
                propriedades.municipio,
                propriedades.uf,
                COUNT(unidades_producao.id) as total_unidades,
                SUM(unidades_producao.area_total_ha) as total_hectares,
                COUNT(DISTINCT unidades_producao.nome_cultura) as total_culturas,
                COUNT(DISTINCT propriedades.id) as total_propriedades,
                STRING_AGG(DISTINCT unidades_producao.nome_cultura, \', \') as culturas_lista
            ')
            ->join('propriedades', 'unidades_producao.propriedade_id', '=', 'propriedades.id')
            ->groupBy(['propriedades.nome', 'propriedades.municipio', 'propriedades.uf'])
            ->orderBy('total_hectares', 'desc')
            ->orderBy('propriedades.nome')
            ->get()
            ->toArray();

            return $dados;
        }, 'medium');
    }

    public function formatDataByPropriedadeNome(array $data): array
    {
        return collect($data)->map(function ($item) {
            return [
                'propriedade_nome' => $item['propriedade_nome'] ?? '',
                'municipio' => $item['municipio'] ?? '',
                'uf' => $item['uf'] ?? '',
                'total_hectares' => number_format((float) ($item['total_hectares'] ?? 0), 2, ',', '.'),
                'total_unidades' => (int) ($item['total_unidades'] ?? 0),
                'total_culturas' => (int) ($item['total_culturas'] ?? 0),
                'culturas_lista' => $item['culturas_lista'] ?? '',
            ];
        })->toArray();
    }

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
