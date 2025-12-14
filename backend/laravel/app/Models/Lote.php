<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'codigo',
        'finalidade',
        'status',
        'data_formacao',
        'data_prevista_venda',
        'propriedade_id',
        'pasto_id',
        'quantidade_animais',
        'peso_medio_inicial',
        'peso_medio_atual',
        'observacoes',
    ];

    protected $casts = [
        'data_formacao' => 'date',
        'data_prevista_venda' => 'date',
        'peso_medio_inicial' => 'decimal:2',
        'peso_medio_atual' => 'decimal:2',
    ];

    // Relacionamentos
    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    public function pasto()
    {
        return $this->belongsTo(Pasto::class);
    }

    public function animais()
    {
        return $this->hasMany(Animal::class);
    }

    public function transacoes()
    {
        return $this->hasMany(TransacaoFinanceira::class);
    }

    // Acessores
    public function getGanhoPesoMedioAttribute()
    {
        if ($this->peso_medio_inicial && $this->peso_medio_atual) {
            return $this->peso_medio_atual - $this->peso_medio_inicial;
        }
        return null;
    }
}
