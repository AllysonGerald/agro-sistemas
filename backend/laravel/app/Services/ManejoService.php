<?php

namespace App\Services;

use App\Models\Manejo;
use App\Models\Animal;
use Illuminate\Pagination\LengthAwarePaginator;

class ManejoService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Manejo::query()->with(['animal', 'propriedade']);

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['animal_id'])) {
            $query->where('animal_id', $filters['animal_id']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        if (!empty($filters['data_inicio'])) {
            $query->where('data', '>=', $filters['data_inicio']);
        }

        if (!empty($filters['data_fim'])) {
            $query->where('data', '<=', $filters['data_fim']);
        }

        return $query->orderBy('data', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Manejo
    {
        return Manejo::with(['animal', 'propriedade'])->find($id);
    }

    public function create(array $data): Manejo
    {
        $manejo = Manejo::create($data);

        // Se for pesagem, atualizar peso do animal
        if ($manejo->tipo === 'pesagem' && !empty($data['peso'])) {
            $animal = Animal::find($manejo->animal_id);
            if ($animal) {
                $animal->update(['peso_atual' => $data['peso']]);
            }
        }

        return $manejo;
    }

    public function update(Manejo $manejo, array $data): bool
    {
        $updated = $manejo->update($data);

        // Se for pesagem e o peso mudou, atualizar animal
        if ($manejo->tipo === 'pesagem' && !empty($data['peso']) && $data['peso'] !== $manejo->getOriginal('peso')) {
            $animal = Animal::find($manejo->animal_id);
            if ($animal) {
                $animal->update(['peso_atual' => $data['peso']]);
            }
        }

        return $updated;
    }

    public function delete(Manejo $manejo): bool
    {
        return $manejo->delete();
    }

    public function getPorAnimal(int $animalId): array
    {
        return Manejo::where('animal_id', $animalId)
            ->orderBy('data', 'desc')
            ->get()
            ->toArray();
    }

    public function getPendentes(): array
    {
        return Manejo::where('data_proxima_aplicacao', '<=', now()->addDays(7))
            ->where('resultado', 'sucesso')
            ->with(['animal', 'propriedade'])
            ->orderBy('data_proxima_aplicacao')
            ->get()
            ->toArray();
    }

    public function getAgenda(string $dataInicio, string $dataFim): array
    {
        return Manejo::whereBetween('data_proxima_aplicacao', [$dataInicio, $dataFim])
            ->with(['animal', 'propriedade'])
            ->orderBy('data_proxima_aplicacao')
            ->get()
            ->toArray();
    }
}

