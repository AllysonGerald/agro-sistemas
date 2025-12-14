<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Propriedade extends Model
{
    use HasFactory;

    protected $table = 'propriedades';

    protected $fillable = [
        'nome',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'inscricao_estadual',
        'car',
        'matricula',
        'cartorio',
        'latitude',
        'longitude',
        'area_total',
        'area_preservada',
        'tipo_exploracao',
        'data_aquisicao',
        'observacoes',
        'produtor_id'
    ];

    protected $casts = [
        'area_total' => 'decimal:2',
        'area_preservada' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'data_aquisicao' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function produtor(): BelongsTo
    {
        return $this->belongsTo(ProdutorRural::class, 'produtor_id');
    }

    public function unidadesProducao(): HasMany
    {
        return $this->hasMany(UnidadeProducao::class, 'propriedade_id');
    }

    public function rebanhos(): HasMany
    {
        return $this->hasMany(Rebanho::class, 'propriedade_id');
    }

    // Accessors
    public function getFormattedAreaTotalAttribute(): string
    {
        return number_format((float) $this->area_total, 2, ',', '.') . ' ha';
    }

    public function getTotalUnidadesProducaoAttribute(): int
    {
        return $this->unidadesProducao()->count();
    }

    public function getTotalRebanhoAttribute(): int
    {
        return $this->rebanhos()->sum('quantidade');
    }

    public function getAreaTotalUnidadesAttribute(): float
    {
        return $this->unidadesProducao()->sum('area_total_ha');
    }

    // Scopes
    public function scopeByMunicipio($query, $municipio)
    {
        return $query->where('municipio', 'ILIKE', "%{$municipio}%");
    }

    public function scopeByUf($query, $uf)
    {
        return $query->where('uf', strtoupper($uf));
    }

    public function scopeByProdutor($query, $produtorId)
    {
        return $query->where('produtor_id', $produtorId);
    }
}
