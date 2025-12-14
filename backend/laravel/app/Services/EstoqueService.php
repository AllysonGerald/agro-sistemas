<?php

namespace App\Services;

use App\Models\Estoque;
use Illuminate\Pagination\LengthAwarePaginator;

class EstoqueService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Estoque::query()->with('propriedade');

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $operator = config('database.default') === 'pgsql' ? 'ILIKE' : 'LIKE';

            $query->where(function ($q) use ($searchTerm, $operator) {
                $q->where('nome', $operator, "%{$searchTerm}%")
                  ->orWhere('codigo', $operator, "%{$searchTerm}%")
                  ->orWhere('marca', $operator, "%{$searchTerm}%");
            });
        }

        if (!empty($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['propriedade_id'])) {
            $query->where('propriedade_id', $filters['propriedade_id']);
        }

        return $query->orderBy('nome')->paginate($perPage);
    }

    public function findById(int $id): ?Estoque
    {
        return Estoque::with('propriedade')->find($id);
    }

    public function create(array $data): Estoque
    {
        // Calcular valor total
        if (!empty($data['valor_unitario']) && !empty($data['quantidade'])) {
            $data['valor_total'] = $data['valor_unitario'] * $data['quantidade'];
        }

        // Definir status baseado na quantidade
        $data['status'] = $this->definirStatus($data);

        return Estoque::create($data);
    }

    public function update(Estoque $estoque, array $data): bool
    {
        // Recalcular valor total se necessário
        if (isset($data['valor_unitario']) || isset($data['quantidade'])) {
            $valorUnitario = $data['valor_unitario'] ?? $estoque->valor_unitario;
            $quantidade = $data['quantidade'] ?? $estoque->quantidade;
            $data['valor_total'] = $valorUnitario * $quantidade;
        }

        // Atualizar status
        $data['status'] = $this->definirStatus(array_merge($estoque->toArray(), $data));

        return $estoque->update($data);
    }

    public function delete(Estoque $estoque): bool
    {
        return $estoque->delete();
    }

    public function registrarEntrada(int $id, float $quantidade, array $dados = []): bool
    {
        $estoque = $this->findById($id);
        
        if (!$estoque) {
            throw new \Exception('Item de estoque não encontrado');
        }

        $novaQuantidade = $estoque->quantidade + $quantidade;
        
        $dataUpdate = array_merge($dados, [
            'quantidade' => $novaQuantidade,
            'status' => $this->definirStatus(['quantidade' => $novaQuantidade, 'quantidade_minima' => $estoque->quantidade_minima])
        ]);

        if (!empty($dados['valor_unitario'])) {
            $dataUpdate['valor_total'] = $dados['valor_unitario'] * $novaQuantidade;
        }

        return $estoque->update($dataUpdate);
    }

    public function registrarSaida(int $id, float $quantidade): bool
    {
        $estoque = $this->findById($id);
        
        if (!$estoque) {
            throw new \Exception('Item de estoque não encontrado');
        }

        if ($estoque->quantidade < $quantidade) {
            throw new \Exception('Quantidade insuficiente em estoque');
        }

        $novaQuantidade = $estoque->quantidade - $quantidade;
        
        return $estoque->update([
            'quantidade' => $novaQuantidade,
            'valor_total' => $estoque->valor_unitario * $novaQuantidade,
            'status' => $this->definirStatus(['quantidade' => $novaQuantidade, 'quantidade_minima' => $estoque->quantidade_minima])
        ]);
    }

    public function getBaixoEstoque(int $propriedadeId = null): array
    {
        $query = Estoque::whereRaw('quantidade <= quantidade_minima')
            ->where('status', '!=', 'esgotado')
            ->with('propriedade');

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        return $query->orderBy('quantidade')->get()->toArray();
    }

    public function getVencidos(int $propriedadeId = null): array
    {
        $query = Estoque::where('data_validade', '<', now())
            ->where('quantidade', '>', 0)
            ->with('propriedade');

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        return $query->orderBy('data_validade')->get()->toArray();
    }

    public function getPorVencer(int $dias = 30, int $propriedadeId = null): array
    {
        $query = Estoque::whereBetween('data_validade', [now(), now()->addDays($dias)])
            ->where('quantidade', '>', 0)
            ->with('propriedade');

        if ($propriedadeId) {
            $query->where('propriedade_id', $propriedadeId);
        }

        return $query->orderBy('data_validade')->get()->toArray();
    }

    private function definirStatus(array $data): string
    {
        // Verifica se está vencido
        if (!empty($data['data_validade']) && $data['data_validade'] < now()) {
            return 'vencido';
        }

        // Verifica se está esgotado
        if (empty($data['quantidade']) || $data['quantidade'] <= 0) {
            return 'esgotado';
        }

        // Verifica se está com baixo estoque
        if (!empty($data['quantidade_minima']) && $data['quantidade'] <= $data['quantidade_minima']) {
            return 'baixo';
        }

        return 'disponivel';
    }
}

