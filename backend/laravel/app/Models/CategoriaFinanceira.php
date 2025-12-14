<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaFinanceira extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_financeiras';

    protected $fillable = [
        'nome',
        'cor',
        'icone',
        'tipo',
        'categoria_pai_id',
        'ativo',
        'descricao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    // Relacionamentos
    public function categoriaPai()
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_pai_id');
    }

    public function subcategorias()
    {
        return $this->hasMany(CategoriaFinanceira::class, 'categoria_pai_id');
    }

    public function transacoes()
    {
        return $this->hasMany(TransacaoFinanceira::class, 'categoria_id');
    }
}
