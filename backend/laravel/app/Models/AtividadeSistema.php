<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AtividadeSistema extends Model
{
    use HasFactory;

    protected $table = 'atividades_sistema';

    protected $fillable = [
        'tipo',
        'titulo',
        'descricao',
        'icone',
        'cor',
        'usuario',
        'localizacao',
        'dados_extras',
        'created_at'
    ];

    protected $casts = [
        'dados_extras' => 'array',
        'created_at' => 'datetime'
    ];

    /**
     * Obter atividades recentes
     */
    public static function obterRecentes($limite = 10)
    {
        return self::orderBy('created_at', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($atividade) {
                return [
                    'id' => $atividade->id,
                    'tipo' => $atividade->tipo,
                    'titulo' => $atividade->titulo,
                    'descricao' => $atividade->descricao,
                    'icone' => $atividade->icone,
                    'cor' => $atividade->cor,
                    'usuario' => $atividade->usuario ?? 'Sistema',
                    'tempo_relativo' => $atividade->created_at->diffForHumans(),
                    'data' => $atividade->created_at->format('d/m/Y H:i')
                ];
            });
    }

    /**
     * Registrar atividade de produtor
     */
    public static function registrarProdutor($produtor, $acao = 'cadastrado')
    {
        $descricao = $acao === 'cadastrado'
            ? "Produtor {$produtor->nome} foi cadastrado"
            : "Produtor {$produtor->nome} foi " . self::traduzirAcao($acao);

        return self::create([
            'tipo' => 'produtor_cadastrado',
            'titulo' => 'Produtor Rural',
            'descricao' => $descricao,
            'icone' => 'fas fa-user',
            'cor' => '#2563eb',
            'usuario' => 'Sistema',
            'localizacao' => 'Sistema Agropecuário',
            'dados_extras' => [
                'produtor_id' => $produtor->id,
                'nome' => $produtor->nome,
                'acao' => $acao
            ]
        ]);
    }

    /**
     * Registrar atividade de propriedade
     */
    public static function registrarPropriedade($propriedade, $acao = 'cadastrada')
    {
        $descricao = $acao === 'cadastrada'
            ? "Propriedade {$propriedade->nome} foi cadastrada"
            : "Propriedade {$propriedade->nome} foi " . self::traduzirAcao($acao);

        return self::create([
            'tipo' => 'propriedade_cadastrada',
            'titulo' => 'Propriedade Rural',
            'descricao' => $descricao,
            'icone' => 'fas fa-home',
            'cor' => '#10b981',
            'usuario' => 'Sistema',
            'localizacao' => 'Sistema Agropecuário',
            'dados_extras' => [
                'propriedade_id' => $propriedade->id,
                'nome' => $propriedade->nome,
                'acao' => $acao
            ]
        ]);
    }

    /**
     * Registrar atividade de rebanho
     */
    public static function registrarRebanho($rebanho, $acao = 'cadastrado')
    {
        $especieLabel = ucfirst($rebanho->especie);
        $descricao = $acao === 'cadastrado'
            ? "Rebanho de {$especieLabel} ({$rebanho->quantidade} animais) foi cadastrado"
            : "Rebanho de {$especieLabel} foi " . self::traduzirAcao($acao);

        return self::create([
            'tipo' => 'rebanho_cadastrado',
            'titulo' => 'Rebanho',
            'descricao' => $descricao,
            'icone' => 'fas fa-cow',
            'cor' => '#8b5cf6',
            'usuario' => 'Sistema',
            'localizacao' => 'Sistema Agropecuário',
            'dados_extras' => [
                'rebanho_id' => $rebanho->id,
                'especie' => $rebanho->especie,
                'quantidade' => $rebanho->quantidade,
                'acao' => $acao
            ]
        ]);
    }

    /**
     * Registrar atividade de unidade de produção
     */
    public static function registrarUnidadeProducao($unidade, $acao = 'cadastrada')
    {
        $culturaLabel = str_replace('_', ' ', $unidade->nome_cultura);
        $descricao = $acao === 'cadastrada'
            ? "Unidade de {$culturaLabel} foi cadastrada"
            : "Unidade de {$culturaLabel} foi " . self::traduzirAcao($acao);

        return self::create([
            'tipo' => 'unidade_cadastrada',
            'titulo' => 'Unidade de Produção',
            'descricao' => $descricao,
            'icone' => 'fas fa-seedling',
            'cor' => '#f59e0b',
            'usuario' => 'Sistema',
            'localizacao' => 'Sistema Agropecuário',
            'dados_extras' => [
                'unidade_id' => $unidade->id,
                'cultura' => $unidade->nome_cultura,
                'area' => $unidade->area_total_ha,
                'acao' => $acao
            ]
        ]);
    }

    /**
     * Registrar atividade de relatório
     */
    public static function registrarRelatorio($tipoRelatorio, $formato, $totalRegistros = null)
    {
        $descricao = "Relatório de {$tipoRelatorio} foi gerado";
        if ($totalRegistros) {
            $descricao .= " ({$totalRegistros} registros)";
        }
        $descricao .= " - " . strtoupper($formato);

        $icone = $formato === 'excel' ? 'fas fa-file-excel' : ($formato === 'csv' ? 'fas fa-file-csv' : 'fas fa-file-pdf');
        $cor = $formato === 'excel' ? '#10b981' : ($formato === 'csv' ? '#3b82f6' : '#ef4444');

        return self::create([
            'tipo' => 'relatorio_gerado',
            'titulo' => 'Relatório Gerado',
            'descricao' => $descricao,
            'icone' => $icone,
            'cor' => $cor,
            'usuario' => 'Sistema',
            'localizacao' => 'Sistema Agropecuário',
            'dados_extras' => [
                'tipo_relatorio' => $tipoRelatorio,
                'formato' => $formato,
                'total_registros' => $totalRegistros
            ]
        ]);
    }

    /**
     * Traduzir ação do inglês para português
     */
    private static function traduzirAcao($acao)
    {
        $traducoes = [
            'create' => 'criado',
            'created' => 'criado',
            'update' => 'atualizado',
            'updated' => 'atualizado',
            'delete' => 'excluído',
            'deleted' => 'excluído',
            'remove' => 'removido',
            'removed' => 'removido'
        ];

        return $traducoes[$acao] ?? $acao;
    }
}
