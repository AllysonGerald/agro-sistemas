<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estoque extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estoque';

    protected $fillable = [
        'nome',
        'codigo',
        'marca',
        'categoria',
        'quantidade',
        'unidade_medida',
        'quantidade_minima',
        'valor_unitario',
        'valor_total',
        'data_compra',
        'data_validade',
        'lote',
        'propriedade_id',
        'localizacao_fisica',
        'fornecedor',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'quantidade_minima' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'data_compra' => 'date',
        'data_validade' => 'date',
    ];

    // Relacionamentos
    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    // Escopos
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeBaixoEstoque($query)
    {
        return $query->where('status', 'baixo')
                     ->orWhereRaw('quantidade <= quantidade_minima');
    }

    public function scopeVencidos($query)
    {
        return $query->where('data_validade', '<', now());
    }

    public function scopePorVencer($query, $dias = 30)
    {
        return $query->whereBetween('data_validade', [now(), now()->addDays($dias)]);
    }
}
