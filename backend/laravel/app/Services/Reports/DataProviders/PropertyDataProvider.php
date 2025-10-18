<?php

namespace App\Services\Reports\DataProviders;

use App\Services\Reports\Contracts\ReportDataProviderInterface;
use App\Services\PropriedadeService;
use App\Services\CacheService;
use Illuminate\Http\Request;

class PropertyDataProvider implements ReportDataProviderInterface
{
    public function __construct(
        private PropriedadeService $propriedadeService
    ) {}

    public function getData($request): array
    {
        $filters = is_array($request) ? $request : $request->all();
        $cacheKey = CacheService::generateKey('property_report', 'data', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            // Buscar propriedades com seus produtores, unidades de produção e rebanhos
            $propriedades = \App\Models\Propriedade::with(['produtor', 'unidadesProducao', 'rebanhos'])
                ->when(isset($filters['search']), function ($query) use ($filters) {
                    $search = $filters['search'];
                    $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
                    return $query->where(function ($q) use ($search, $operator) {
                        $q->where('nome', $operator, "%{$search}%")
                          ->orWhere('municipio', $operator, "%{$search}%")
                          ->orWhere('uf', $operator, "%{$search}%")
                          ->orWhereHas('produtor', function ($prodQuery) use ($search, $operator) {
                              $prodQuery->where('nome', $operator, "%{$search}%");
                          });
                    });
                })
                ->orderBy('nome')
                ->limit(1000)
                ->get()
                ->toArray();

            return $propriedades;
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
            return [
                'nome_propriedade' => $item['nome'] ?? '',
                'municipio' => $item['municipio'] ?? '',
                'uf' => $item['uf'] ?? '',
                'inscricao_estadual' => $item['inscricao_estadual'] ?? '',
                'area_total' => number_format($item['area_total'] ?? 0, 2, ',', '.'),
                'produtor_nome' => $item['produtor']['nome'] ?? 'Sem produtor',
                'produtor_cpf_cnpj' => $item['produtor']['cpf_cnpj'] ?? '',
                'produtor_email' => $item['produtor']['email'] ?? '',
                'produtor_telefone' => $item['produtor']['telefone'] ?? '',
                'produtor_endereco' => $item['produtor']['endereco'] ?? '',
                'total_unidades' => count($item['unidades_producao'] ?? []),
                'total_rebanhos' => collect($item['rebanhos'] ?? [])->sum('quantidade'),
            ];
        })->toArray();
    }

    public function getMetadata(array $data): array
    {
        return [
            'total_municipios' => count($data),
            'total_propriedades' => collect($data)->sum('total'),
            'total_area' => collect($data)->sum('area_total'),
            'total_produtores' => collect($data)->sum('produtores'),
        ];
    }

    public function getDataByMunicipio($request): array
    {
        $filters = is_array($request) ? $request : $request->all();
        $cacheKey = CacheService::generateKey('property_report', 'by_municipio', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            $query = \App\Models\Propriedade::query();

            // Aplicar filtros de busca se existirem
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $normalizedSearch = $this->normalizeString($searchTerm);
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                $query->where(function ($q) use ($searchTerm, $normalizedSearch, $operator) {
                    $q->where('nome', $operator, "%{$searchTerm}%")
                      ->orWhere('municipio', $operator, "%{$searchTerm}%")
                      ->orWhere('uf', $operator, "%{$searchTerm}%")
                      ->orWhereHas('produtor', function ($prodQuery) use ($searchTerm, $normalizedSearch, $operator) {
                          $prodQuery->where('nome', $operator, "%{$searchTerm}%")
                                   ->orWhere('cpf_cnpj', $operator, "%{$searchTerm}%")
                                   ->orWhere('email', $operator, "%{$searchTerm}%");
                      });
                });
            }

            // Agrupar por município e UF
            $dados = $query->selectRaw('
                municipio,
                uf,
                COUNT(DISTINCT propriedades.id) as total_propriedades,
                COALESCE(SUM(area_total), 0) as total_area,
                COUNT(DISTINCT produtor_id) as total_produtores,
                COALESCE(SUM(CASE WHEN unidades_producao.id IS NOT NULL THEN 1 ELSE 0 END), 0) as total_unidades,
                COALESCE(SUM(rebanhos.quantidade), 0) as total_animais
            ')
            ->leftJoin('unidades_producao', 'propriedades.id', '=', 'unidades_producao.propriedade_id')
            ->leftJoin('rebanhos', 'propriedades.id', '=', 'rebanhos.propriedade_id')
            ->groupBy(['municipio', 'uf'])
            ->orderBy('total_propriedades', 'desc')
            ->orderBy('municipio')
            ->get()
            ->toArray();

            return $dados;
        }, 'medium');
    }

    public function formatDataByMunicipio(array $data): array
    {
        return collect($data)->map(function ($item) {
            return [
                'municipio' => $item['municipio'] ?? '',
                'uf' => $item['uf'] ?? '',
                'total_propriedades' => (int) ($item['total_propriedades'] ?? 0),
                'total_area' => number_format((float) ($item['total_area'] ?? 0), 2, ',', '.'),
                'total_produtores' => (int) ($item['total_produtores'] ?? 0),
                'total_unidades' => (int) ($item['total_unidades'] ?? 0),
                'total_animais' => (int) ($item['total_animais'] ?? 0),
            ];
        })->toArray();
    }

    /**
     * Normaliza uma string removendo acentos para busca
     */
    private function normalizeString(string $string): string
    {
        $normalizeChars = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c',
            'Ñ' => 'N', 'ñ' => 'n'
        ];

        return strtr($string, $normalizeChars);
    }
}
