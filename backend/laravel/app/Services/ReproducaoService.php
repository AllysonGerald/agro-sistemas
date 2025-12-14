<?php

namespace App\Services;

use App\Models\Reproducao;
use Illuminate\Pagination\LengthAwarePaginator;

class ReproducaoService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Reproducao::query()->with(['femea', 'macho', 'propriedade']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['femea_id'])) {
            $query->where('femea_id', $filters['femea_id']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        return $query->orderBy('data_cobertura', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Reproducao
    {
        return Reproducao::with(['femea', 'macho', 'propriedade'])->find($id);
    }

    public function create(array $data): Reproducao
    {
        // Calcular data prevista de parto (9 meses após cobertura)
        if (!empty($data['data_cobertura']) && empty($data['data_prevista_parto'])) {
            $data['data_prevista_parto'] = date('Y-m-d', strtotime($data['data_cobertura'] . ' +9 months'));
        }

        return Reproducao::create($data);
    }

    public function update(Reproducao $reproducao, array $data): bool
    {
        return $reproducao->update($data);
    }

    public function delete(Reproducao $reproducao): bool
    {
        return $reproducao->delete();
    }

    public function getPrenhas(int $propriedadeId = null): array
    {
        $query = Reproducao::where('status', 'prenha')
            ->with(['femea', 'propriedade']);

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        return $query->orderBy('data_prevista_parto')->get()->toArray();
    }

    public function getPartosProximos(int $dias = 30, int $propriedadeId = null): array
    {
        $query = Reproducao::where('status', 'prenha')
            ->whereBetween('data_prevista_parto', [now(), now()->addDays($dias)])
            ->with(['femea', 'propriedade']);

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        return $query->orderBy('data_prevista_parto')->get()->toArray();
    }

    public function getEstatisticas(int $propriedadeId = null): array
    {
        $query = Reproducao::query();

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        $total = $query->count();
        $prenhas = (clone $query)->where('status', 'prenha')->count();
        $partos = (clone $query)->where('status', 'parto_realizado')->count();
        $vazias = (clone $query)->where('status', 'vazia')->count();
        $abortou = (clone $query)->where('status', 'abortou')->count();

        $taxaPrenhez = $total > 0 ? round(($prenhas / $total) * 100, 2) : 0;
        $mediaCrias = (clone $query)->where('status', 'parto_realizado')->avg('numero_crias');

        return [
            'total' => $total,
            'prenhas' => $prenhas,
            'partos_realizados' => $partos,
            'vazias' => $vazias,
            'abortos' => $abortou,
            'taxa_prenhez' => $taxaPrenhez,
            'media_crias' => round($mediaCrias ?? 0, 2),
        ];
    }

    public function registrarParto(Reproducao $reproducao, array $dados): bool
    {
        return $reproducao->update(array_merge($dados, [
            'status' => 'parto_realizado',
            'data_parto' => $dados['data_parto'] ?? now()->format('Y-m-d'),
        ]));
    }
}

