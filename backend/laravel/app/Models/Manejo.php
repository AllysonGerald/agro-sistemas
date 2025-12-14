<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manejo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tipo',
        'data',
        'hora',
        'animal_id',
        'peso',
        'produto_aplicado',
        'dose',
        'lote_produto',
        'data_proxima_aplicacao',
        'responsavel',
        'veterinario',
        'propriedade_id',
        'observacoes',
        'resultado',
        'custo',
        'fotos',
    ];

    protected $casts = [
        'data' => 'date',
        'data_proxima_aplicacao' => 'date',
        'peso' => 'decimal:2',
        'custo' => 'decimal:2',
        'fotos' => 'json',
    ];

    // Relacionamentos
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    // Escopos
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePesagens($query)
    {
        return $query->where('tipo', 'pesagem');
    }

    public function scopeVacinacoes($query)
    {
        return $query->where('tipo', 'vacinacao');
    }
}
