<?php

namespace App\Models;

use App\Enums\AnimalSpeciesEnum;
use App\Enums\LivestockPurposeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Carbon\Carbon;

class Rebanho extends Model
{
    use HasFactory;

    protected $table = 'rebanhos';

    protected $fillable = [
        'especie',
        'quantidade',
        'finalidade',
        'data_atualizacao',
        'propriedade_id'
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'data_atualizacao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function propriedade(): BelongsTo
    {
        return $this->belongsTo(Propriedade::class, 'propriedade_id');
    }

    public function produtor(): HasOneThrough
    {
        return $this->hasOneThrough(ProdutorRural::class, Propriedade::class, 'id', 'id', 'propriedade_id', 'produtor_id');
    }

    // Accessors
    public function getSpeciesLabelAttribute(): string
    {
        $enum = AnimalSpeciesEnum::tryFrom($this->especie);
        return $enum ? $enum->label() : $this->especie;
    }

    public function getPurposeLabelAttribute(): string
    {
        $enum = LivestockPurposeEnum::tryFrom($this->finalidade);
        return $enum ? $enum->label() : $this->finalidade;
    }

    public function getFormattedQuantidadeAttribute(): string
    {
        return number_format($this->quantidade, 0, ',', '.') . ' animais';
    }

    public function getDaysFromLastUpdateAttribute(): int
    {
        return Carbon::now()->diffInDays($this->data_atualizacao);
    }

    // Mutators
    public function setDataAtualizacaoAttribute($value): void
    {
        $this->attributes['data_atualizacao'] = $value ?? now();
    }

    // Scopes
    public function scopeByEspecie($query, $especie)
    {
        return $query->where('especie', $especie);
    }

    public function scopeByFinalidade($query, $finalidade)
    {
        return $query->where('finalidade', $finalidade);
    }

    public function scopeByPropriedade($query, $propriedadeId)
    {
        return $query->where('propriedade_id', $propriedadeId);
    }

    public function scopeByProdutor($query, $produtorId)
    {
        return $query->whereHas('propriedade', function ($q) use ($produtorId) {
            $q->where('produtor_id', $produtorId);
        });
    }

    public function scopeWithMinQuantidade($query, $minQuantidade)
    {
        return $query->where('quantidade', '>=', $minQuantidade);
    }

    public function scopeUpdatedRecently($query, $days = 30)
    {
        return $query->where('data_atualizacao', '>=', now()->subDays($days));
    }
}
