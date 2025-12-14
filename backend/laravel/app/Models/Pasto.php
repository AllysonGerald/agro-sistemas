<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'codigo',
        'area_hectares',
        'tipo_pastagem',
        'qualidade',
        'status',
        'capacidade_animais',
        'animais_atual',
        'data_ultima_reforma',
        'data_entrada_descanso',
        'data_prevista_liberacao',
        'propriedade_id',
        'coordenadas_geograficas',
        'tem_agua',
        'tem_sombra',
        'tem_cocho',
        'tem_saleiro',
        'observacoes',
    ];

    protected $casts = [
        'area_hectares' => 'decimal:2',
        'data_ultima_reforma' => 'date',
        'data_entrada_descanso' => 'date',
        'data_prevista_liberacao' => 'date',
        'coordenadas_geograficas' => 'json',
        'tem_agua' => 'boolean',
        'tem_sombra' => 'boolean',
        'tem_cocho' => 'boolean',
        'tem_saleiro' => 'boolean',
    ];

    // Relacionamentos
    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    // Acessores
    public function getTaxaOcupacaoAttribute()
    {
        if ($this->capacidade_animais > 0) {
            return ($this->animais_atual / $this->capacidade_animais) * 100;
        }
        return 0;
    }

    public function getDisponibilidadeAttribute()
    {
        return $this->capacidade_animais - $this->animais_atual;
    }
}
