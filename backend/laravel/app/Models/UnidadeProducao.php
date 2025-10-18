<?php

namespace App\Models;

use App\Enums\CropTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadeProducao extends Model
{
    use HasFactory;

    protected $table = 'unidades_producao';

    protected $fillable = [
        'nome_cultura',
        'area_total_ha',
        'coordenadas_geograficas',
        'propriedade_id'
    ];

    protected $casts = [
        'area_total_ha' => 'decimal:2',
        'coordenadas_geograficas' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function propriedade(): BelongsTo
    {
        return $this->belongsTo(Propriedade::class, 'propriedade_id');
    }

    // Accessors
    public function getFormattedAreaAttribute(): string
    {
        return number_format((float) $this->area_total_ha, 2, ',', '.') . ' ha';
    }

    public function getCropTypeLabelAttribute(): string
    {
        $enum = CropTypeEnum::tryFrom($this->nome_cultura);
        return $enum ? $enum->label() : $this->nome_cultura;
    }

    public function getLatitudeAttribute(): ?float
    {
        return $this->coordenadas_geograficas['lat'] ?? null;
    }

    public function getLongitudeAttribute(): ?float
    {
        return $this->coordenadas_geograficas['lng'] ?? null;
    }

    // Mutators
    public function setCoordenadasGeograficasAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['coordenadas_geograficas'] = $value;
        } else {
            $this->attributes['coordenadas_geograficas'] = json_encode($value);
        }
    }

    // Scopes
    public function scopeByCultura($query, $cultura)
    {
        return $query->where('nome_cultura', $cultura);
    }

    public function scopeByPropriedade($query, $propriedadeId)
    {
        return $query->where('propriedade_id', $propriedadeId);
    }

    public function scopeByMunicipio($query, $municipio)
    {
        return $query->whereHas('propriedade', function ($q) use ($municipio) {
            $q->where('municipio', 'ILIKE', "%{$municipio}%");
        });
    }

    public function scopeWithMinArea($query, $minArea)
    {
        return $query->where('area_total_ha', '>=', $minArea);
    }
}
