<?php

namespace App\Services;

use App\Models\Animal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class AnimalService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Animal::query()->with(['rebanho', 'propriedade', 'lote']);

        // Busca geral
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query->where(function ($q) use ($searchTerm, $operator) {
                $q->where('identificacao', $operator, "%{$searchTerm}%")
                  ->orWhere('nome_numero', $operator, "%{$searchTerm}%")
                  ->orWhere('raca', $operator, "%{$searchTerm}%")
                  ->orWhereHas('propriedade', function ($subQ) use ($searchTerm, $operator) {
                      $subQ->where('nome', $operator, "%{$searchTerm}%");
                  });
            });
        }

        // Filtros específicos
        if (!empty($filters['sexo'])) {
            $query->where('sexo', $filters['sexo']);
        }

        if (!empty($filters['situacao'])) {
            $query->where('situacao', $filters['situacao']);
        }

        if (!empty($filters['raca'])) {
            $query->where('raca', 'ILIKE', "%{$filters['raca']}%");
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        if (!empty($filters['lote_id'])) {
            $query->where('lote_id', $filters['lote_id']);
        }

        if (!empty($filters['rebanho_id'])) {
            $query->where('rebanho_id', $filters['rebanho_id']);
        }

        if (!empty($filters['categoria_atual'])) {
            $query->where('categoria_atual', $filters['categoria_atual']);
        }

        // Filtro por peso
        if (!empty($filters['peso_min'])) {
            $query->where('peso_atual', '>=', $filters['peso_min']);
        }

        if (!empty($filters['peso_max'])) {
            $query->where('peso_atual', '<=', $filters['peso_max']);
        }

        return $query->orderBy('identificacao')
                    ->paginate($perPage);
    }

    public function findById(int $id): ?Animal
    {
        return Animal::with(['rebanho', 'propriedade', 'lote', 'manejos', 'reproducoes', 'transacoes'])
                     ->find($id);
    }

    public function create(array $data): Animal
    {
        // Calcular idade em meses se data_nascimento foi fornecida
        if (!empty($data['data_nascimento'])) {
            $data['idade_meses'] = now()->diffInMonths($data['data_nascimento']);
        }

        // Calcular total_dias_ativo se data_entrada foi fornecida
        if (!empty($data['data_entrada'])) {
            $data['total_dias_ativo'] = now()->diffInDays($data['data_entrada']);
        }

        return Animal::create($data);
    }

    public function update(Animal $animal, array $data): bool
    {
        // Recalcular idade se data_nascimento mudou
        if (!empty($data['data_nascimento']) && $data['data_nascimento'] !== $animal->data_nascimento) {
            $data['idade_meses'] = now()->diffInMonths($data['data_nascimento']);
        }

        // Recalcular dias ativos se data_entrada mudou
        if (!empty($data['data_entrada']) && $data['data_entrada'] !== $animal->data_entrada) {
            $data['total_dias_ativo'] = now()->diffInDays($data['data_entrada']);
        }

        return $animal->update($data);
    }

    public function delete(Animal $animal): bool
    {
        return $animal->delete();
    }

    public function uploadFoto(Animal $animal, $foto): string
    {
        // Deletar foto antiga se existir
        if ($animal->foto_url) {
            Storage::disk('public')->delete($animal->foto_url);
        }

        // Salvar nova foto
        $path = $foto->store('animais', 'public');
        
        $animal->update(['foto_url' => $path]);

        return Storage::url($path);
    }

    public function getHistorico(int $animalId): array
    {
        $animal = $this->findById($animalId);

        if (!$animal) {
            return [];
        }

        return [
            'animal' => $animal,
            'manejos' => $animal->manejos()->orderBy('data', 'desc')->get(),
            'pesagens' => $animal->manejos()->where('tipo', 'pesagem')->orderBy('data', 'desc')->get(),
            'vacinacoes' => $animal->manejos()->where('tipo', 'vacinacao')->orderBy('data', 'desc')->get(),
            'reproducoes' => $animal->reproducoes()->orderBy('data_cobertura', 'desc')->get(),
            'transacoes' => $animal->transacoes()->orderBy('data', 'desc')->get(),
        ];
    }

    public function getEstatisticas(int $propriedadeId = null): array
    {
        $query = Animal::query();

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        $total = $query->count();
        $ativos = $query->where('situacao', 'ativo')->count();
        $machos = $query->where('sexo', 'macho')->count();
        $femeas = $query->where('sexo', 'femea')->count();

        $pesoMedio = $query->where('situacao', 'ativo')
                          ->whereNotNull('peso_atual')
                          ->avg('peso_atual');

        $porSituacao = Animal::selectRaw('situacao, COUNT(*) as total')
                            ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
                            ->groupBy('situacao')
                            ->get();

        $porCategoria = Animal::selectRaw('categoria_atual, COUNT(*) as total')
                             ->when($propriedadeId, fn($q) => $q->where('propriedade_id', $propriedadeId))
                             ->where('situacao', 'ativo')
                             ->whereNotNull('categoria_atual')
                             ->groupBy('categoria_atual')
                             ->get();

        return [
            'total' => $total,
            'ativos' => $ativos,
            'machos' => $machos,
            'femeas' => $femeas,
            'peso_medio' => round($pesoMedio ?? 0, 2),
            'por_situacao' => $porSituacao,
            'por_categoria' => $porCategoria,
        ];
    }

    public function atualizarPeso(Animal $animal, float $peso): bool
    {
        return $animal->update(['peso_atual' => $peso]);
    }

    public function transferirLote(Animal $animal, int $loteId): bool
    {
        return $animal->update(['lote_id' => $loteId]);
    }

    public function alterarSituacao(Animal $animal, string $situacao): bool
    {
        return $animal->update(['situacao' => $situacao]);
    }
}

