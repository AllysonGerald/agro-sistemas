<?php

namespace App\Services;

use App\Models\Pasto;
use Illuminate\Pagination\LengthAwarePaginator;

class PastoService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Pasto::query()->with('propriedade');

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query->where(function ($q) use ($searchTerm, $operator) {
                $q->where('nome', $operator, "%{$searchTerm}%")
                  ->orWhere('codigo', $operator, "%{$searchTerm}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        return $query->orderBy('nome')->paginate($perPage);
    }

    public function findById(int $id): ?Pasto
    {
        return Pasto::with(['propriedade', 'lotes'])->find($id);
    }

    public function create(array $data): Pasto
    {
        return Pasto::create($data);
    }

    public function update(Pasto $pasto, array $data): bool
    {
        return $pasto->update($data);
    }

    public function delete(Pasto $pasto): bool
    {
        return $pasto->delete();
    }

    public function getDisponiveis(int $propriedadeId = null): array
    {
        $query = Pasto::where('status', 'disponivel');

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        return $query->orderBy('nome')->get()->toArray();
    }

    public function iniciarDescanso(Pasto $pasto, string $dataLiberacao): bool
    {
        return $pasto->update([
            'status' => 'em_descanso',
            'data_entrada_descanso' => now(),
            'data_prevista_liberacao' => $dataLiberacao,
        ]);
    }

    public function liberar(Pasto $pasto): bool
    {
        return $pasto->update([
            'status' => 'disponivel',
            'data_entrada_descanso' => null,
            'data_prevista_liberacao' => null,
        ]);
    }
}

