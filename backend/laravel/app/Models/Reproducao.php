<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reproducao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reproducoes';

    protected $fillable = [
        'tipo',
        'femea_id',
        'macho_id',
        'touro_nome',
        'raca_touro',
        'data_cobertura',
        'data_prevista_parto',
        'data_diagnostico',
        'data_parto',
        'status',
        'numero_crias',
        'tipo_parto',
        'dificuldade_parto',
        'crias',
        'responsavel',
        'veterinario',
        'propriedade_id',
        'observacoes',
        'custo_total',
    ];

    protected $casts = [
        'data_cobertura' => 'date',
        'data_prevista_parto' => 'date',
        'data_diagnostico' => 'date',
        'data_parto' => 'date',
        'crias' => 'json',
        'custo_total' => 'decimal:2',
    ];

    // Relacionamentos
    public function femea()
    {
        return $this->belongsTo(Animal::class, 'femea_id');
    }

    public function macho()
    {
        return $this->belongsTo(Animal::class, 'macho_id');
    }

    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    // Escopos
    public function scopePrenhas($query)
    {
        return $query->where('status', 'prenha');
    }

    public function scopeAguardandoDiagnostico($query)
    {
        return $query->where('status', 'aguardando_diagnostico');
    }

    public function scopePartosRealizados($query)
    {
        return $query->where('status', 'parto_realizado');
    }
}
