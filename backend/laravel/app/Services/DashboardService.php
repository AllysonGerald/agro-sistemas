<?php

namespace App\Services;

use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;
use App\Models\AtividadeSistema;
use App\Services\AtividadeService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Serviço de Dashboard
 *
 * Centraliza toda a lógica de negócio para geração de dados
 * estatísticos e métricas do dashboard agropecuário.
 *
 * @package App\Services
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class DashboardService
{
    public function __construct(
        private CacheService $cacheService,
        private AtividadeService $atividadeService
    ) {}

    /**
     * Obter dados completos do dashboard
     */
    public function getDashboardData(): array
    {
        $cacheKey = CacheService::generateKey('dashboard', 'complete');

        return $this->cacheService->remember($cacheKey, function () {
            return [
                'estatisticas' => $this->obterEstatisticas(),
                'graficos' => $this->obterDadosGraficos(),
                'atividades_recentes' => $this->obterAtividadesRecentes(),
                'metricas_periodo' => $this->obterMetricasPeriodo()
            ];
        }, 'short'); // Cache por 5 minutos
    }

    /**
     * Obter estatísticas principais
     */
    public function obterEstatisticas(): array
    {
        $totalProdutores = ProdutorRural::count();
        $totalPropriedades = Propriedade::count();
        $totalUnidades = UnidadeProducao::count();
        $totalRebanhos = Rebanho::count();
        $totalAnimais = Rebanho::sum('quantidade');

        // Cálculos de crescimento
        $crescimentoProdutores = $this->calcularCrescimento('produtores_rurais');
        $crescimentoPropriedades = $this->calcularCrescimento('propriedades');

        return [
            'total_produtores' => [
                'valor' => $totalProdutores,
                'crescimento' => $crescimentoProdutores,
                'icone' => 'fas fa-users',
                'cor' => $this->getCor($crescimentoProdutores)
            ],
            'total_propriedades' => [
                'valor' => $totalPropriedades,
                'crescimento' => $crescimentoPropriedades,
                'icone' => 'fas fa-map-marked-alt',
                'cor' => $this->getCor($crescimentoPropriedades)
            ],
            'total_unidades_producao' => [
                'valor' => $totalUnidades,
                'crescimento' => $this->calcularCrescimento('unidades_producao'),
                'icone' => 'fas fa-industry',
                'cor' => $this->getCor($this->calcularCrescimento('unidades_producao'))
            ],
            'total_animais' => [
                'valor' => $totalAnimais,
                'crescimento' => $this->calcularCrescimento('rebanhos'),
                'icone' => 'fas fa-horse-head',
                'cor' => $this->getCor($this->calcularCrescimento('rebanhos'))
            ]
        ];
    }

    /**
     * Obter dados para gráficos
     */
    public function obterDadosGraficos(): array
    {
        try {
            return [
                'producao_mensal' => $this->obterProducaoMensal(),
                'distribuicao_propriedades' => $this->obterDistribuicaoPropriedades(),
                'evolucao_rebanho' => $this->obterEvolucaoRebanho(),
                'tipos_producao' => $this->obterTiposProducao()
            ];
        } catch (\Exception $e) {
            return [
                'producao_mensal' => [],
                'distribuicao_propriedades' => [],
                'evolucao_rebanho' => [],
                'tipos_producao' => []
            ];
        }
    }

    /**
     * Determinar cor baseada no crescimento
     */
    private function getCor(float $crescimento): string
    {
        if ($crescimento > 0) {
            return 'success';
        } elseif ($crescimento < 0) {
            return 'danger';
        }
        return 'secondary';
    }

    /**
     * Obter dados de produção mensal
     */
    private function obterProducaoMensal(): array
    {
        try {
            $dados = UnidadeProducao::select(
                    DB::raw("strftime('%Y-%m', created_at) as mes"),
                    DB::raw('count(*) as total')
                )
                ->groupBy('mes')
                ->orderBy('mes')
                ->limit(12)
                ->get();

            return $dados->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obter distribuição de propriedades por estado
     */
    private function obterDistribuicaoPropriedades(): array
    {
        try {
            $dados = Propriedade::select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->get();

            return $dados->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obter evolução do rebanho
     */
    private function obterEvolucaoRebanho(): array
    {
        try {
            $dados = Rebanho::select(
                    DB::raw("strftime('%Y-%m', created_at) as periodo"),
                    DB::raw('count(*) as total')
                )
                ->groupBy('periodo')
                ->orderBy('periodo')
                ->limit(12)
                ->get();

            return $dados->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obter tipos de produção
     */
    private function obterTiposProducao(): array
    {
        try {
            $dados = UnidadeProducao::select('tipo_producao as tipo', DB::raw('count(*) as total'))
                ->groupBy('tipo_producao')
                ->get();

            return $dados->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obter atividades recentes
     */
    public function obterAtividadesRecentes(): array
    {
        return $this->atividadeService->obterRecentes(10);
    }

    /**
     * Obter métricas por período
     */
    public function obterMetricasPeriodo(): array
    {
        $hoje = Carbon::now();
        $ontem = $hoje->copy()->subDay();
        $semanaPassada = $hoje->copy()->subWeek();
        $mesPassado = $hoje->copy()->subMonth();

        return [
            'hoje' => [
                'produtores' => ProdutorRural::whereDate('created_at', $hoje)->count(),
                'propriedades' => Propriedade::whereDate('created_at', $hoje)->count()
            ],
            'ontem' => [
                'produtores' => ProdutorRural::whereDate('created_at', $ontem)->count(),
                'propriedades' => Propriedade::whereDate('created_at', $ontem)->count()
            ],
            'esta_semana' => [
                'produtores' => ProdutorRural::where('created_at', '>=', $semanaPassada)->count(),
                'propriedades' => Propriedade::where('created_at', '>=', $semanaPassada)->count()
            ],
            'este_mes' => [
                'produtores' => ProdutorRural::where('created_at', '>=', $mesPassado)->count(),
                'propriedades' => Propriedade::where('created_at', '>=', $mesPassado)->count()
            ]
        ];
    }

    /**
     * Calcular crescimento percentual
     */
    private function calcularCrescimento(string $tabela): float
    {
        $mesAtual = Carbon::now()->startOfMonth();
        $mesAnterior = Carbon::now()->subMonth()->startOfMonth();

        $totalAtual = DB::table($tabela)
            ->where('created_at', '>=', $mesAtual)
            ->count();

        $totalAnterior = DB::table($tabela)
            ->whereBetween('created_at', [$mesAnterior, $mesAtual])
            ->count();

        if ($totalAnterior == 0) {
            return $totalAtual > 0 ? 100 : 0;
        }

        return round((($totalAtual - $totalAnterior) / $totalAnterior) * 100, 1);
    }

    /**
     * Gráfico de produção mensal
     */
    private function graficoProducaoMensal(): array
    {
        $meses = [];
        $dados = [];

        for ($i = 11; $i >= 0; $i--) {
            $data = Carbon::now()->subMonths($i);
            $meses[] = $data->format('M/Y');

            $dados[] = ProdutorRural::whereYear('created_at', $data->year)
                ->whereMonth('created_at', $data->month)
                ->count();
        }

        return [
            'labels' => $meses,
            'datasets' => [
                [
                    'label' => 'Produtores Cadastrados',
                    'data' => $dados,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    /**
     * Gráfico de distribuição de propriedades
     */
    private function graficoDistribuicaoPropriedades(): array
    {
        $distribuicao = Propriedade::select('municipio', DB::raw('count(*) as total'))
            ->groupBy('municipio')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        return [
            'labels' => $distribuicao->pluck('municipio')->toArray(),
            'datasets' => [
                [
                    'label' => 'Propriedades por Município',
                    'data' => $distribuicao->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                        '#4BC0C0', '#FF6384'
                    ]
                ]
            ]
        ];
    }

    /**
     * Gráfico de evolução de rebanhos
     */
    private function graficoEvolucaoRebanhos(): array
    {
        $evolucao = Rebanho::select(
                DB::raw("strftime('%Y-%m', created_at) as mes"),
                DB::raw('count(*) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->limit(12)
            ->get();

        return [
            'labels' => $evolucao->pluck('mes')->toArray(),
            'datasets' => [
                [
                    'label' => 'Rebanhos Cadastrados',
                    'data' => $evolucao->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(147, 51, 234, 0.8)',
                    'borderColor' => 'rgba(147, 51, 234, 1)',
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    /**
     * Gráfico de tipos de produção
     */
    private function graficoTiposProducao(): array
    {
        $tipos = UnidadeProducao::select('tipo_producao', DB::raw('count(*) as total'))
            ->groupBy('tipo_producao')
            ->get();

        return [
            'labels' => $tipos->pluck('tipo_producao')->toArray(),
            'datasets' => [
                [
                    'label' => 'Tipos de Produção',
                    'data' => $tipos->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#06B6D4', '#F97316', '#84CC16', '#EC4899'
                    ]
                ]
            ]
        ];
    }
}
