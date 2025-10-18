<?php

namespace App\Services\Reports\DataProviders;

use App\Services\Reports\Contracts\ReportDataProviderInterface;
use App\Services\RebanhoService;
use App\Services\CacheService;
use Illuminate\Http\Request;

class AnimalDataProvider implements ReportDataProviderInterface
{
    public function __construct(
        private RebanhoService $rebanhoService
    ) {}

    public function getData($request): array
    {
        $filters = is_array($request) ? $request : $request->all();
        $cacheKey = CacheService::generateKey('animal_report', 'data', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            // Buscar rebanhos com suas propriedades e produtores
            $rebanhos = \App\Models\Rebanho::with(['propriedade.produtor'])
                ->when(isset($filters['search']), function ($query) use ($filters) {
                    $search = $filters['search'];
                    $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
                    return $query->where(function ($q) use ($search, $operator) {
                        $q->where('especie', $operator, "%{$search}%")
                          ->orWhere('finalidade', $operator, "%{$search}%")
                          ->orWhereHas('propriedade', function ($propQuery) use ($search, $operator) {
                              $propQuery->where('nome', $operator, "%{$search}%")
                                       ->orWhere('municipio', $operator, "%{$search}%");
                          });
                    });
                })
                ->orderBy('especie')
                ->limit(1000)
                ->get()
                ->toArray();

            return $rebanhos;
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
                'especie' => $this->getEspecieLabel($item['especie'] ?? ''),
                'quantidade' => $item['quantidade'] ?? $item['total_animais'] ?? 0,
                'finalidade' => $this->getFinalidadeLabel($item['finalidade'] ?? ''),
                'ultima_atualizacao' => isset($item['updated_at']) ? date('d/m/Y', strtotime($item['updated_at'])) : '',
                'propriedade' => $item['propriedade']['nome'] ?? '',
                'municipio' => $item['propriedade']['municipio'] ?? '',
                'uf' => $item['propriedade']['uf'] ?? '',
                'produtor' => $item['propriedade']['produtor']['nome'] ?? 'Sem produtor',
                'area_total' => isset($item['propriedade']['area_total']) ? number_format($item['propriedade']['area_total'], 2, ',', '.') . ' ha' : '-',
            ];
        })->toArray();
    }

    public function getMetadata(array $data): array
    {
        return [
            'total_especies' => count($data),
            'total_animais' => collect($data)->sum('quantidade') ?: collect($data)->sum('total_animais'),
            'especies_unicas' => collect($data)->pluck('especie')->unique()->count(),
        ];
    }

    private function getEspecieLabel(string $especie): string
    {
        $especies = [
            'bovinos' => 'Bovinos',
            'suinos' => 'Suínos',
            'caprinos' => 'Caprinos',
            'ovinos' => 'Ovinos',
            'aves' => 'Aves',
        ];

        return $especies[$especie] ?? ucfirst($especie);
    }

    private function getFinalidadeLabel(string $finalidade): string
    {
        $finalidades = [
            'corte' => 'Corte',
            'leite' => 'Leite',
            'reproducao' => 'Reprodução',
            'postura' => 'Postura',
        ];

        return $finalidades[$finalidade] ?? ucfirst($finalidade);
    }

    public function getDataByEspecie($request): array
    {
        $filters = is_array($request) ? $request : $request->all();
        $cacheKey = CacheService::generateKey('animal_report', 'by_especie', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            $query = \App\Models\Rebanho::query();

            // Aplicar filtros de busca se existirem
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $normalizedSearch = $this->normalizeString($searchTerm);
                $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

                $query->where(function ($q) use ($searchTerm, $normalizedSearch, $operator) {
                    $q->where('especie', $operator, "%{$searchTerm}%")
                      ->orWhere('finalidade', $operator, "%{$searchTerm}%")
                      ->orWhereHas('propriedade', function ($propQuery) use ($searchTerm, $normalizedSearch, $operator) {
                          $propQuery->where('nome', $operator, "%{$searchTerm}%")
                                   ->orWhere('municipio', $operator, "%{$searchTerm}%");
                      });
                });
            }

            // Agrupar por espécie
            $dados = $query->selectRaw('
                especie,
                COUNT(*) as total_rebanhos,
                SUM(quantidade) as total_animais,
                COUNT(DISTINCT propriedade_id) as total_propriedades,
                COUNT(DISTINCT CASE WHEN finalidade = \'corte\' THEN propriedade_id END) as propriedades_corte,
                COUNT(DISTINCT CASE WHEN finalidade = \'leite\' THEN propriedade_id END) as propriedades_leite,
                COUNT(DISTINCT CASE WHEN finalidade = \'reproducao\' THEN propriedade_id END) as propriedades_reproducao,
                COUNT(DISTINCT CASE WHEN finalidade = \'misto\' THEN propriedade_id END) as propriedades_misto
            ')
            ->groupBy('especie')
            ->orderBy('total_animais', 'desc')
            ->orderBy('especie')
            ->get()
            ->toArray();

            return $dados;
        }, 'medium');
    }

    public function formatDataByEspecie(array $data): array
    {
        return collect($data)->map(function ($item) {
            return [
                'especie' => $this->getEspecieLabel($item['especie'] ?? ''),
                'total_animais' => (int) ($item['total_animais'] ?? 0),
                'total_rebanhos' => (int) ($item['total_rebanhos'] ?? 0),
                'total_propriedades' => (int) ($item['total_propriedades'] ?? 0),
                'propriedades_corte' => (int) ($item['propriedades_corte'] ?? 0),
                'propriedades_leite' => (int) ($item['propriedades_leite'] ?? 0),
                'propriedades_reproducao' => (int) ($item['propriedades_reproducao'] ?? 0),
                'propriedades_misto' => (int) ($item['propriedades_misto'] ?? 0),
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
