<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'animais';

    protected $fillable = [
        'identificacao',
        'nome_numero',
        'foto_url',
        'sexo',
        'raca',
        'categoria_atual',
        'situacao',
        'data_nascimento',
        'data_entrada',
        'idade_meses',
        'total_dias_ativo',
        'peso_entrada',
        'peso_atual',
        'origem_materna',
        'origem_paterna',
        'rebanho_id',
        'propriedade_id',
        'lote_id',
        'finalidade_lote',
        'observacoes',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_entrada' => 'date',
        'peso_entrada' => 'decimal:2',
        'peso_atual' => 'decimal:2',
    ];

    // Relacionamentos
    public function rebanho()
    {
        return $this->belongsTo(Rebanho::class);
    }

    public function propriedade()
    {
        return $this->belongsTo(Propriedade::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function manejos()
    {
        return $this->hasMany(Manejo::class);
    }

    public function reproducoes()
    {
        return $this->hasMany(Reproducao::class, 'femea_id');
    }

    public function reproducoesComoMacho()
    {
        return $this->hasMany(Reproducao::class, 'macho_id');
    }

    public function transacoes()
    {
        return $this->hasMany(TransacaoFinanceira::class);
    }

    // Acessores
    public function getIdadeAnosAttribute()
    {
        if ($this->idade_meses) {
            return floor($this->idade_meses / 12);
        }
        return null;
    }

    public function getGanhoPesoAttribute()
    {
        if ($this->peso_entrada && $this->peso_atual) {
            return $this->peso_atual - $this->peso_entrada;
        }
        return null;
    }
}
