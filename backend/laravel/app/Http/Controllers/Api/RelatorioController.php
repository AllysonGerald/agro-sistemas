<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropriedadeService;
use App\Services\RebanhoService;
use App\Services\UnidadeProducaoService;
use App\Services\CacheService;
use App\Enums\AnimalSpeciesEnum;
use App\Enums\CropTypeEnum;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

/**
 * @tags Relatórios
 */
class RelatorioController extends Controller
{
    public function __construct(
        private PropriedadeService $propriedadeService,
        private RebanhoService $rebanhoService,
        private UnidadeProducaoService $unidadeService
    ) {}

    /**
     * Relatório do dashboard
     */
    public function dashboard(): JsonResponse
    {
        try {
            $cacheKey = CacheService::generateKey('dashboard', 'main');

            $data = CacheService::remember($cacheKey, function () {
                return [
                    'propriedades_por_municipio' => $this->propriedadeService->getByMunicipio(),
                    'animais_por_especie' => $this->rebanhoService->getQuantidadeByEspecie(),
                    'hectares_por_cultura' => $this->unidadeService->getAreaByCultura(),
                    'total_propriedades' => \App\Models\Propriedade::count(),
                    'total_produtores' => \App\Models\ProdutorRural::count(),
                    'total_animais' => \App\Models\Rebanho::sum('quantidade'),
                    'total_hectares' => \App\Models\UnidadeProducao::sum('area_total_ha'),
                ];
            }, 'medium'); // Cache dashboard por 30 minutos

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório do dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function propriedadesPorMunicipio(): JsonResponse
    {
        try {
            $dados = $this->propriedadeService->getByMunicipio();

            return response()->json([
                'success' => true,
                'data' => $dados,
                'meta' => [
                    'total_municipios' => count($dados),
                    'total_propriedades' => $dados->sum('total')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório por município',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function animaisPorEspecie(): JsonResponse
    {
        try {
            $dados = $this->rebanhoService->getQuantidadeByEspecie();

            return response()->json([
                'success' => true,
                'data' => $dados,
                'meta' => [
                    'total_especies' => count($dados),
                    'total_animais' => $dados->sum('total_animais')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório por espécie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function hectaresPorCultura(): JsonResponse
    {
        try {
            $dados = $this->unidadeService->getAreaByCultura();

            return response()->json([
                'success' => true,
                'data' => $dados,
                'meta' => [
                    'total_culturas' => count($dados),
                    'total_hectares' => $dados->sum('area_total')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório por cultura',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function opcoes(): JsonResponse
    {
        try {
            $data = [
                'especies_animais' => AnimalSpeciesEnum::options(),
                'tipos_cultura' => CropTypeEnum::options(),
                'municipios' => \App\Models\Propriedade::distinct()
                    ->orderBy('municipio')
                    ->pluck('municipio'),
                'ufs' => \App\Models\Propriedade::distinct()
                    ->orderBy('uf')
                    ->pluck('uf'),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar opções',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function filtroAvancado(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $tipo = $request->get('tipo', 'propriedades');

            $data = match($tipo) {
                'propriedades' => $this->propriedadeService->exportData($filters),
                'rebanhos' => $this->rebanhoService->getAll($filters, 1000)->items(),
                'unidades' => $this->unidadeService->getAll($filters, 1000)->items(),
                default => []
            };

            return response()->json([
                'success' => true,
                'data' => $data,
                'filters_applied' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aplicar filtros',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarPropriedadesPorMunicipio(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $dados = $this->propriedadeService->getByMunicipio();

            if ($formato === 'excel') {
                return $this->exportarExcel($dados, 'propriedades_por_municipio');
            } elseif ($formato === 'csv') {
                return $this->exportarCsv($dados, 'propriedades_por_municipio');
            } else {
                return $this->exportarPdf($dados, 'propriedades_por_municipio');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarAnimaisPorEspecie(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $dados = $this->rebanhoService->getQuantidadeByEspecie();


            if ($formato === 'excel') {
                return $this->exportarExcel($dados, 'animais_por_especie');
            } elseif ($formato === 'csv') {
                return $this->exportarCsv($dados, 'animais_por_especie');
            } else {
                return $this->exportarPdf($dados, 'animais_por_especie');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarHectaresPorCultura(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $dados = $this->unidadeService->getAreaByCultura();

            if ($formato === 'excel') {
                return $this->exportarExcel($dados, 'hectares_por_cultura');
            } elseif ($formato === 'csv') {
                return $this->exportarCsv($dados, 'hectares_por_cultura');
            } else {
                return $this->exportarPdf($dados, 'hectares_por_cultura');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function exportarPdf($dados, $tipo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar margens e orientação
            $sheet->getPageMargins()->setTop(1.5);
            $sheet->getPageMargins()->setRight(0.75);
            $sheet->getPageMargins()->setLeft(0.75);
            $sheet->getPageMargins()->setBottom(1.5);

            // Título principal elegante
            $titulos = [
                'animais_por_especie' => 'Relatório de Animais por Espécie',
                'propriedades_por_municipio' => 'Relatório de Propriedades por Município',
                'hectares_por_cultura' => 'Relatório de Hectares por Cultura',
                'rebanhos_por_produtor' => 'Relatório de Rebanhos por Produtor',
            ];

            $titulo = $titulos[$tipo] ?? 'Relatório do Sistema Agropecuário';

            // Cabeçalho principal
            $sheet->setCellValue('A1', $titulo);
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(20)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32'); // Verde escuro
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(1)->setRowHeight(35);

            // Data de geração
            $sheet->setCellValue('A2', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A2:D2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(11)
                ->setItalic(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Linha separadora
            $sheet->setCellValue('A3', '');
            $sheet->mergeCells('A3:D3');
            $sheet->getStyle('A3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
            $sheet->getRowDimension(3)->setRowHeight(3);

            // Cabeçalhos da tabela
            $linhaInicial = 5;
            $cabecalhos = $this->getCabecalhosRelatorio($tipo);

            $sheet->setCellValue('A' . $linhaInicial, $cabecalhos['coluna1']);
            $sheet->setCellValue('B' . $linhaInicial, $cabecalhos['coluna2']);

            // Estilizar cabeçalhos
            $headerRange = 'A' . $linhaInicial . ':B' . $linhaInicial;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C'); // Verde médio
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($linhaInicial)->setRowHeight(25);

            // Dados da tabela
            $linhaAtual = $linhaInicial + 1;

            if (is_countable($dados) && count($dados) > 0) {
                foreach ($dados as $index => $item) {
                    $dadosLinha = $this->processarDadosItem($item, $tipo);

                    $sheet->setCellValue('A' . $linhaAtual, $dadosLinha['nome']);
                    $sheet->setCellValue('B' . $linhaAtual, $dadosLinha['valor']);

                    // Estilizar linha de dados
                    $dataRange = 'A' . $linhaAtual . ':B' . $linhaAtual;

                    // Alternar cor de fundo
                    $corFundo = (intval($index) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                    $sheet->getStyle($dataRange)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($corFundo);

                    // Bordas
                    $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

                    // Alinhamento
                    $sheet->getStyle('A' . $linhaAtual)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('B' . $linhaAtual)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Fonte
                    $sheet->getStyle($dataRange)->getFont()->setSize(10);

                    $sheet->getRowDimension($linhaAtual)->setRowHeight(20);
                    $linhaAtual++;
                }
            } else {
                $sheet->setCellValue('A' . $linhaAtual, 'Nenhum dado disponível');
                $sheet->mergeCells('A' . $linhaAtual . ':B' . $linhaAtual);
                $sheet->getStyle('A' . $linhaAtual)->getFont()
                    ->setItalic(true)
                    ->setSize(12)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
                $sheet->getStyle('A' . $linhaAtual)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(35);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(5);
            $sheet->getColumnDimension('D')->setWidth(5);

            // Rodapé com informações do sistema
            $linhaRodape = $linhaAtual + 2;
            $sheet->setCellValue('A' . $linhaRodape, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaRodape . ':D' . $linhaRodape);
            $sheet->getStyle('A' . $linhaRodape)->getFont()
                ->setSize(9)
                ->setItalic(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('888888'));
            $sheet->getStyle('A' . $linhaRodape)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                ->setFitToWidth(1)
                ->setFitToHeight(0);

            $writer = new Mpdf($spreadsheet);
            $writer->setFont('Arial');
            $writer->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $filename = 'relatorio_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getCabecalhosRelatorio($tipo)
    {
        $cabecalhos = [
            'animais_por_especie' => ['coluna1' => 'Espécie', 'coluna2' => 'Total de Animais'],
            'propriedades_por_municipio' => ['coluna1' => 'Município', 'coluna2' => 'Total de Propriedades'],
            'hectares_por_cultura' => ['coluna1' => 'Cultura', 'coluna2' => 'Área Total (hectares)'],
            'rebanhos_por_produtor' => ['coluna1' => 'Propriedade', 'coluna2' => 'Total de Animais'],
        ];

        return $cabecalhos[$tipo] ?? ['coluna1' => 'Item', 'coluna2' => 'Valor'];
    }

    private function processarDadosItem($item, $tipo)
    {
        $nome = 'N/A';
        $valor = 0;

        if (is_object($item)) {
            if (isset($item->especie)) {
                $nome = $item->especie_label ?? $item->especie;
                $valor = is_numeric($item->total_animais) ? (int)$item->total_animais : 0;
            } elseif (isset($item->municipio)) {
                $nome = $item->municipio . ($item->uf ? ' - ' . $item->uf : '');
                $valor = is_numeric($item->total) ? (int)$item->total : 0;
            } elseif (isset($item->nome_cultura)) {
                $nome = $item->cultura_label ?? $item->nome_cultura;
                $valor = is_numeric($item->area_total) ? (float)$item->area_total : 0;
            } elseif (isset($item->cultura)) {
                $nome = $item->cultura_label ?? $item->cultura;
                $valor = is_numeric($item->area_total) ? (float)$item->area_total : 0;
            }
        }

        // Formatar valor
        $valorFormatado = '';
        if (is_numeric($valor) && $valor > 0) {
            if ($tipo === 'hectares_por_cultura') {
                $valorFormatado = number_format($valor, 2, ',', '.');
            } else {
                $valorFormatado = number_format($valor, 0, ',', '.');
            }
        } else {
            $valorFormatado = '0';
        }

        return [
            'nome' => $nome,
            'valor' => $valorFormatado
        ];
    }

    private function exportarExcel($dados, $tipo)
    {
        $spreadsheet = $this->criarPlanilha($dados, $tipo);

        $writer = new Xlsx($spreadsheet);

        $filename = 'relatorio_' . $tipo . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function exportarCsv($dados, $tipo)
    {
        $spreadsheet = $this->criarPlanilha($dados, $tipo);

        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $filename = 'relatorio_' . $tipo . '.csv';
        $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function criarPlanilha($dados, $tipo)
    {
        Log::info('Iniciando criação de planilha:', ['tipo' => $tipo, 'dados_count' => is_countable($dados) ? count($dados) : 'not_countable']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar título baseado no tipo
        $titulos = [
            'propriedades_por_municipio' => 'Propriedades por Município',
            'animais_por_especie' => 'Animais por Espécie',
            'hectares_por_cultura' => 'Hectares por Cultura'
        ];

        $titulo = $titulos[$tipo] ?? 'Relatório';
        $sheet->setTitle($titulo);

        // Adicionar título
        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Adicionar data de geração
        $sheet->setCellValue('A2', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Adicionar linha em branco
        $sheet->setCellValue('A3', '');

        // Configurar cabeçalhos baseado no tipo
        $linhaAtual = 4;
        $numColunas = 0;

        if ($tipo === 'propriedades_por_municipio') {
            $sheet->setCellValue('A' . $linhaAtual, 'Município');
            $sheet->setCellValue('B' . $linhaAtual, 'UF');
            $sheet->setCellValue('C' . $linhaAtual, 'Total de Propriedades');
            $numColunas = 3;

            $linhaAtual++;

            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item->municipio ?? '');
                $sheet->setCellValue('B' . $linhaAtual, $item->uf ?? '');
                $sheet->setCellValue('C' . $linhaAtual, $item->total ?? 0);
                $linhaAtual++;
            }
        } elseif ($tipo === 'animais_por_especie') {
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Total de Animais');
            $numColunas = 2;

            $linhaAtual++;

            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item->especie ?? '');
                $sheet->setCellValue('B' . $linhaAtual, $item->total_animais ?? 0);
                $linhaAtual++;
            }
        } elseif ($tipo === 'hectares_por_cultura') {
            $sheet->setCellValue('A' . $linhaAtual, 'Cultura');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total (hectares)');
            $numColunas = 2;

            $linhaAtual++;

            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item->cultura ?? '');
                $sheet->setCellValue('B' . $linhaAtual, $item->area_total ?? 0);
                $linhaAtual++;
            }
        }

        // Estilizar cabeçalhos
        if ($numColunas > 0) {
            $headerRange = 'A4:' . chr(ord('A') + $numColunas - 1) . '4';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            // Ajustar largura das colunas
            foreach (range('A', chr(ord('A') + $numColunas - 1)) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Adicionar bordas nas células de dados
            if ($linhaAtual > 5) {
                $dataRange = 'A4:' . chr(ord('A') + $numColunas - 1) . ($linhaAtual - 1);
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            }
        }

        return $spreadsheet;
    }

    /**
     * Exportar rebanhos por produtor
     */
    public function exportarRebanhosPorProdutor(Request $request)
    {
        try {
            $produtorId = $request->get('produtor_id');
            $formato = $request->get('formato', 'pdf');

            if (!$produtorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do produtor é obrigatório'
                ], 400);
            }

            $produtor = ProdutorRural::with(['propriedades.rebanhos'])->find($produtorId);
            if (!$produtor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produtor não encontrado'
                ], 404);
            }

            $dados = $this->prepararDadosRebanhosPorProdutor($produtor);

            return $this->exportarPdfRebanhosPorProdutor($dados, 'Rebanhos por Produtor');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Preparar dados de rebanhos por produtor
     */
    private function prepararDadosRebanhosPorProdutor($produtor)
    {
        $dados = [
            'produtor' => [
                'nome' => $produtor->nome,
                'cpf_cnpj' => $produtor->cpf_cnpj,
                'email' => $produtor->email,
                'telefone' => $produtor->telefone
            ],
            'propriedades' => [],
            'total_rebanhos' => 0,
            'total_animais' => 0
        ];

        foreach ($produtor->propriedades as $propriedade) {
            $propriedadeData = [
                'nome' => $propriedade->nome,
                'municipio' => $propriedade->municipio,
                'uf' => $propriedade->uf,
                'area_total' => $propriedade->area_total,
                'rebanhos' => []
            ];

            foreach ($propriedade->rebanhos as $rebanho) {
                $rebanhoData = [
                    'especie' => $rebanho->especie,
                    'finalidade' => $rebanho->finalidade,
                    'quantidade' => $rebanho->quantidade,
                    'data_cadastro' => $rebanho->created_at->format('d/m/Y')
                ];

                $propriedadeData['rebanhos'][] = $rebanhoData;
                $dados['total_rebanhos']++;
                $dados['total_animais'] += $rebanho->quantidade;
            }

            $dados['propriedades'][] = $propriedadeData;
        }

        return $dados;
    }

    /**
     * Exportar PDF específico para rebanhos por produtor
     */
    private function exportarPdfRebanhosPorProdutor($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':D' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(20)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Dados do produtor
            $sheet->setCellValue('A' . $linhaAtual, 'Produtor: ' . $dados['produtor']['nome']);
            $sheet->mergeCells('A' . $linhaAtual . ':D' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(12);
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            $sheet->setCellValue('A' . $linhaAtual, 'CPF/CNPJ: ' . $dados['produtor']['cpf_cnpj']);
            $sheet->mergeCells('A' . $linhaAtual . ':D' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            $sheet->setCellValue('A' . $linhaAtual, 'Email: ' . $dados['produtor']['email']);
            $sheet->mergeCells('A' . $linhaAtual . ':D' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':D' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Resumo Geral');
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(14);
            $linhaAtual++;

            $sheet->setCellValue('A' . $linhaAtual, 'Total de Propriedades:');
            $sheet->setCellValue('B' . $linhaAtual, count($dados['propriedades']));
            $linhaAtual++;

            $sheet->setCellValue('A' . $linhaAtual, 'Total de Rebanhos:');
            $sheet->setCellValue('B' . $linhaAtual, $dados['total_rebanhos']);
            $linhaAtual++;

            $sheet->setCellValue('A' . $linhaAtual, 'Total de Animais:');
            $sheet->setCellValue('B' . $linhaAtual, number_format($dados['total_animais'], 0, ',', '.'));
            $linhaAtual += 2;

            // Detalhes por propriedade
            foreach ($dados['propriedades'] as $propriedade) {
                $sheet->setCellValue('A' . $linhaAtual, 'Propriedade: ' . $propriedade['nome']);
                $sheet->getStyle('A' . $linhaAtual)->getFont()
                    ->setBold(true)
                    ->setSize(12);
                $linhaAtual++;

                $sheet->setCellValue('A' . $linhaAtual, 'Município: ' . $propriedade['municipio'] . ' - ' . $propriedade['uf']);
                $linhaAtual++;

                $sheet->setCellValue('A' . $linhaAtual, 'Área Total: ' . number_format($propriedade['area_total'], 2, ',', '.') . ' ha');
                $linhaAtual++;

                if (!empty($propriedade['rebanhos'])) {
                    // Cabeçalho da tabela de rebanhos
                    $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
                    $sheet->setCellValue('B' . $linhaAtual, 'Finalidade');
                    $sheet->setCellValue('C' . $linhaAtual, 'Quantidade');
                    $sheet->setCellValue('D' . $linhaAtual, 'Data Cadastro');

                    $headerRange = 'A' . $linhaAtual . ':D' . $linhaAtual;
                    $sheet->getStyle($headerRange)->getFont()
                        ->setBold(true)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                    $sheet->getStyle($headerRange)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('388E3C');
                    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
                    $linhaAtual++;

                    // Dados dos rebanhos
                    foreach ($propriedade['rebanhos'] as $rebanho) {
                        $sheet->setCellValue('A' . $linhaAtual, ucfirst($rebanho['especie']));
                        $sheet->setCellValue('B' . $linhaAtual, ucfirst($rebanho['finalidade']));
                        $sheet->setCellValue('C' . $linhaAtual, number_format($rebanho['quantidade'], 0, ',', '.'));
                        $sheet->setCellValue('D' . $linhaAtual, $rebanho['data_cadastro']);

                        // Alternar cor de fundo
                        $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                        $dataRange = 'A' . $linhaAtual . ':D' . $linhaAtual;
                        $sheet->getStyle($dataRange)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($corFundo);

                        $linhaAtual++;
                    }
                } else {
                    $sheet->setCellValue('A' . $linhaAtual, 'Nenhum rebanho cadastrado nesta propriedade');
                    $sheet->getStyle('A' . $linhaAtual)->getFont()
                        ->setItalic(true)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
                    $linhaAtual++;
                }

                $linhaAtual += 2;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(20);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':D' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setSize(8)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');

            // Gerar PDF usando Mpdf writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            $writer->setFont('Arial');

            $filename = 'relatorio_rebanhos_produtor_' . date('Ymd_His') . '.pdf';

            ob_start();
            $writer->save('php://output');
            $pdfContent = ob_get_clean();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF específico para dashboard completo
     */

    /**
     * Relatório geral de produtores rurais
     */
    public function produtoresRurais(Request $request)
    {
        try {
            $produtores = ProdutorRural::with(['propriedades', 'rebanhos'])
                ->orderBy('nome')
                ->get();

            $dados = [
                'total' => $produtores->count(),
                'produtores' => $produtores->map(function ($produtor) {
                    return [
                        'id' => $produtor->id,
                        'nome' => $produtor->nome,
                        'cpf_cnpj' => $this->formatarCpfCnpj($produtor->cpf_cnpj),
                        'email' => $produtor->email,
                        'telefone' => $this->formatarTelefone($produtor->telefone),
                        'endereco' => $produtor->endereco,
                        'total_propriedades' => $produtor->propriedades->count(),
                        'total_rebanhos' => $produtor->rebanhos->count(),
                        'data_cadastro' => $produtor->created_at->format('d/m/Y'),
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $dados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório geral de propriedades rurais
     */
    public function propriedadesRurais(Request $request)
    {
        try {
            $propriedades = Propriedade::with(['produtor', 'unidadesProducao'])
                ->orderBy('nome')
                ->get();

            $dados = [
                'total' => $propriedades->count(),
                'propriedades' => $propriedades->map(function ($propriedade) {
                    return [
                        'id' => $propriedade->id,
                        'nome' => $propriedade->nome,
                        'area_total' => $propriedade->area_total,
                        'area_total_formatada' => number_format($propriedade->area_total ?? 0, 2, ',', '.') . ' ha',
                        'municipio' => $propriedade->municipio,
                        'uf' => $propriedade->uf,
                        'coordenadas' => $propriedade->coordenadas,
                        'produtor' => $propriedade->produtor->nome ?? 'N/A',
                        'total_unidades' => $propriedade->unidadesProducao->count(),
                        'data_cadastro' => $propriedade->created_at->format('d/m/Y'),
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $dados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório geral de unidades de produção
     */
    public function unidadesProducao(Request $request)
    {
        try {
            $unidades = UnidadeProducao::with(['propriedade.produtor'])
                ->orderBy('nome_cultura')
                ->get();

            $dados = [
                'total' => $unidades->count(),
                'unidades' => $unidades->map(function ($unidade) {
                    return [
                        'id' => $unidade->id,
                        'nome' => $unidade->nome_cultura,
                        'area_total' => $unidade->area_total_ha,
                        'area_total_formatada' => number_format($unidade->area_total_ha ?? 0, 2, ',', '.') . ' ha',
                        'tipo_cultura' => $unidade->nome_cultura,
                        'coordenadas' => $unidade->coordenadas_geograficas,
                        'propriedade' => $unidade->propriedade->nome ?? 'N/A',
                        'produtor' => $unidade->propriedade->produtor->nome ?? 'N/A',
                        'data_cadastro' => $unidade->created_at->format('d/m/Y'),
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $dados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório geral de rebanhos
     */
    public function rebanhos(Request $request)
    {
        try {
            $rebanhos = Rebanho::with(['produtor'])
                ->orderBy('especie')
                ->get();

            $dados = [
                'total' => $rebanhos->count(),
                'rebanhos' => $rebanhos->map(function ($rebanho) {
                    return [
                        'id' => $rebanho->id,
                        'especie' => $rebanho->especie,
                        'especie_label' => ucfirst($rebanho->especie),
                        'quantidade' => $rebanho->quantidade,
                        'quantidade_formatada' => number_format($rebanho->quantidade, 0, ',', '.'),
                        'finalidade' => $rebanho->finalidade,
                        'finalidade_label' => ucfirst($rebanho->finalidade),
                        'produtor' => $rebanho->produtor->nome ?? 'N/A',
                        'data_cadastro' => $rebanho->created_at->format('d/m/Y'),
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $dados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar relatório de produtores rurais
     */
    public function exportarProdutoresRurais(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $produtores = ProdutorRural::with(['propriedades', 'rebanhos'])
                ->orderBy('nome')
                ->get();

            $dados = $produtores->map(function ($produtor) {
                return [
                    'Nome' => $produtor->nome,
                    'CPF/CNPJ' => $this->formatarCpfCnpj($produtor->cpf_cnpj),
                    'Email' => $produtor->email,
                    'Telefone' => $this->formatarTelefone($produtor->telefone),
                    'Endereço' => $produtor->endereco,
                    'Propriedades' => $produtor->propriedades->count(),
                    'Rebanhos' => $produtor->rebanhos->count(),
                    'Data Cadastro' => $produtor->created_at->format('d/m/Y'),
                ];
            });

            if ($formato === 'excel') {
                return $this->exportarExcelProdutores($dados, 'Produtores Rurais');
            } elseif ($formato === 'csv') {
                return $this->exportarCsvProdutores($dados, 'Produtores Rurais');
            } else {
                return $this->exportarPdfProdutores($dados, 'Produtores Rurais');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar relatório de propriedades rurais
     */
    public function exportarPropriedadesRurais(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $propriedades = Propriedade::with(['produtor', 'unidadesProducao'])
                ->orderBy('nome')
                ->get();

            $dados = $propriedades->map(function ($propriedade) {
                return [
                    'Nome' => $propriedade->nome,
                    'Área Total' => number_format($propriedade->area_total ?? 0, 2, ',', '.') . ' ha',
                    'Município' => $propriedade->municipio ?? 'N/A',
                    'UF' => $propriedade->uf ?? 'N/A',
                    'Inscrição Estadual' => $propriedade->inscricao_estadual ?? 'N/A',
                    'Produtor' => $propriedade->produtor->nome ?? 'N/A',
                    'Unidades' => $propriedade->unidadesProducao->count(),
                    'Data Cadastro' => $propriedade->created_at->format('d/m/Y'),
                ];
            });

            if ($formato === 'excel') {
                return $this->exportarExcelPropriedades($dados->toArray(), 'Propriedades Rurais');
            } elseif ($formato === 'csv') {
                return $this->exportarCsvPropriedades($dados->toArray(), 'Propriedades Rurais');
            } else {
                return $this->exportarPdfPropriedades($dados->toArray(), 'Propriedades Rurais');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar relatório de unidades de produção
     */
    public function exportarUnidadesProducao(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $unidades = UnidadeProducao::with(['propriedade.produtor'])
                ->orderBy('nome_cultura')
                ->get();

            $dados = $unidades->map(function ($unidade) {
                // Formatar coordenadas se existirem
                $coordenadas = '';
                if ($unidade->coordenadas_geograficas) {
                    $coords = $unidade->coordenadas_geograficas;

                    // Se for string, decodificar JSON
                    if (is_string($coords)) {
                        $decoded = json_decode($coords, true);
                        if ($decoded) {
                            $coords = $decoded;
                        }
                    }

                    // Se for array e tiver lat/lng, formatar
                    if (is_array($coords) && isset($coords['lat']) && isset($coords['lng'])) {
                        $coordenadas = number_format($coords['lat'], 6) . ', ' . number_format($coords['lng'], 6);
                    }
                }

                // Substituir _ por espaços no nome
                $nomeFormatado = str_replace('_', ' ', $unidade->nome_cultura ?? 'N/A');

                return [
                    'Nome' => $nomeFormatado,
                    'Área Total' => number_format($unidade->area_total_ha ?? 0, 2, ',', '.') . ' ha',
                    'Coordenadas' => $coordenadas,
                    'Propriedade' => $unidade->propriedade->nome ?? 'N/A',
                    'Produtor' => $unidade->propriedade->produtor->nome ?? 'N/A',
                    'Data Cadastro' => $unidade->created_at->format('d/m/Y'),
                ];
            });

            if ($formato === 'excel') {
                return $this->exportarExcelUnidadesProducao($dados->toArray(), 'Unidades de Produção');
            } elseif ($formato === 'csv') {
                return $this->exportarCsvUnidadesProducao($dados->toArray(), 'Unidades de Produção');
            } else {
                return $this->exportarPdfUnidadesProducao($dados->toArray(), 'Unidades de Produção');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar relatório de rebanhos
     */
    public function exportarRebanhos(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf');
            $rebanhos = Rebanho::with(['produtor'])
                ->orderBy('especie')
                ->get();

            $dados = $rebanhos->map(function ($rebanho) {
                return [
                    'Espécie' => ucfirst($rebanho->especie ?? 'N/A'),
                    'Quantidade' => number_format($rebanho->quantidade ?? 0, 0, ',', '.'),
                    'Finalidade' => ucfirst($rebanho->finalidade ?? 'N/A'),
                    'Propriedade' => $rebanho->propriedade->nome ?? 'N/A',
                    'Produtor' => $rebanho->produtor->nome ?? 'N/A',
                    'Data Cadastro' => $rebanho->created_at->format('d/m/Y'),
                ];
            });

            if ($formato === 'excel') {
                return $this->exportarExcelRebanhos($dados->toArray(), 'Rebanhos');
            } elseif ($formato === 'csv') {
                return $this->exportarCsvRebanhos($dados->toArray(), 'Rebanhos');
            } else {
                return $this->exportarPdfRebanhos($dados->toArray(), 'Rebanhos');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF específico para produtores rurais
     */
    private function exportarPdfProdutores($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(20)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 3;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Produtores: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(14);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'CPF/CNPJ');
            $sheet->setCellValue('C' . $linhaAtual, 'Email');
            $sheet->setCellValue('D' . $linhaAtual, 'Telefone');
            $sheet->setCellValue('E' . $linhaAtual, 'Endereço');
            $sheet->setCellValue('F' . $linhaAtual, 'Propriedades');
            $sheet->setCellValue('G' . $linhaAtual, 'Rebanhos');
            $sheet->setCellValue('H' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados dos produtores
            foreach ($dados as $produtor) {
                $sheet->setCellValue('A' . $linhaAtual, $produtor['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $produtor['CPF/CNPJ']);
                $sheet->setCellValue('C' . $linhaAtual, $produtor['Email']);
                $sheet->setCellValue('D' . $linhaAtual, $produtor['Telefone']);
                $sheet->setCellValue('E' . $linhaAtual, $produtor['Endereço']);
                $sheet->setCellValue('F' . $linhaAtual, $produtor['Propriedades']);
                $sheet->setCellValue('G' . $linhaAtual, $produtor['Rebanhos']);
                $sheet->setCellValue('H' . $linhaAtual, $produtor['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(15);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setSize(8)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');

            // Gerar PDF usando Mpdf writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            $writer->setFont('Arial');

            $filename = 'relatorio_produtores_rurais_' . date('Ymd_His') . '.pdf';

            ob_start();
            $writer->save('php://output');
            $pdfContent = ob_get_clean();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF específico para propriedades rurais
     */
    private function exportarPdfPropriedades($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Propriedades: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(14);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total');
            $sheet->setCellValue('C' . $linhaAtual, 'Município');
            $sheet->setCellValue('D' . $linhaAtual, 'UF');
            $sheet->setCellValue('E' . $linhaAtual, 'Inscrição Estadual');
            $sheet->setCellValue('F' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('G' . $linhaAtual, 'Unidades');
            $sheet->setCellValue('H' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados das propriedades
            foreach ($dados as $propriedade) {
                $sheet->setCellValue('A' . $linhaAtual, $propriedade['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $propriedade['Área Total']);
                $sheet->setCellValue('C' . $linhaAtual, $propriedade['Município']);
                $sheet->setCellValue('D' . $linhaAtual, $propriedade['UF']);
                $sheet->setCellValue('E' . $linhaAtual, $propriedade['Inscrição Estadual']);
                $sheet->setCellValue('F' . $linhaAtual, $propriedade['Produtor']);
                $sheet->setCellValue('G' . $linhaAtual, $propriedade['Unidades']);
                $sheet->setCellValue('H' . $linhaAtual, $propriedade['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(8);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(15);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);

            // Gerar PDF
            $writer = new Mpdf($spreadsheet);
            $writer->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

            $filename = 'relatorio_propriedades_rurais_' . date('Ymd_His') . '.pdf';

            ob_start();
            $writer->save('php://output');
            $pdfContent = ob_get_clean();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel específico para propriedades rurais
     */
    private function exportarExcelPropriedades($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Propriedades: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(12);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total');
            $sheet->setCellValue('C' . $linhaAtual, 'Município');
            $sheet->setCellValue('D' . $linhaAtual, 'UF');
            $sheet->setCellValue('E' . $linhaAtual, 'Inscrição Estadual');
            $sheet->setCellValue('F' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('G' . $linhaAtual, 'Unidades');
            $sheet->setCellValue('H' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados das propriedades
            foreach ($dados as $propriedade) {
                $sheet->setCellValue('A' . $linhaAtual, $propriedade['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $propriedade['Área Total']);
                $sheet->setCellValue('C' . $linhaAtual, $propriedade['Município']);
                $sheet->setCellValue('D' . $linhaAtual, $propriedade['UF']);
                $sheet->setCellValue('E' . $linhaAtual, $propriedade['Inscrição Estadual']);
                $sheet->setCellValue('F' . $linhaAtual, $propriedade['Produtor']);
                $sheet->setCellValue('G' . $linhaAtual, $propriedade['Unidades']);
                $sheet->setCellValue('H' . $linhaAtual, $propriedade['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(18);

            // Adicionar bordas à tabela
            $tableRange = 'A' . ($linhaAtual - count($dados) - 1) . ':H' . ($linhaAtual - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setSize(8)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');

            // Gerar Excel
            $writer = new Xlsx($spreadsheet);

            $filename = 'relatorio_propriedades_rurais_' . date('Ymd_His') . '.xlsx';

            ob_start();
            $writer->save('php://output');
            $excelContent = ob_get_clean();

            return response($excelContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF específico para unidades de produção
     */
    private function exportarPdfUnidadesProducao($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Unidades: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(14);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total');
            $sheet->setCellValue('C' . $linhaAtual, 'Coordenadas');
            $sheet->setCellValue('D' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('E' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('F' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados das unidades
            foreach ($dados as $unidade) {
                $sheet->setCellValue('A' . $linhaAtual, $unidade['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $unidade['Área Total']);
                $sheet->setCellValue('C' . $linhaAtual, $unidade['Coordenadas']);
                $sheet->setCellValue('D' . $linhaAtual, $unidade['Propriedade']);
                $sheet->setCellValue('E' . $linhaAtual, $unidade['Produtor']);
                $sheet->setCellValue('F' . $linhaAtual, $unidade['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(15);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);

            // Gerar PDF
            $writer = new Mpdf($spreadsheet);
            $writer->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

            $filename = 'relatorio_unidades_producao_' . date('Ymd_His') . '.pdf';

            ob_start();
            $writer->save('php://output');
            $pdfContent = ob_get_clean();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel específico para unidades de produção
     */
    private function exportarExcelUnidadesProducao($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Unidades: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(12);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total');
            $sheet->setCellValue('C' . $linhaAtual, 'Coordenadas');
            $sheet->setCellValue('D' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('E' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('F' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados das unidades
            foreach ($dados as $unidade) {
                $sheet->setCellValue('A' . $linhaAtual, $unidade['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $unidade['Área Total']);
                $sheet->setCellValue('C' . $linhaAtual, $unidade['Coordenadas']);
                $sheet->setCellValue('D' . $linhaAtual, $unidade['Propriedade']);
                $sheet->setCellValue('E' . $linhaAtual, $unidade['Produtor']);
                $sheet->setCellValue('F' . $linhaAtual, $unidade['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(18);

            // Adicionar bordas à tabela
            $tableRange = 'A' . ($linhaAtual - count($dados) - 1) . ':F' . ($linhaAtual - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setSize(8)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');

            // Gerar Excel
            $writer = new Xlsx($spreadsheet);

            $filename = 'relatorio_unidades_producao_' . date('Ymd_His') . '.xlsx';

            ob_start();
            $writer->save('php://output');
            $excelContent = ob_get_clean();

            return response($excelContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF específico para rebanhos
     */
    private function exportarPdfRebanhos($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Rebanhos: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(14);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Quantidade');
            $sheet->setCellValue('C' . $linhaAtual, 'Finalidade');
            $sheet->setCellValue('D' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('E' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('F' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados dos rebanhos
            foreach ($dados as $rebanho) {
                $sheet->setCellValue('A' . $linhaAtual, $rebanho['Espécie']);
                $sheet->setCellValue('B' . $linhaAtual, $rebanho['Quantidade']);
                $sheet->setCellValue('C' . $linhaAtual, $rebanho['Finalidade']);
                $sheet->setCellValue('D' . $linhaAtual, $rebanho['Propriedade']);
                $sheet->setCellValue('E' . $linhaAtual, $rebanho['Produtor']);
                $sheet->setCellValue('F' . $linhaAtual, $rebanho['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(15);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);

            // Gerar PDF
            $writer = new Mpdf($spreadsheet);
            $writer->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

            $filename = 'relatorio_rebanhos_' . date('Ymd_His') . '.pdf';

            ob_start();
            $writer->save('php://output');
            $pdfContent = ob_get_clean();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel específico para rebanhos
     */
    private function exportarExcelRebanhos($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Rebanhos: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(12);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Quantidade');
            $sheet->setCellValue('C' . $linhaAtual, 'Finalidade');
            $sheet->setCellValue('D' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('E' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('F' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados dos rebanhos
            foreach ($dados as $rebanho) {
                $sheet->setCellValue('A' . $linhaAtual, $rebanho['Espécie']);
                $sheet->setCellValue('B' . $linhaAtual, $rebanho['Quantidade']);
                $sheet->setCellValue('C' . $linhaAtual, $rebanho['Finalidade']);
                $sheet->setCellValue('D' . $linhaAtual, $rebanho['Propriedade']);
                $sheet->setCellValue('E' . $linhaAtual, $rebanho['Produtor']);
                $sheet->setCellValue('F' . $linhaAtual, $rebanho['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':F' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(30);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(18);

            // Adicionar bordas à tabela
            $tableRange = 'A' . ($linhaAtual - count($dados) - 1) . ':F' . ($linhaAtual - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setSize(8)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');

            // Gerar Excel
            $writer = new Xlsx($spreadsheet);

            $filename = 'relatorio_rebanhos_' . date('Ymd_His') . '.xlsx';

            ob_start();
            $writer->save('php://output');
            $excelContent = ob_get_clean();

            return response($excelContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV específico para produtores rurais
     */
    private function exportarCsvProdutores($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Produtores: ' . count($dados));
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'CPF/CNPJ');
            $sheet->setCellValue('C' . $linhaAtual, 'Email');
            $sheet->setCellValue('D' . $linhaAtual, 'Telefone');
            $sheet->setCellValue('E' . $linhaAtual, 'Endereço');
            $sheet->setCellValue('F' . $linhaAtual, 'Propriedades');
            $sheet->setCellValue('G' . $linhaAtual, 'Rebanhos');
            $sheet->setCellValue('H' . $linhaAtual, 'Data Cadastro');
            $linhaAtual++;

            // Dados dos produtores
            foreach ($dados as $produtor) {
                $sheet->setCellValue('A' . $linhaAtual, $produtor['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $produtor['CPF/CNPJ']);
                $sheet->setCellValue('C' . $linhaAtual, $produtor['Email']);
                $sheet->setCellValue('D' . $linhaAtual, $produtor['Telefone']);
                $sheet->setCellValue('E' . $linhaAtual, $produtor['Endereço']);
                $sheet->setCellValue('F' . $linhaAtual, $produtor['Propriedades']);
                $sheet->setCellValue('G' . $linhaAtual, $produtor['Rebanhos']);
                $sheet->setCellValue('H' . $linhaAtual, $produtor['Data Cadastro']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_produtores_rurais_' . date('Ymd_His') . '.csv';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

            $writer->save($tempFile);
            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV específico para propriedades rurais
     */
    private function exportarCsvPropriedades($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Propriedades: ' . count($dados));
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total');
            $sheet->setCellValue('C' . $linhaAtual, 'Município');
            $sheet->setCellValue('D' . $linhaAtual, 'UF');
            $sheet->setCellValue('E' . $linhaAtual, 'Inscrição Estadual');
            $sheet->setCellValue('F' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('G' . $linhaAtual, 'Unidades');
            $sheet->setCellValue('H' . $linhaAtual, 'Data Cadastro');
            $linhaAtual++;

            // Dados das propriedades
            foreach ($dados as $propriedade) {
                $sheet->setCellValue('A' . $linhaAtual, $propriedade['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $propriedade['Área Total']);
                $sheet->setCellValue('C' . $linhaAtual, $propriedade['Município']);
                $sheet->setCellValue('D' . $linhaAtual, $propriedade['UF']);
                $sheet->setCellValue('E' . $linhaAtual, $propriedade['Inscrição Estadual']);
                $sheet->setCellValue('F' . $linhaAtual, $propriedade['Produtor']);
                $sheet->setCellValue('G' . $linhaAtual, $propriedade['Unidades']);
                $sheet->setCellValue('H' . $linhaAtual, $propriedade['Data Cadastro']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_propriedades_rurais_' . date('Ymd_His') . '.csv';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

            $writer->save($tempFile);
            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV específico para unidades de produção
     */
    private function exportarCsvUnidadesProducao($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Unidades: ' . count($dados));
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'Área Total');
            $sheet->setCellValue('C' . $linhaAtual, 'Coordenadas');
            $sheet->setCellValue('D' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('E' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('F' . $linhaAtual, 'Data Cadastro');
            $linhaAtual++;

            // Dados das unidades
            foreach ($dados as $unidade) {
                $sheet->setCellValue('A' . $linhaAtual, $unidade['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $unidade['Área Total']);
                $sheet->setCellValue('C' . $linhaAtual, $unidade['Coordenadas']);
                $sheet->setCellValue('D' . $linhaAtual, $unidade['Propriedade']);
                $sheet->setCellValue('E' . $linhaAtual, $unidade['Produtor']);
                $sheet->setCellValue('F' . $linhaAtual, $unidade['Data Cadastro']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_unidades_producao_' . date('Ymd_His') . '.csv';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

            $writer->save($tempFile);
            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV específico para rebanhos
     */
    private function exportarCsvRebanhos($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':F' . $linhaAtual);
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Rebanhos: ' . count($dados));
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Quantidade');
            $sheet->setCellValue('C' . $linhaAtual, 'Finalidade');
            $sheet->setCellValue('D' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('E' . $linhaAtual, 'Produtor');
            $sheet->setCellValue('F' . $linhaAtual, 'Data Cadastro');
            $linhaAtual++;

            // Dados dos rebanhos
            foreach ($dados as $rebanho) {
                $sheet->setCellValue('A' . $linhaAtual, $rebanho['Espécie']);
                $sheet->setCellValue('B' . $linhaAtual, $rebanho['Quantidade']);
                $sheet->setCellValue('C' . $linhaAtual, $rebanho['Finalidade']);
                $sheet->setCellValue('D' . $linhaAtual, $rebanho['Propriedade']);
                $sheet->setCellValue('E' . $linhaAtual, $rebanho['Produtor']);
                $sheet->setCellValue('F' . $linhaAtual, $rebanho['Data Cadastro']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_rebanhos_' . date('Ymd_His') . '.csv';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');

            $writer->save($tempFile);
            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel específico para produtores rurais
     */
    private function exportarExcelProdutores($dados, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $linhaAtual = 1;

            // Título principal
            $sheet->setCellValue('A' . $linhaAtual, $titulo);
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(18)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Data de geração
            $sheet->setCellValue('A' . $linhaAtual, 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setItalic(true)
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');
            $linhaAtual += 2;

            // Resumo
            $sheet->setCellValue('A' . $linhaAtual, 'Total de Produtores: ' . count($dados));
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setSize(12);
            $linhaAtual += 2;

            // Cabeçalho da tabela
            $sheet->setCellValue('A' . $linhaAtual, 'Nome');
            $sheet->setCellValue('B' . $linhaAtual, 'CPF/CNPJ');
            $sheet->setCellValue('C' . $linhaAtual, 'Email');
            $sheet->setCellValue('D' . $linhaAtual, 'Telefone');
            $sheet->setCellValue('E' . $linhaAtual, 'Endereço');
            $sheet->setCellValue('F' . $linhaAtual, 'Propriedades');
            $sheet->setCellValue('G' . $linhaAtual, 'Rebanhos');
            $sheet->setCellValue('H' . $linhaAtual, 'Data Cadastro');

            $headerRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
            $sheet->getStyle($headerRange)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
            $linhaAtual++;

            // Dados dos produtores
            foreach ($dados as $produtor) {
                $sheet->setCellValue('A' . $linhaAtual, $produtor['Nome']);
                $sheet->setCellValue('B' . $linhaAtual, $produtor['CPF/CNPJ']);
                $sheet->setCellValue('C' . $linhaAtual, $produtor['Email']);
                $sheet->setCellValue('D' . $linhaAtual, $produtor['Telefone']);
                $sheet->setCellValue('E' . $linhaAtual, $produtor['Endereço']);
                $sheet->setCellValue('F' . $linhaAtual, $produtor['Propriedades']);
                $sheet->setCellValue('G' . $linhaAtual, $produtor['Rebanhos']);
                $sheet->setCellValue('H' . $linhaAtual, $produtor['Data Cadastro']);

                // Alinhar todas as colunas à esquerda
                $dataRange = 'A' . $linhaAtual . ':H' . $linhaAtual;
                $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('left');

                // Alternar cor de fundo
                $corFundo = (intval($linhaAtual) % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
                $sheet->getStyle($dataRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($corFundo);

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(40);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(18);

            // Adicionar bordas à tabela
            $tableRange = 'A' . ($linhaAtual - count($dados) - 1) . ':H' . ($linhaAtual - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Rodapé
            $linhaAtual += 2;
            $sheet->setCellValue('A' . $linhaAtual, 'Sistema Agropecuário - Relatórios Automatizados');
            $sheet->mergeCells('A' . $linhaAtual . ':H' . $linhaAtual);
            $sheet->getStyle('A' . $linhaAtual)->getFont()
                ->setSize(8)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A' . $linhaAtual)->getAlignment()->setHorizontal('center');

            // Gerar Excel
            $writer = new Xlsx($spreadsheet);

            $filename = 'relatorio_produtores_rurais_' . date('Ymd_His') . '.xlsx';

            ob_start();
            $writer->save('php://output');
            $excelContent = ob_get_clean();

            return response($excelContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatar CPF/CNPJ
     */
    private function formatarCpfCnpj($documento)
    {
        $documento = preg_replace('/\D/', '', $documento);

        if (strlen($documento) === 11) {
            // CPF: 000.000.000-00
            return substr($documento, 0, 3) . '.' .
                   substr($documento, 3, 3) . '.' .
                   substr($documento, 6, 3) . '-' .
                   substr($documento, 9, 2);
        } elseif (strlen($documento) === 14) {
            // CNPJ: 00.000.000/0000-00
            return substr($documento, 0, 2) . '.' .
                   substr($documento, 2, 3) . '.' .
                   substr($documento, 5, 3) . '/' .
                   substr($documento, 8, 4) . '-' .
                   substr($documento, 12, 2);
        }

        return $documento;
    }

    /**
     * Formatar telefone
     */
    private function formatarTelefone($telefone)
    {
        $telefone = preg_replace('/\D/', '', $telefone);

        if (strlen($telefone) === 11) {
            // (00) 00000-0000
            return '(' . substr($telefone, 0, 2) . ') ' .
                   substr($telefone, 2, 5) . '-' .
                   substr($telefone, 7, 4);
        } elseif (strlen($telefone) === 10) {
            // (00) 0000-0000
            return '(' . substr($telefone, 0, 2) . ') ' .
                   substr($telefone, 2, 4) . '-' .
                   substr($telefone, 6, 4);
        } elseif (strlen($telefone) === 9) {
            // 00000-0000 (celular sem DDD)
            return substr($telefone, 0, 5) . '-' . substr($telefone, 5, 4);
        } elseif (strlen($telefone) === 8) {
            // 0000-0000 (fixo sem DDD)
            return substr($telefone, 0, 4) . '-' . substr($telefone, 4, 4);
        }

        return $telefone;
    }

}
