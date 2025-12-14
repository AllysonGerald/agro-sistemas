<?php

namespace App\Services;

use App\Models\CategoriaFinanceira;
use Illuminate\Database\Eloquent\Collection;

class CategoriaFinanceiraService
{
    public function getAll(array $filters = []): Collection
    {
        $query = CategoriaFinanceira::query()->with(['categoriaPai', 'subcategorias']);

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!empty($filters['ativo'])) {
            $query->where('ativo', $filters['ativo']);
        }

        return $query->orderBy('tipo')->orderBy('nome')->get();
    }

    public function findById(int $id): ?CategoriaFinanceira
    {
        return CategoriaFinanceira::with(['categoriaPai', 'subcategorias', 'transacoes'])->find($id);
    }

    public function create(array $data): CategoriaFinanceira
    {
        return CategoriaFinanceira::create($data);
    }

    public function update(CategoriaFinanceira $categoria, array $data): bool
    {
        return $categoria->update($data);
    }

    public function delete(CategoriaFinanceira $categoria): bool
    {
        return $categoria->delete();
    }

    public function getPorTipo(string $tipo): Collection
    {
        return CategoriaFinanceira::where('tipo', $tipo)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }
}

