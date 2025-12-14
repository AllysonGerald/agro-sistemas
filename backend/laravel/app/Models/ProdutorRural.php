<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Carbon\Carbon;

class ProdutorRural extends Model
{
    use HasFactory;

    protected $table = 'produtores_rurais';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'telefone',
        'email',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'inscricao_estadual',
        'car',
        'tipo_pessoa',
        'observacoes',
        'data_cadastro'
    ];

    protected $casts = [
        'data_cadastro' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function propriedades(): HasMany
    {
        return $this->hasMany(Propriedade::class, 'produtor_id');
    }

    public function rebanhos(): HasManyThrough
    {
        return $this->hasManyThrough(Rebanho::class, Propriedade::class, 'produtor_id', 'propriedade_id');
    }

    // Accessors
    public function getFormattedCpfCnpjAttribute(): string
    {
        $doc = preg_replace('/\D/', '', $this->cpf_cnpj);

        if (strlen($doc) === 11) {
            return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
        }

        return substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12, 2);
    }

    public function getFormattedTelefoneAttribute(): string
    {
        $phone = preg_replace('/\D/', '', $this->telefone);

        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
        }

        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6, 4);
    }

    // Mutators
    public function setCpfCnpjAttribute($value): void
    {
        $this->attributes['cpf_cnpj'] = preg_replace('/\D/', '', $value);
    }

    public function setTelefoneAttribute($value): void
    {
        $this->attributes['telefone'] = preg_replace('/\D/', '', $value);
    }

    // Scopes
    public function scopeByName($query, $name)
    {
        return $query->where('nome', 'ILIKE', "%{$name}%");
    }

    public function scopeByCpfCnpj($query, $cpfCnpj)
    {
        $cleanDoc = preg_replace('/\D/', '', $cpfCnpj);
        return $query->where('cpf_cnpj', $cleanDoc);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', 'ILIKE', "%{$email}%");
    }

    public function scopeByMunicipio($query, $municipio)
    {
        return $query->where('municipio', 'ILIKE', "%{$municipio}%");
    }
}
