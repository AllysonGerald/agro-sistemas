<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'inscricao_estadual',
        'telefone',
        'email',
        'endereco',
        'cep',
        'cidade',
        'estado',
        'logo_path',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}

