<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Animal;
use Illuminate\Pagination\LengthAwarePaginator;

class LoteService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Lote::query()->with(['propriedade', 'pasto', 'animais']);

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

        if (!empty($filters['finalidade'])) {
            $query->where('finalidade', $filters['finalidade']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        return $query->orderBy('nome')->paginate($perPage);
    }

    public function findById(int $id): ?Lote
    {
        return Lote::with(['propriedade', 'pasto', 'animais', 'transacoes'])->find($id);
    }

    public function create(array $data): Lote
    {
        return Lote::create($data);
    }

    public function update(Lote $lote, array $data): bool
    {
        return $lote->update($data);
    }

    public function delete(Lote $lote): bool
    {
        return $lote->delete();
    }

    public function adicionarAnimal(Lote $lote, int $animalId): bool
    {
        $animal = Animal::find($animalId);
        
        if (!$animal) {
            throw new \Exception('Animal não encontrado');
        }

        $animal->update(['lote_id' => $lote->id]);
        
        // Atualizar quantidade de animais no lote
        $lote->increment('quantidade_animais');

        // Recalcular peso médio
        $this->recalcularPesoMedio($lote);

        return true;
    }

    public function removerAnimal(Lote $lote, int $animalId): bool
    {
        $animal = Animal::find($animalId);
        
        if (!$animal || $animal->lote_id !== $lote->id) {
            throw new \Exception('Animal não pertence a este lote');
        }

        $animal->update(['lote_id' => null]);
        
        // Atualizar quantidade
        $lote->decrement('quantidade_animais');

        // Recalcular peso médio
        $this->recalcularPesoMedio($lote);

        return true;
    }

    private function recalcularPesoMedio(Lote $lote): void
    {
        $pesoMedio = Animal::where('lote_id', $lote->id)
            ->whereNotNull('peso_atual')
            ->avg('peso_atual');

        $lote->update(['peso_medio_atual' => $pesoMedio ?? 0]);
    }

    public function getRelatorio(int $loteId): array
    {
        $lote = $this->findById($loteId);

        if (!$lote) {
            return [];
        }

        $animais = $lote->animais;
        
        return [
            'lote' => $lote,
            'total_animais' => $animais->count(),
            'peso_total' => $animais->sum('peso_atual'),
            'peso_medio' => $animais->avg('peso_atual'),
            'ganho_peso_medio' => $lote->ganho_peso_medio,
            'animais' => $animais,
        ];
    }
}

