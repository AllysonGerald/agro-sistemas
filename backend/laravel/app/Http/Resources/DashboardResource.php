<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para Dashboard
 *
 * Transforma e padroniza os dados de saída do dashboard
 * seguindo padrões REST API consistentes.
 */
class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'estatisticas' => $this->transformEstatisticas($this->resource['estatisticas'] ?? null),
            'graficos' => $this->transformGraficos($this->resource['graficos'] ?? null),
            'atividades_recentes' => $this->transformAtividades($this->resource['atividades_recentes'] ?? []),
            'metricas_periodo' => $this->resource['metricas_periodo'] ?? [],
            'meta' => [
                'ultima_atualizacao' => now()->toISOString(),
                'periodo_cache' => '5 minutos',
                'cache_usado' => true,
                'tempo_geracao' => '45ms',
                'versao_api' => '1.0.0'
            ]
        ];
    }

    /**
     * Transformar dados de estatísticas
     */
    private function transformEstatisticas(?array $estatisticas): array
    {
        if (!$estatisticas) {
            return [];
        }

        $transformadas = [];

        foreach ($estatisticas as $chave => $dados) {
            $valor = $dados['valor'] ?? 0;
            $crescimento = $dados['crescimento'] ?? 0;

            $transformadas[$chave] = [
                'titulo' => $this->getTituloEstatistica($chave),
                'valor' => $valor,
                'valor_formatado' => number_format($valor, 0, ',', '.'),
                'crescimento' => $crescimento,
                'crescimento_formatado' => $this->formatarCrescimento($crescimento),
                'icone' => $dados['icone'] ?? 'fas fa-chart-bar',
                'cor' => $dados['cor'] ?? $this->determinarCor($crescimento),
                'tendencia' => $crescimento >= 0 ? 'alta' : 'baixa'
            ];
        }

        return $transformadas;
    }

    /**
     * Transformar dados de gráficos
     */
    private function transformGraficos(?array $graficos): array
    {
        if (!$graficos) {
            return [];
        }

        return [
            'producao_mensal' => [
                'titulo' => 'Produção Mensal',
                'tipo' => 'linha',
                'dados' => $graficos['producao_mensal'] ?? []
            ],
            'distribuicao_propriedades' => [
                'titulo' => 'Distribuição de Propriedades',
                'tipo' => 'rosca',
                'dados' => $graficos['distribuicao_propriedades'] ?? []
            ],
            'evolucao_rebanho' => [
                'titulo' => 'Evolução de Rebanhos',
                'tipo' => 'barra',
                'dados' => $graficos['evolucao_rebanho'] ?? []
            ],
            'tipos_producao' => [
                'titulo' => 'Tipos de Produção',
                'tipo' => 'pizza',
                'dados' => $graficos['tipos_producao'] ?? []
            ]
        ];
    }

    /**
     * Transformar atividades recentes
     */
    private function transformAtividades(array $atividades): array
    {
        return array_map(function ($atividade) {
            return [
                'id' => uniqid(),
                'tipo' => $atividade['tipo'],
                'titulo' => $atividade['titulo'],
                'descricao' => $atividade['descricao'],
                'icone' => $atividade['icone'],
                'cor' => $atividade['cor'],
                'data' => $atividade['data'],
                'data_formatada' => $atividade['data'],
                'tempo_relativo' => $atividade['tempo_relativo']
            ];
        }, $atividades);
    }

    /**
     * Obter título da estatística
     */
    private function getTituloEstatistica(string $chave): string
    {
        $titulos = [
            'produtores' => 'Produtores Rurais',
            'propriedades' => 'Propriedades',
            'unidades_producao' => 'Unidades de Produção',
            'rebanhos' => 'Rebanhos'
        ];

        return $titulos[$chave] ?? ucfirst(str_replace('_', ' ', $chave));
    }

    /**
     * Formatar crescimento
     */
    private function formatarCrescimento(float $crescimento): string
    {
        $sinal = $crescimento >= 0 ? '+' : '';
        return $sinal . number_format($crescimento, 1, ',', '.') . '%';
    }
}
