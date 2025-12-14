<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransacaoFinanceira extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transacoes_financeiras';

    protected $fillable = [
        'descricao',
        'tipo',
        'categoria_id',
        'valor',
        'data',
        'animal_id',
        'lote_id',
        'propriedade_id',
        'forma_pagamento',
        'status',
        'observacoes',
        'comprovante_url',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
    ];

    // Relacionamentos
    public function categoria()
    {
        return $this->belongsTo(CategoriaFinanceira::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    // Escopos
    public function scopeReceitas($query)
    {
        return $query->where('tipo', 'receita');
    }

    public function scopeDespesas($query)
    {
        return $query->where('tipo', 'despesa');
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }
}
