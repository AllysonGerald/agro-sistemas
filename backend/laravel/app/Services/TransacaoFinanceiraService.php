<?php

namespace App\Services;

use App\Models\TransacaoFinanceira;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransacaoFinanceiraService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TransacaoFinanceira::query()->with(['categoria', 'animal', 'lote', 'propriedade']);

        // Busca geral
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query->where(function ($q) use ($searchTerm, $operator) {
                $q->where('descricao', $operator, "%{$searchTerm}%")
                  ->orWhereHas('categoria', function ($subQ) use ($searchTerm, $operator) {
                      $subQ->where('nome', $operator, "%{$searchTerm}%");
                  });
            });
        }

        // Filtros específicos
        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['categoria_id'])) {
            $query->where('categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['animal_id'])) {
            $query->where('animal_id', $filters['animal_id']);
        }

        if (!empty($filters['lote_id'])) {
            $query->where('lote_id', $filters['lote_id']);
        }

        // Filtro por período
        if (!empty($filters['data_inicio'])) {
            $query->where('data', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $query->where('data', '<=', $filters['data_fim']);
        }

        // Filtro por valor
        if (!empty($filters['valor_min'])) {
            $query->where('valor', '>=', $filters['valor_min']);
        }

        if (!empty($filters['valor_max'])) {
            $query->where('valor', '<=', $filters['valor_max']);
        }

        return $query->orderBy('data', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    public function findById(int $id): ?TransacaoFinanceira
    {
        return TransacaoFinanceira::with(['categoria', 'animal', 'lote', 'propriedade'])->find($id);
    }

    public function create(array $data): TransacaoFinanceira
    {
        return TransacaoFinanceira::create($data);
    }

    public function update(TransacaoFinanceira $transacao, array $data): bool
    {
        return $transacao->update($data);
    }

    public function delete(TransacaoFinanceira $transacao): bool
    {
        return $transacao->delete();
    }

    public function getDashboard(int $propriedadeId = null, string $periodo = 'mes'): array
    {
        $query = TransacaoFinanceira::query();

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        // Definir período
        $dataInicio = match($periodo) {
            'dia' => now()->startOfDay(),
            'semana' => now()->startOfWeek(),
            'mes' => now()->startOfMonth(),
            'ano' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $query->where('data', '>=', $dataInicio);

        // Calcular totais
        $receitas = (clone $query)->where('tipo', 'receita')->sum('valor');
        $despesas = (clone $query)->where('tipo', 'despesa')->sum('valor');
        $saldo = $receitas - $despesas;

        // Top categorias
        $topCategorias = TransacaoFinanceira::select('categoria_id', DB::raw('SUM(valor) as total'))
            ->with('categoria')
            ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
            ->where('data', '>=', $dataInicio)
            ->groupBy('categoria_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Transações recentes
        $transacoesRecentes = (clone $query)
            ->with(['categoria', 'animal', 'lote'])
            ->orderBy('data', 'desc')
            ->limit(10)
            ->get();

        return [
            'periodo' => $periodo,
            'data_inicio' => $dataInicio->format('Y-m-d'),
            'receitas' => round($receitas, 2),
            'despesas' => round($despesas, 2),
            'saldo' => round($saldo, 2),
            'top_categorias' => $topCategorias,
            'transacoes_recentes' => $transacoesRecentes,
        ];
    }

    public function getEstatisticasPorPeriodo(string $dataInicio, string $dataFim, int $propriedadeId = null): array
    {
        $query = TransacaoFinanceira::query()
            ->whereBetween('data', [$dataInicio, $dataFim]);

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        $receitas = (clone $query)->where('tipo', 'receita')->sum('valor');
        $despesas = (clone $query)->where('tipo', 'despesa')->sum('valor');

        // Agrupar por mês
        $porMes = TransacaoFinanceira::select(
                DB::raw("DATE_TRUNC('month', data) as mes"),
                'tipo',
                DB::raw('SUM(valor) as total')
            )
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
            ->groupBy('mes', 'tipo')
            ->orderBy('mes')
            ->get();

        // Agrupar por categoria
        $porCategoria = TransacaoFinanceira::select('categoria_id', 'tipo', DB::raw('SUM(valor) as total'))
            ->with('categoria')
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
            ->groupBy('categoria_id', 'tipo')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim
            ],
            'receitas_total' => round($receitas, 2),
            'despesas_total' => round($despesas, 2),
            'saldo' => round($receitas - $despesas, 2),
            'por_mes' => $porMes,
            'por_categoria' => $porCategoria,
        ];
    }

    public function getReceitasPorCategoria(int $propriedadeId = null, string $dataInicio = null, string $dataFim = null): array
    {
        $query = TransacaoFinanceira::select('categoria_id', DB::raw('SUM(valor) as total'))
            ->with('categoria')
            ->where('tipo', 'receita')
            ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
            ->when($dataInicio, fn($q) => $q->where('data', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->where('data', '<=', $dataFim))
            ->groupBy('categoria_id')
            ->orderBy('total', 'desc')
            ->get();

        return $query->toArray();
    }

    public function getDespesasPorCategoria(int $propriedadeId = null, string $dataInicio = null, string $dataFim = null): array
    {
        $query = TransacaoFinanceira::select('categoria_id', DB::raw('SUM(valor) as total'))
            ->with('categoria')
            ->where('tipo', 'despesa')
            ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
            ->when($dataInicio, fn($q) => $q->where('data', '>=', $dataInicio))
            ->when($dataFim, fn($q) => $q->where('data', '<=', $dataFim))
            ->groupBy('categoria_id')
            ->orderBy('total', 'desc')
            ->get();

        return $query->toArray();
    }
}

