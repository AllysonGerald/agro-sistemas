<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use App\Models\AtividadeSistema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Relatórios
 */
class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Relatório do dashboard
     */
    public function dashboard(): JsonResponse
    {
        try {
            $data = $this->reportService->getDashboardData();

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

    /**
     * Relatório de propriedades por município
     */
    public function propriedadesPorMunicipio(): JsonResponse
    {
        try {
            $request = request();
            $propertyDataProvider = new \App\Services\Reports\DataProviders\PropertyDataProvider(new \App\Services\PropriedadeService());

            $dados = $propertyDataProvider->getDataByMunicipio($request);
            $dadosFormatados = $propertyDataProvider->formatDataByMunicipio($dados);

            return response()->json([
                'success' => true,
                'data' => $dadosFormatados,
                'meta' => [
                    'total_municipios' => count($dadosFormatados),
                    'total_propriedades' => collect($dadosFormatados)->sum('total_propriedades'),
                    'total_area' => collect($dadosFormatados)->sum(function($item) {
                        return (float) str_replace(',', '.', str_replace('.', '', $item['total_area']));
                    }),
                    'total_produtores' => collect($dadosFormatados)->sum('total_produtores')
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

    /**
     * Relatório de animais por espécie
     */
    public function animaisPorEspecie(): JsonResponse
    {
        try {
            $request = request();
            $animalDataProvider = new \App\Services\Reports\DataProviders\AnimalDataProvider(new \App\Services\RebanhoService());

            $dados = $animalDataProvider->getDataByEspecie($request);
            $dadosFormatados = $animalDataProvider->formatDataByEspecie($dados);

            return response()->json([
                'success' => true,
                'data' => $dadosFormatados,
                'meta' => [
                    'total_especies' => count($dadosFormatados),
                    'total_animais' => collect($dadosFormatados)->sum('total_animais'),
                    'total_rebanhos' => collect($dadosFormatados)->sum('total_rebanhos'),
                    'total_propriedades' => collect($dadosFormatados)->sum('total_propriedades')
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

    /**
     * Relatório de hectares por nome de propriedade
     */
    public function hectaresPorCultura(): JsonResponse
    {
        try {
            $request = request();
            $search = $request->get('search', '');

            // Validar se há busca e se a propriedade existe
            if (!empty($search)) {
                $propriedadeExiste = \App\Models\Propriedade::where('nome', 'ILIKE', "%{$search}%")
                    ->orWhere('municipio', 'ILIKE', "%{$search}%")
                    ->exists();

                if (!$propriedadeExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nenhuma propriedade encontrada com o nome informado. Verifique o nome e tente novamente.',
                        'data' => [],
                        'meta' => [
                            'total_propriedades' => 0,
                            'total_hectares' => 0,
                            'total_unidades' => 0,
                            'total_culturas' => 0
                        ]
                    ]);
                }
            }

            $productionUnitDataProvider = new \App\Services\Reports\DataProviders\ProductionUnitDataProvider(new \App\Services\UnidadeProducaoService());

            $dados = $productionUnitDataProvider->getDataByPropriedadeNome($request);
            $dadosFormatados = $productionUnitDataProvider->formatDataByPropriedadeNome($dados);

            // Verificar se há dados após a busca
            if (empty($dadosFormatados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum dado encontrado para os critérios de busca informados.',
                    'data' => [],
                    'meta' => [
                        'total_propriedades' => 0,
                        'total_hectares' => 0,
                        'total_unidades' => 0,
                        'total_culturas' => 0
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $dadosFormatados,
                'meta' => [
                    'total_propriedades' => count($dadosFormatados),
                    'total_hectares' => collect($dadosFormatados)->sum(function($item) {
                        return (float) str_replace(',', '.', str_replace('.', '', $item['total_hectares']));
                    }),
                    'total_unidades' => collect($dadosFormatados)->sum('total_unidades'),
                    'total_culturas' => collect($dadosFormatados)->sum('total_culturas')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório por nome de propriedade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Opções de filtros
     */
    public function opcoes(): JsonResponse
    {
        try {
            $data = $this->reportService->getFilterOptions();

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

    /**
     * Filtro avançado
     */
    public function filtroAvancado(Request $request): JsonResponse
    {
        try {
            $tipo = $request->get('tipo', 'propriedades');
            $data = $this->reportService->getReportData($tipo, $request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'filters_applied' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aplicar filtros',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar propriedades por município
     */
    public function exportarPropriedadesPorMunicipio(Request $request)
    {
        try {
            $propertyDataProvider = new \App\Services\Reports\DataProviders\PropertyDataProvider(new \App\Services\PropriedadeService());

            $dados = $propertyDataProvider->getDataByMunicipio($request);
            $dadosFormatados = $propertyDataProvider->formatDataByMunicipio($dados);

            $formato = $request->get('formato', 'pdf');

            if ($formato === 'excel') {
                return $this->exportarExcelPropriedadesPorMunicipio($dadosFormatados);
            } elseif ($formato === 'csv') {
                return $this->exportarCsvPropriedadesPorMunicipio($dadosFormatados);
            } else {
                return $this->exportarPdfPropriedadesPorMunicipio($dadosFormatados);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar animais por espécie
     */
    public function exportarAnimaisPorEspecie(Request $request)
    {
        try {
            $animalDataProvider = new \App\Services\Reports\DataProviders\AnimalDataProvider(new \App\Services\RebanhoService());

            $dados = $animalDataProvider->getDataByEspecie($request);
            $dadosFormatados = $animalDataProvider->formatDataByEspecie($dados);

            $formato = $request->get('formato', 'pdf');

            if ($formato === 'excel') {
                return $this->exportarExcelAnimaisPorEspecie($dadosFormatados);
            } elseif ($formato === 'csv') {
                return $this->exportarCsvAnimaisPorEspecie($dadosFormatados);
            } else {
                return $this->exportarPdfAnimaisPorEspecie($dadosFormatados);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar hectares por nome de propriedade
     */
    public function exportarHectaresPorCultura(Request $request)
    {
        try {
            $productionUnitDataProvider = new \App\Services\Reports\DataProviders\ProductionUnitDataProvider(new \App\Services\UnidadeProducaoService());

            $dados = $productionUnitDataProvider->getDataByPropriedadeNome($request);
            $dadosFormatados = $productionUnitDataProvider->formatDataByPropriedadeNome($dados);

            $formato = $request->get('formato', 'pdf');

            // Registrar atividade
            $this->registrarAtividadeRelatorio('Hectares por Nome', $formato, count($dadosFormatados));

            if ($formato === 'excel') {
                return $this->exportarExcelHectaresPorNome($dadosFormatados);
            } elseif ($formato === 'csv') {
                return $this->exportarCsvHectaresPorNome($dadosFormatados);
            } else {
                return $this->exportarPdfHectaresPorNome($dadosFormatados);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório de rebanhos por produtor
     */
    public function rebanhosPorProdutor(Request $request): JsonResponse
    {
        try {
            $produtorId = $request->get('produtor_id');

            if (!$produtorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do produtor é obrigatório',
                ], 400);
            }

            $animalDataProvider = new \App\Services\Reports\DataProviders\AnimalDataProvider(new \App\Services\RebanhoService());

            // Buscar rebanhos do produtor específico
            $rebanhos = \App\Models\Rebanho::with(['propriedade.produtor'])
                ->whereHas('propriedade', function ($query) use ($produtorId) {
                    $query->where('produtor_id', $produtorId);
                })
                ->orderBy('especie')
                ->get()
                ->toArray();

            $dadosFormatados = $animalDataProvider->formatData($rebanhos);

            // Buscar informações do produtor
            $produtor = \App\Models\ProdutorRural::find($produtorId);

            return response()->json([
                'success' => true,
                'data' => $dadosFormatados,
                'produtor' => $produtor ? [
                    'id' => $produtor->id,
                    'nome' => $produtor->nome,
                    'cpf_cnpj' => $produtor->cpf_cnpj,
                    'email' => $produtor->email,
                    'telefone' => $produtor->telefone
                ] : null,
                'meta' => [
                    'total_rebanhos' => count($dadosFormatados),
                    'total_animais' => collect($dadosFormatados)->sum('quantidade'),
                    'total_especies' => collect($dadosFormatados)->pluck('especie')->unique()->count(),
                    'total_propriedades' => collect($dadosFormatados)->pluck('propriedade')->unique()->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de rebanhos por produtor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatórios de dados completos
     */
    public function produtoresRurais(Request $request): JsonResponse
    {
        try {
            $data = $this->reportService->getReportData('produtores', $request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de produtores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function propriedadesRurais(Request $request): JsonResponse
    {
        try {
            $data = $this->reportService->getReportData('propriedades', $request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de propriedades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unidadesProducao(Request $request): JsonResponse
    {
        try {
            $data = $this->reportService->getReportData('unidades', $request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de unidades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rebanhos(Request $request): JsonResponse
    {
        try {
            $data = $this->reportService->getReportData('rebanhos', $request);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de rebanhos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportações de dados completos
     */
    public function exportarProdutoresRurais(Request $request)
    {
        try {
            return $this->reportService->exportReport('produtores', $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarPropriedadesRurais(Request $request)
    {
        try {
            return $this->reportService->exportReport('propriedades', $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarUnidadesProducao(Request $request)
    {
        try {
            return $this->reportService->exportReport('unidades-producao', $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportarRebanhos(Request $request)
    {
        try {
            return $this->reportService->exportReport('rebanhos', $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF - Propriedades por Município
     */
    private function exportarPdfPropriedadesPorMunicipio(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Propriedades por Município');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Subtítulo
            $sheet->setCellValue('A2', 'Relatório detalhado das propriedades agrupadas por município');
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalMunicipios = count($dados);
            $totalPropriedades = collect($dados)->sum('total_propriedades');
            $totalArea = collect($dados)->sum(function($item) {
                return (float) str_replace(',', '.', str_replace('.', '', $item['total_area']));
            });
            $totalProdutores = collect($dados)->sum('total_produtores');

            $sheet->setCellValue('A4', "Total de Municípios: {$totalMunicipios} | Total de Propriedades: {$totalPropriedades} | Total de Área: {$totalArea} ha | Total de Produtores: {$totalProdutores}");
            $sheet->mergeCells('A4:G4');
            $sheet->getStyle('A4')->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A4')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Município');
            $sheet->setCellValue('B' . $linhaAtual, 'UF');
            $sheet->setCellValue('C' . $linhaAtual, 'Total de Propriedades');
            $sheet->setCellValue('D' . $linhaAtual, 'Área Total (ha)');
            $sheet->setCellValue('E' . $linhaAtual, 'Total de Produtores');
            $sheet->setCellValue('F' . $linhaAtual, 'Unidades de Produção');
            $sheet->setCellValue('G' . $linhaAtual, 'Total de Animais');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('B' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('C' . $linhaAtual, $item['total_propriedades']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_area']);
                $sheet->setCellValue('E' . $linhaAtual, $item['total_produtores']);
                $sheet->setCellValue('F' . $linhaAtual, $item['total_unidades']);
                $sheet->setCellValue('G' . $linhaAtual, $item['total_animais']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(8);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(22);
            $sheet->getColumnDimension('G')->setWidth(18);

            // Bordas
            $sheet->getStyle('A6:G' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar PDF
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            $filename = 'relatorio_propriedades_por_municipio_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel - Propriedades por Município
     */
    private function exportarExcelPropriedadesPorMunicipio(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Propriedades por Município');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Subtítulo
            $sheet->setCellValue('A2', 'Relatório detalhado das propriedades agrupadas por município');
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalMunicipios = count($dados);
            $totalPropriedades = collect($dados)->sum('total_propriedades');
            $totalArea = collect($dados)->sum(function($item) {
                return (float) str_replace(',', '.', str_replace('.', '', $item['total_area']));
            });
            $totalProdutores = collect($dados)->sum('total_produtores');

            $sheet->setCellValue('A4', "Total de Municípios: {$totalMunicipios} | Total de Propriedades: {$totalPropriedades} | Total de Área: {$totalArea} ha | Total de Produtores: {$totalProdutores}");
            $sheet->mergeCells('A4:G4');
            $sheet->getStyle('A4')->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A4')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Município');
            $sheet->setCellValue('B' . $linhaAtual, 'UF');
            $sheet->setCellValue('C' . $linhaAtual, 'Total de Propriedades');
            $sheet->setCellValue('D' . $linhaAtual, 'Área Total (ha)');
            $sheet->setCellValue('E' . $linhaAtual, 'Total de Produtores');
            $sheet->setCellValue('F' . $linhaAtual, 'Unidades de Produção');
            $sheet->setCellValue('G' . $linhaAtual, 'Total de Animais');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('B' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('C' . $linhaAtual, $item['total_propriedades']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_area']);
                $sheet->setCellValue('E' . $linhaAtual, $item['total_produtores']);
                $sheet->setCellValue('F' . $linhaAtual, $item['total_unidades']);
                $sheet->setCellValue('G' . $linhaAtual, $item['total_animais']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(8);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(22);
            $sheet->getColumnDimension('G')->setWidth(18);

            // Bordas
            $sheet->getStyle('A6:G' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'relatorio_propriedades_por_municipio_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV - Propriedades por Município
     */
    private function exportarCsvPropriedadesPorMunicipio(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalho
            $sheet->setCellValue('A1', 'Município');
            $sheet->setCellValue('B1', 'UF');
            $sheet->setCellValue('C1', 'Total de Propriedades');
            $sheet->setCellValue('D1', 'Área Total (ha)');
            $sheet->setCellValue('E1', 'Total de Produtores');
            $sheet->setCellValue('F1', 'Unidades de Produção');
            $sheet->setCellValue('G1', 'Total de Animais');

            // Dados
            $linhaAtual = 2;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('B' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('C' . $linhaAtual, $item['total_propriedades']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_area']);
                $sheet->setCellValue('E' . $linhaAtual, $item['total_produtores']);
                $sheet->setCellValue('F' . $linhaAtual, $item['total_unidades']);
                $sheet->setCellValue('G' . $linhaAtual, $item['total_animais']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_propriedades_por_municipio_' . now()->format('Y_m_d_H_i_s') . '.csv';
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
                'message' => 'Erro ao gerar CSV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF - Animais por Espécie
     */
    private function exportarPdfAnimaisPorEspecie(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Animais por Espécie');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Subtítulo
            $sheet->setCellValue('A2', 'Relatório detalhado dos animais agrupados por espécie');
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:H3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalEspecies = count($dados);
            $totalAnimais = collect($dados)->sum('total_animais');
            $totalRebanhos = collect($dados)->sum('total_rebanhos');
            $totalPropriedades = collect($dados)->sum('total_propriedades');

            $sheet->setCellValue('A4', "Total de Espécies: {$totalEspecies} | Total de Animais: {$totalAnimais} | Total de Rebanhos: {$totalRebanhos} | Total de Propriedades: {$totalPropriedades}");
            $sheet->mergeCells('A4:H4');
            $sheet->getStyle('A4')->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A4')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Total de Animais');
            $sheet->setCellValue('C' . $linhaAtual, 'Total de Rebanhos');
            $sheet->setCellValue('D' . $linhaAtual, 'Total de Propriedades');
            $sheet->setCellValue('E' . $linhaAtual, 'Propriedades - Corte');
            $sheet->setCellValue('F' . $linhaAtual, 'Propriedades - Leite');
            $sheet->setCellValue('G' . $linhaAtual, 'Propriedades - Reprodução');
            $sheet->setCellValue('H' . $linhaAtual, 'Propriedades - Misto');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['especie']);
                $sheet->setCellValue('B' . $linhaAtual, $item['total_animais']);
                $sheet->setCellValue('C' . $linhaAtual, $item['total_rebanhos']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_propriedades']);
                $sheet->setCellValue('E' . $linhaAtual, $item['propriedades_corte']);
                $sheet->setCellValue('F' . $linhaAtual, $item['propriedades_leite']);
                $sheet->setCellValue('G' . $linhaAtual, $item['propriedades_reproducao']);
                $sheet->setCellValue('H' . $linhaAtual, $item['propriedades_misto']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(22);
            $sheet->getColumnDimension('F')->setWidth(22);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(22);

            // Bordas
            $sheet->getStyle('A6:H' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar PDF
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            $filename = 'relatorio_animais_por_especie_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel - Animais por Espécie
     */
    private function exportarExcelAnimaisPorEspecie(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Animais por Espécie');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Subtítulo
            $sheet->setCellValue('A2', 'Relatório detalhado dos animais agrupados por espécie');
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:H3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalEspecies = count($dados);
            $totalAnimais = collect($dados)->sum('total_animais');
            $totalRebanhos = collect($dados)->sum('total_rebanhos');
            $totalPropriedades = collect($dados)->sum('total_propriedades');

            $sheet->setCellValue('A4', "Total de Espécies: {$totalEspecies} | Total de Animais: {$totalAnimais} | Total de Rebanhos: {$totalRebanhos} | Total de Propriedades: {$totalPropriedades}");
            $sheet->mergeCells('A4:H4');
            $sheet->getStyle('A4')->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A4')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Total de Animais');
            $sheet->setCellValue('C' . $linhaAtual, 'Total de Rebanhos');
            $sheet->setCellValue('D' . $linhaAtual, 'Total de Propriedades');
            $sheet->setCellValue('E' . $linhaAtual, 'Propriedades - Corte');
            $sheet->setCellValue('F' . $linhaAtual, 'Propriedades - Leite');
            $sheet->setCellValue('G' . $linhaAtual, 'Propriedades - Reprodução');
            $sheet->setCellValue('H' . $linhaAtual, 'Propriedades - Misto');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['especie']);
                $sheet->setCellValue('B' . $linhaAtual, $item['total_animais']);
                $sheet->setCellValue('C' . $linhaAtual, $item['total_rebanhos']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_propriedades']);
                $sheet->setCellValue('E' . $linhaAtual, $item['propriedades_corte']);
                $sheet->setCellValue('F' . $linhaAtual, $item['propriedades_leite']);
                $sheet->setCellValue('G' . $linhaAtual, $item['propriedades_reproducao']);
                $sheet->setCellValue('H' . $linhaAtual, $item['propriedades_misto']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(22);
            $sheet->getColumnDimension('F')->setWidth(22);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(22);

            // Bordas
            $sheet->getStyle('A6:H' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'relatorio_animais_por_especie_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV - Animais por Espécie
     */
    private function exportarCsvAnimaisPorEspecie(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalho
            $sheet->setCellValue('A1', 'Espécie');
            $sheet->setCellValue('B1', 'Total de Animais');
            $sheet->setCellValue('C1', 'Total de Rebanhos');
            $sheet->setCellValue('D1', 'Total de Propriedades');
            $sheet->setCellValue('E1', 'Propriedades - Corte');
            $sheet->setCellValue('F1', 'Propriedades - Leite');
            $sheet->setCellValue('G1', 'Propriedades - Reprodução');
            $sheet->setCellValue('H1', 'Propriedades - Misto');

            // Dados
            $linhaAtual = 2;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['especie']);
                $sheet->setCellValue('B' . $linhaAtual, $item['total_animais']);
                $sheet->setCellValue('C' . $linhaAtual, $item['total_rebanhos']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_propriedades']);
                $sheet->setCellValue('E' . $linhaAtual, $item['propriedades_corte']);
                $sheet->setCellValue('F' . $linhaAtual, $item['propriedades_leite']);
                $sheet->setCellValue('G' . $linhaAtual, $item['propriedades_reproducao']);
                $sheet->setCellValue('H' . $linhaAtual, $item['propriedades_misto']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_animais_por_especie_' . now()->format('Y_m_d_H_i_s') . '.csv';
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
                'message' => 'Erro ao gerar CSV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF - Hectares por Nome de Propriedade
     */
    private function exportarPdfHectaresPorNome(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Hectares por Nome de Propriedade');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Subtítulo
            $sheet->setCellValue('A2', 'Relatório detalhado dos hectares agrupados por nome de propriedade');
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalPropriedades = count($dados);
            $totalHectares = collect($dados)->sum(function($item) {
                return (float) str_replace(',', '.', str_replace('.', '', $item['total_hectares']));
            });
            $totalUnidades = collect($dados)->sum('total_unidades');
            $totalCulturas = collect($dados)->sum('total_culturas');

            $sheet->setCellValue('A4', "Total de Propriedades: {$totalPropriedades} | Total de Hectares: " . number_format($totalHectares, 2, ',', '.') . " ha | Total de Unidades: {$totalUnidades} | Total de Culturas: {$totalCulturas}");
            $sheet->mergeCells('A4:G4');
            $sheet->getStyle('A4')->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A4')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Nome da Propriedade');
            $sheet->setCellValue('B' . $linhaAtual, 'Município');
            $sheet->setCellValue('C' . $linhaAtual, 'UF');
            $sheet->setCellValue('D' . $linhaAtual, 'Total de Hectares');
            $sheet->setCellValue('E' . $linhaAtual, 'Total de Unidades');
            $sheet->setCellValue('F' . $linhaAtual, 'Total de Culturas');
            $sheet->setCellValue('G' . $linhaAtual, 'Culturas');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['propriedade_nome']);
                $sheet->setCellValue('B' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('C' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_hectares']);
                $sheet->setCellValue('E' . $linhaAtual, $item['total_unidades']);
                $sheet->setCellValue('F' . $linhaAtual, $item['total_culturas']);
                $sheet->setCellValue('G' . $linhaAtual, $item['culturas_lista']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(8);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->getColumnDimension('G')->setWidth(40);

            // Bordas
            $sheet->getStyle('A6:G' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar PDF
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            $filename = 'relatorio_hectares_por_nome_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel - Hectares por Nome de Propriedade
     */
    private function exportarExcelHectaresPorNome(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Hectares por Nome de Propriedade');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Subtítulo
            $sheet->setCellValue('A2', 'Relatório detalhado dos hectares agrupados por nome de propriedade');
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
            $sheet->getStyle('A2')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalPropriedades = count($dados);
            $totalHectares = collect($dados)->sum(function($item) {
                return (float) str_replace(',', '.', str_replace('.', '', $item['total_hectares']));
            });
            $totalUnidades = collect($dados)->sum('total_unidades');
            $totalCulturas = collect($dados)->sum('total_culturas');

            $sheet->setCellValue('A4', "Total de Propriedades: {$totalPropriedades} | Total de Hectares: " . number_format($totalHectares, 2, ',', '.') . " ha | Total de Unidades: {$totalUnidades} | Total de Culturas: {$totalCulturas}");
            $sheet->mergeCells('A4:G4');
            $sheet->getStyle('A4')->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A4')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Nome da Propriedade');
            $sheet->setCellValue('B' . $linhaAtual, 'Município');
            $sheet->setCellValue('C' . $linhaAtual, 'UF');
            $sheet->setCellValue('D' . $linhaAtual, 'Total de Hectares');
            $sheet->setCellValue('E' . $linhaAtual, 'Total de Unidades');
            $sheet->setCellValue('F' . $linhaAtual, 'Total de Culturas');
            $sheet->setCellValue('G' . $linhaAtual, 'Culturas');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['propriedade_nome']);
                $sheet->setCellValue('B' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('C' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_hectares']);
                $sheet->setCellValue('E' . $linhaAtual, $item['total_unidades']);
                $sheet->setCellValue('F' . $linhaAtual, $item['total_culturas']);
                $sheet->setCellValue('G' . $linhaAtual, $item['culturas_lista']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':G' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(8);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->getColumnDimension('G')->setWidth(40);

            // Bordas
            $sheet->getStyle('A6:G' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'relatorio_hectares_por_nome_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV - Hectares por Nome de Propriedade
     */
    private function exportarCsvHectaresPorNome(array $dados)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalho
            $sheet->setCellValue('A1', 'Nome da Propriedade');
            $sheet->setCellValue('B1', 'Município');
            $sheet->setCellValue('C1', 'UF');
            $sheet->setCellValue('D1', 'Total de Hectares');
            $sheet->setCellValue('E1', 'Total de Unidades');
            $sheet->setCellValue('F1', 'Total de Culturas');
            $sheet->setCellValue('G1', 'Culturas');

            // Dados
            $linhaAtual = 2;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['propriedade_nome']);
                $sheet->setCellValue('B' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('C' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('D' . $linhaAtual, $item['total_hectares']);
                $sheet->setCellValue('E' . $linhaAtual, $item['total_unidades']);
                $sheet->setCellValue('F' . $linhaAtual, $item['total_culturas']);
                $sheet->setCellValue('G' . $linhaAtual, $item['culturas_lista']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_hectares_por_nome_' . now()->format('Y_m_d_H_i_s') . '.csv';
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
                'message' => 'Erro ao gerar CSV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar rebanhos por produtor
     */
    public function exportarRebanhosPorProdutor(Request $request)
    {
        try {
            $produtorId = $request->get('produtor_id');

            if (!$produtorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do produtor é obrigatório',
                ], 400);
            }

            $animalDataProvider = new \App\Services\Reports\DataProviders\AnimalDataProvider(new \App\Services\RebanhoService());

            // Buscar rebanhos do produtor específico
            $rebanhos = \App\Models\Rebanho::with(['propriedade.produtor'])
                ->whereHas('propriedade', function ($query) use ($produtorId) {
                    $query->where('produtor_id', $produtorId);
                })
                ->orderBy('especie')
                ->get()
                ->toArray();

            $dadosFormatados = $animalDataProvider->formatData($rebanhos);

            // Buscar informações do produtor
            $produtor = \App\Models\ProdutorRural::find($produtorId);

            $formato = $request->get('formato', 'pdf');

            // Registrar atividade
            $this->registrarAtividadeRelatorio('Rebanhos por Produtor', $formato, count($dadosFormatados));

            if ($formato === 'excel') {
                return $this->exportarExcelRebanhosPorProdutor($dadosFormatados, $produtor);
            } elseif ($formato === 'csv') {
                return $this->exportarCsvRebanhosPorProdutor($dadosFormatados, $produtor);
            } else {
                return $this->exportarPdfRebanhosPorProdutor($dadosFormatados, $produtor);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar PDF - Rebanhos por Produtor
     */
    private function exportarPdfRebanhosPorProdutor(array $dados, $produtor)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Rebanhos por Produtor');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Informações do produtor
            if ($produtor) {
                $sheet->setCellValue('A2', 'Produtor: ' . $produtor->nome);
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:H3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalRebanhos = count($dados);
            $totalAnimais = collect($dados)->sum('quantidade');
            $totalEspecies = collect($dados)->pluck('especie')->unique()->count();
            $totalPropriedades = collect($dados)->pluck('propriedade')->unique()->count();

            $linhaResumo = 4;
            $sheet->setCellValue('A' . $linhaResumo, "Total de Rebanhos: {$totalRebanhos} | Total de Animais: {$totalAnimais} | Total de Espécies: {$totalEspecies} | Total de Propriedades: {$totalPropriedades}");
            $sheet->mergeCells('A' . $linhaResumo . ':H' . $linhaResumo);
            $sheet->getStyle('A' . $linhaResumo)->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A' . $linhaResumo)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Quantidade');
            $sheet->setCellValue('C' . $linhaAtual, 'Finalidade');
            $sheet->setCellValue('D' . $linhaAtual, 'Última Atualização');
            $sheet->setCellValue('E' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('F' . $linhaAtual, 'Município');
            $sheet->setCellValue('G' . $linhaAtual, 'UF');
            $sheet->setCellValue('H' . $linhaAtual, 'Área Total (ha)');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['especie']);
                $sheet->setCellValue('B' . $linhaAtual, $item['quantidade']);
                $sheet->setCellValue('C' . $linhaAtual, $item['finalidade']);
                $sheet->setCellValue('D' . $linhaAtual, $item['ultima_atualizacao']);
                $sheet->setCellValue('E' . $linhaAtual, $item['propriedade_nome']);
                $sheet->setCellValue('F' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('G' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('H' . $linhaAtual, $item['area_total']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(8);
            $sheet->getColumnDimension('H')->setWidth(18);

            // Bordas
            $sheet->getStyle('A6:H' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar PDF
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            $filename = 'relatorio_rebanhos_produtor_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Excel - Rebanhos por Produtor
     */
    private function exportarExcelRebanhosPorProdutor(array $dados, $produtor)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar página
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setRight(0.5)
                ->setLeft(0.5)
                ->setBottom(0.5);

            // Título principal
            $sheet->setCellValue('A1', 'Relatório de Rebanhos por Produtor');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()
                ->setBold(true)
                ->setSize(16)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Informações do produtor
            if ($produtor) {
                $sheet->setCellValue('A2', 'Produtor: ' . $produtor->nome);
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->getFont()
                    ->setBold(true)
                    ->setSize(12)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
                $sheet->getStyle('A2')->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            // Data de geração
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A3:H3');
            $sheet->getStyle('A3')->getFont()
                ->setSize(10)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('999999'));
            $sheet->getStyle('A3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Resumo
            $totalRebanhos = count($dados);
            $totalAnimais = collect($dados)->sum('quantidade');
            $totalEspecies = collect($dados)->pluck('especie')->unique()->count();
            $totalPropriedades = collect($dados)->pluck('propriedade')->unique()->count();

            $linhaResumo = 4;
            $sheet->setCellValue('A' . $linhaResumo, "Total de Rebanhos: {$totalRebanhos} | Total de Animais: {$totalAnimais} | Total de Espécies: {$totalEspecies} | Total de Propriedades: {$totalPropriedades}");
            $sheet->mergeCells('A' . $linhaResumo . ':H' . $linhaResumo);
            $sheet->getStyle('A' . $linhaResumo)->getFont()
                ->setBold(true)
                ->setSize(11)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle('A' . $linhaResumo)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Cabeçalho da tabela
            $linhaAtual = 6;
            $sheet->setCellValue('A' . $linhaAtual, 'Espécie');
            $sheet->setCellValue('B' . $linhaAtual, 'Quantidade');
            $sheet->setCellValue('C' . $linhaAtual, 'Finalidade');
            $sheet->setCellValue('D' . $linhaAtual, 'Última Atualização');
            $sheet->setCellValue('E' . $linhaAtual, 'Propriedade');
            $sheet->setCellValue('F' . $linhaAtual, 'Município');
            $sheet->setCellValue('G' . $linhaAtual, 'UF');
            $sheet->setCellValue('H' . $linhaAtual, 'Área Total (ha)');

            // Estilo do cabeçalho
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4CAF50');
            $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Dados
            $linhaAtual++;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['especie']);
                $sheet->setCellValue('B' . $linhaAtual, $item['quantidade']);
                $sheet->setCellValue('C' . $linhaAtual, $item['finalidade']);
                $sheet->setCellValue('D' . $linhaAtual, $item['ultima_atualizacao']);
                $sheet->setCellValue('E' . $linhaAtual, $item['propriedade_nome']);
                $sheet->setCellValue('F' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('G' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('H' . $linhaAtual, $item['area_total']);

                // Alternar cor de fundo
                if ($linhaAtual % 2 == 0) {
                    $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8F9FA');
                }

                $linhaAtual++;
            }

            // Ajustar largura das colunas
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(8);
            $sheet->getColumnDimension('H')->setWidth(18);

            // Bordas
            $sheet->getStyle('A6:H' . ($linhaAtual - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

            // Gerar Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'relatorio_rebanhos_produtor_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $writer->save($tempFile);

            $content = file_get_contents($tempFile);
            unlink($tempFile);

            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar CSV - Rebanhos por Produtor
     */
    private function exportarCsvRebanhosPorProdutor(array $dados, $produtor)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalho
            $sheet->setCellValue('A1', 'Espécie');
            $sheet->setCellValue('B1', 'Quantidade');
            $sheet->setCellValue('C1', 'Finalidade');
            $sheet->setCellValue('D1', 'Última Atualização');
            $sheet->setCellValue('E1', 'Propriedade');
            $sheet->setCellValue('F1', 'Município');
            $sheet->setCellValue('G1', 'UF');
            $sheet->setCellValue('H1', 'Área Total (ha)');

            // Dados
            $linhaAtual = 2;
            foreach ($dados as $item) {
                $sheet->setCellValue('A' . $linhaAtual, $item['especie']);
                $sheet->setCellValue('B' . $linhaAtual, $item['quantidade']);
                $sheet->setCellValue('C' . $linhaAtual, $item['finalidade']);
                $sheet->setCellValue('D' . $linhaAtual, $item['ultima_atualizacao']);
                $sheet->setCellValue('E' . $linhaAtual, $item['propriedade_nome']);
                $sheet->setCellValue('F' . $linhaAtual, $item['municipio']);
                $sheet->setCellValue('G' . $linhaAtual, $item['uf']);
                $sheet->setCellValue('H' . $linhaAtual, $item['area_total']);
                $linhaAtual++;
            }

            // Gerar CSV
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            $filename = 'relatorio_rebanhos_produtor_' . now()->format('Y_m_d_H_i_s') . '.csv';
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
                'message' => 'Erro ao gerar CSV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar atividade de relatório gerado
     */
    private function registrarAtividadeRelatorio($tipoRelatorio, $formato, $totalRegistros = null)
    {
        try {
            AtividadeSistema::registrarRelatorio($tipoRelatorio, $formato, $totalRegistros);
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o processo
            \Log::warning('Erro ao registrar atividade de relatório: ' . $e->getMessage());
        }
    }

    /**
     * Buscar propriedades para autocomplete
     */
    public function buscarPropriedades(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $query = \App\Models\Propriedade::select('id', 'nome', 'municipio', 'uf')
                ->orderBy('nome');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nome', 'ILIKE', "%{$search}%")
                      ->orWhere('municipio', 'ILIKE', "%{$search}%");
                });
            }

            $propriedades = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $propriedades->map(function ($propriedade) {
                    return [
                        'id' => $propriedade->id,
                        'nome' => $propriedade->nome,
                        'municipio' => $propriedade->municipio,
                        'uf' => $propriedade->uf,
                        'label' => "{$propriedade->nome} - {$propriedade->municipio}/{$propriedade->uf}"
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar propriedades',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
