<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\TransacaoFinanceira;
use App\Models\Estoque;
use App\Models\Manejo;
use App\Models\Lote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Retorna estatísticas gerais do dashboard
     */
    public function estatisticas(): JsonResponse
    {
        try {
            // Animais
            $totalAnimais = Animal::count();
            $animaisAtivos = Animal::where('situacao', 'ativo')->count();
            $animaisMesPassado = Animal::where('created_at', '>=', Carbon::now()->subMonth())->count();
            $variacaoAnimais = $animaisMesPassado > 0 ? round(($animaisMesPassado / $totalAnimais) * 100, 1) : 0;

            // Financeiro
            $receitas = TransacaoFinanceira::where('tipo', 'receita')
                ->whereMonth('data', Carbon::now()->month)
                ->sum('valor');

            $despesas = TransacaoFinanceira::where('tipo', 'despesa')
                ->whereMonth('data', Carbon::now()->month)
                ->sum('valor');

            $saldo = $receitas - $despesas;

            $receitasMesPassado = TransacaoFinanceira::where('tipo', 'receita')
                ->whereMonth('data', Carbon::now()->subMonth()->month)
                ->sum('valor');

            $despesasMesPassado = TransacaoFinanceira::where('tipo', 'despesa')
                ->whereMonth('data', Carbon::now()->subMonth()->month)
                ->sum('valor');

            $saldoMesPassado = $receitasMesPassado - $despesasMesPassado;
            $variacaoFinanceiro = $saldoMesPassado != 0
                ? round((($saldo - $saldoMesPassado) / abs($saldoMesPassado)) * 100, 1)
                : 0;

            // Estoque
            $itensEstoque = Estoque::count();
            $estoqueBaixo = Estoque::whereRaw('quantidade <= quantidade_minima')->count();

            // Atividades
            $atividadesMes = Manejo::whereMonth('data', Carbon::now()->month)->count();
            $atividades24h = Manejo::where('data', '>=', Carbon::now()->subDay())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_animais' => $totalAnimais,
                    'variacao_animais' => $variacaoAnimais,
                    'saldo_financeiro' => $saldo,
                    'variacao_financeiro' => $variacaoFinanceiro,
                    'itens_estoque' => $itensEstoque,
                    'estoque_baixo' => $estoqueBaixo,
                    'atividades_mes' => $atividadesMes,
                    'ultimas_24h' => $atividades24h,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna dados para gráfico de evolução do rebanho
     */
    public function graficoEvolucao(): JsonResponse
    {
        try {
            $labels = [];
            $valores = [];

            // Últimos 6 meses
            for ($i = 5; $i >= 0; $i--) {
                $mes = Carbon::now()->subMonths($i);
                $labels[] = ucfirst($mes->locale('pt_BR')->isoFormat('MMM/YY'));

                $total = Animal::where('created_at', '<=', $mes->endOfMonth())
                    ->whereIn('situacao', ['ativo', 'vendido', 'transferido'])
                    ->count();

                $valores[] = $total;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'valores' => $valores
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar gráfico de evolução',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna dados para gráfico financeiro
     */
    public function graficoFinanceiro(): JsonResponse
    {
        try {
            $labels = [];
            $receitas = [];
            $despesas = [];

            // Últimos 6 meses
            for ($i = 5; $i >= 0; $i--) {
                $mes = Carbon::now()->subMonths($i);
                $labels[] = ucfirst($mes->locale('pt_BR')->isoFormat('MMM/YY'));

                $receitasMes = TransacaoFinanceira::where('tipo', 'receita')
                    ->whereYear('data', $mes->year)
                    ->whereMonth('data', $mes->month)
                    ->sum('valor');

                $despesasMes = TransacaoFinanceira::where('tipo', 'despesa')
                    ->whereYear('data', $mes->year)
                    ->whereMonth('data', $mes->month)
                    ->sum('valor');

                $receitas[] = (float) $receitasMes;
                $despesas[] = (float) $despesasMes;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'receitas' => $receitas,
                    'despesas' => $despesas
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar gráfico financeiro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna distribuições para gráficos de pizza
     */
    public function distribuicoes(): JsonResponse
    {
        try {
            // Animais por situação
            $situacao = [
                'ativo' => Animal::where('situacao', 'ativo')->count(),
                'vendido' => Animal::where('situacao', 'vendido')->count(),
                'transferido' => Animal::where('situacao', 'transferido')->count(),
            ];

            // Estoque por categoria
            $estoque = [
                'racao' => Estoque::where('categoria', 'racao')->count(),
                'medicamento' => Estoque::where('categoria', 'medicamento')->count(),
                'vacina' => Estoque::where('categoria', 'vacina')->count(),
                'suplemento' => Estoque::where('categoria', 'suplemento')->count(),
                'outros' => Estoque::whereIn('categoria', ['equipamento', 'outro', 'vermifugo'])->count(),
            ];

            // Atividades por tipo
            $atividades = [
                'pesagem' => Manejo::where('tipo', 'pesagem')
                    ->whereMonth('data', Carbon::now()->month)
                    ->count(),
                'vacinacao' => Manejo::where('tipo', 'vacinacao')
                    ->whereMonth('data', Carbon::now()->month)
                    ->count(),
                'tratamento' => Manejo::whereIn('tipo', ['curativo', 'exame'])
                    ->whereMonth('data', Carbon::now()->month)
                    ->count(),
                'outros' => Manejo::whereIn('tipo', ['vermifugacao', 'castracao', 'descorna', 'marcacao', 'transferencia', 'outro'])
                    ->whereMonth('data', Carbon::now()->month)
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'situacao' => $situacao,
                    'estoque' => $estoque,
                    'atividades' => $atividades
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar distribuições',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna atividades recentes
     */
    public function atividadesRecentes(): JsonResponse
    {
        try {
            $manejos = Manejo::with(['animal', 'propriedade'])
                ->orderBy('data', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($manejo) {
                    $descricao = $this->formatarDescricaoManejo($manejo);

                    return [
                        'id' => $manejo->id,
                        'tipo' => 'manejo',
                        'descricao' => $descricao,
                        'data_relativa' => $this->getDataRelativa($manejo->data),
                    ];
                });

            $animais = Animal::with('propriedade')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($animal) {
                    return [
                        'id' => $animal->id,
                        'tipo' => 'animal',
                        'descricao' => "Animal {$animal->identificacao} cadastrado",
                        'data_relativa' => $this->getDataRelativa($animal->created_at),
                    ];
                });

            $transacoes = TransacaoFinanceira::with('categoria')
                ->orderBy('data', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transacao) {
                    $tipo = $transacao->tipo === 'receita' ? 'Receita' : 'Despesa';
                    return [
                        'id' => $transacao->id,
                        'tipo' => 'financeiro',
                        'descricao' => "{$tipo} de R$ " . number_format($transacao->valor, 2, ',', '.'),
                        'data_relativa' => $this->getDataRelativa($transacao->data),
                    ];
                });

            // Mesclar e ordenar todas as atividades
            $todas = collect([])
                ->merge($manejos)
                ->merge($animais)
                ->merge($transacoes)
                ->sortByDesc(function ($atividade) {
                    return $atividade['data_relativa'];
                })
                ->take(10)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $todas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar atividades recentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formata a descrição do manejo
     */
    private function formatarDescricaoManejo($manejo): string
    {
        $tipo = match($manejo->tipo) {
            'pesagem' => 'Pesagem',
            'vacinacao' => 'Vacinação',
            'vermifugacao' => 'Vermifugação',
            'curativo' => 'Curativo',
            'castracao' => 'Castração',
            'descorna' => 'Descorna',
            'marcacao' => 'Marcação',
            'transferencia' => 'Transferência',
            'exame' => 'Exame',
            default => 'Atividade'
        };

        $alvo = $manejo->animal
            ? "animal {$manejo->animal->identificacao}"
            : "";

        return $alvo ? "{$tipo} realizada em {$alvo}" : "{$tipo} realizada";
    }

    /**
     * Retorna data relativa (ex: "há 2 horas")
     */
    private function getDataRelativa($data): string
    {
        $carbon = Carbon::parse($data);
        $diff = $carbon->diffForHumans();

        // Traduzir para português
        $traducoes = [
            'seconds ago' => 'segundos atrás',
            'minute ago' => 'minuto atrás',
            'minutes ago' => 'minutos atrás',
            'hour ago' => 'hora atrás',
            'hours ago' => 'horas atrás',
            'day ago' => 'dia atrás',
            'days ago' => 'dias atrás',
            'week ago' => 'semana atrás',
            'weeks ago' => 'semanas atrás',
            'month ago' => 'mês atrás',
            'months ago' => 'meses atrás',
        ];

        foreach ($traducoes as $en => $pt) {
            $diff = str_replace($en, $pt, $diff);
        }

        return $diff;
    }
}
