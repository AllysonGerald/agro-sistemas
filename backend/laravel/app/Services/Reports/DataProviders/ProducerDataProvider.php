<?php

namespace App\Services\Reports\DataProviders;

use App\Services\Reports\Contracts\ReportDataProviderInterface;
use App\Services\ProdutorRuralService;
use App\Services\CacheService;
use Illuminate\Http\Request;

class ProducerDataProvider implements ReportDataProviderInterface
{
    public function __construct(
        private ProdutorRuralService $produtorService
    ) {}

    public function getData(Request $request): array
    {
        $filters = $request->all();
        $cacheKey = CacheService::generateKey('producer_report', 'data', $filters);

        return CacheService::remember($cacheKey, function () use ($filters) {
            // Buscar produtores com suas propriedades para calcular hectares
            $produtores = \App\Models\ProdutorRural::with(['propriedades'])
                ->when(isset($filters['search']), function ($query) use ($filters) {
                    $search = $filters['search'];
                    $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';
                    return $query->where(function ($q) use ($search, $operator) {
                        $q->where('nome', $operator, "%{$search}%")
                          ->orWhere('email', $operator, "%{$search}%")
                          ->orWhere('cpf_cnpj', 'LIKE', "%{$search}%");
                    });
                })
                ->orderBy('nome')
                ->limit(1000)
                ->get()
                ->toArray();

            return $produtores;
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
            // Calcular total de hectares das propriedades
            $totalHectares = 0;
            if (isset($item['propriedades']) && is_array($item['propriedades'])) {
                $totalHectares = collect($item['propriedades'])->sum('area_total');
            }

            return [
                'nome' => $item['nome'] ?? '',
                'cpf_cnpj' => $this->formatCpfCnpj($item['cpf_cnpj'] ?? ''),
                'email' => $item['email'] ?? '',
                'telefone' => $this->formatTelefone($item['telefone'] ?? ''),
                'endereco' => $item['endereco'] ?? '',
                'total_propriedades' => count($item['propriedades'] ?? []),
                'total_hectares' => number_format($totalHectares, 2, ',', '.'),
                'data_cadastro' => isset($item['created_at']) ? date('d/m/Y', strtotime($item['created_at'])) : '',
            ];
        })->toArray();
    }

    public function getMetadata(array $data): array
    {
        return [
            'total_produtores' => count($data),
            'total_propriedades' => collect($data)->sum('propriedades_count'),
            'total_hectares' => collect($data)->sum('total_hectares'),
        ];
    }

    private function formatCpfCnpj(string $cpfCnpj): string
    {
        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpj);

        if (strlen($cpfCnpj) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfCnpj);
        } elseif (strlen($cpfCnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cpfCnpj);
        }

        return $cpfCnpj;
    }

    private function formatTelefone(string $telefone): string
    {
        $telefone = preg_replace('/\D/', '', $telefone);

        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }

        return $telefone;
    }
}
