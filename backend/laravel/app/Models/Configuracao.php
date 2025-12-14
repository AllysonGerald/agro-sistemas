<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracao extends Model
{
    use HasFactory;

    protected $table = 'configuracoes';

    protected $fillable = [
        'user_id',
        'tema',
        'idioma',
        'formato_data',
        'moeda',
        'notificacao_email',
        'notificacao_estoque',
        'notificacao_manejo',
        'notificacao_financeiro',
    ];

    protected $casts = [
        'notificacao_email' => 'boolean',
        'notificacao_estoque' => 'boolean',
        'notificacao_manejo' => 'boolean',
        'notificacao_financeiro' => 'boolean',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Criar configurações padrão para um usuário
     */
    public static function criarPadrao(int $userId): self
    {
        return self::create([
            'user_id' => $userId,
            'tema' => 'claro',
            'idioma' => 'pt-BR',
            'formato_data' => 'DD/MM/YYYY',
            'moeda' => 'BRL',
            'notificacao_email' => true,
            'notificacao_estoque' => true,
            'notificacao_manejo' => true,
            'notificacao_financeiro' => false,
        ]);
    }
}

