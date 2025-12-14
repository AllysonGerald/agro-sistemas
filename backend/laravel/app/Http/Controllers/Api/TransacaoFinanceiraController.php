<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransacaoFinanceira;
use App\Services\TransacaoFinanceiraService;
use App\Services\AtividadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @tags Financeiro
 */
class TransacaoFinanceiraController extends Controller
{
    public function __construct(
        private TransacaoFinanceiraService $transacaoService,
        private AtividadeService $atividadeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search',
                'tipo',
                'categoria_id',
                'propriedade_id',
                'status',
                'animal_id',
                'lote_id',
                'data_inicio',
                'data_fim',
                'valor_min',
                'valor_max'
            ]);

            $perPage = $request->get('per_page', 15);

            $transacoes = $this->transacaoService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $transacoes->items(),
                    'current_page' => $transacoes->currentPage(),
                    'last_page' => $transacoes->lastPage(),
                    'per_page' => $transacoes->perPage(),
                    'total' => $transacoes->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar transações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'descricao' => 'required|string|max:255',
                'tipo' => 'required|in:receita,despesa',
                'categoria_id' => 'required|exists:categorias_financeiras,id',
                'valor' => 'required|numeric|min:0.01',
                'data' => 'required|date',
                'animal_id' => 'nullable|exists:animais,id',
                'lote_id' => 'nullable|exists:lotes,id',
                'propriedade_id' => 'required|exists:propriedades,id',
                'forma_pagamento' => 'nullable|string',
                'status' => 'nullable|in:pendente,pago,cancelado',
                'observacoes' => 'nullable|string',
                'comprovante_url' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transacao = $this->transacaoService->create($validator->validated());

            $this->atividadeService->registrar([
                'tipo' => 'transacao_financeira',
                'descricao' => "Transação {$transacao->tipo} criada: {$transacao->descricao}",
                'modelo' => TransacaoFinanceira::class,
                'modelo_id' => $transacao->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transação criada com sucesso',
                'data' => $transacao->load(['categoria', 'animal', 'lote', 'propriedade'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar transação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $transacao = $this->transacaoService->findById($id);

            if (!$transacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transação não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $transacao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar transação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $transacao = TransacaoFinanceira::find($id);

            if (!$transacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transação não encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'descricao' => 'sometimes|required|string|max:255',
                'tipo' => 'sometimes|required|in:receita,despesa',
                'categoria_id' => 'sometimes|required|exists:categorias_financeiras,id',
                'valor' => 'sometimes|required|numeric|min:0.01',
                'data' => 'sometimes|required|date',
                'animal_id' => 'nullable|exists:animais,id',
                'lote_id' => 'nullable|exists:lotes,id',
                'propriedade_id' => 'sometimes|required|exists:propriedades,id',
                'forma_pagamento' => 'nullable|string',
                'status' => 'nullable|in:pendente,pago,cancelado',
                'observacoes' => 'nullable|string',
                'comprovante_url' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->transacaoService->update($transacao, $validator->validated());

            $this->atividadeService->registrar([
                'tipo' => 'transacao_atualizada',
                'descricao' => "Transação atualizada: {$transacao->descricao}",
                'modelo' => TransacaoFinanceira::class,
                'modelo_id' => $transacao->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transação atualizada com sucesso',
                'data' => $transacao->fresh()->load(['categoria', 'animal', 'lote', 'propriedade'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar transação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $transacao = TransacaoFinanceira::find($id);

            if (!$transacao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transação não encontrada'
                ], 404);
            }

            $descricao = $transacao->descricao;
            
            $this->transacaoService->delete($transacao);

            $this->atividadeService->registrar([
                'tipo' => 'transacao_excluida',
                'descricao' => "Transação excluída: {$descricao}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transação excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir transação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $propriedadeId = $request->get('propriedade_id');
            $periodo = $request->get('periodo', 'mes'); // dia, semana, mes, ano
            
            $dashboard = $this->transacaoService->getDashboard($propriedadeId, $periodo);

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function estatisticasPeriodo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'propriedade_id' => 'nullable|exists:propriedades,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $estatisticas = $this->transacaoService->getEstatisticasPorPeriodo(
                $request->data_inicio,
                $request->data_fim,
                $request->propriedade_id
            );

            return response()->json([
                'success' => true,
                'data' => $estatisticas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function gerarPDF(Request $request)
    {
        try {
            $filters = $request->only([
                'data_inicio',
                'data_fim',
                'tipo',
                'categoria_id',
                'propriedade_id'
            ]);

            $transacoes = TransacaoFinanceira::with(['categoria', 'propriedade', 'animal', 'lote'])
                ->when($filters['data_inicio'] ?? null, function ($query, $dataInicio) {
                    $query->whereDate('data', '>=', $dataInicio);
                })
                ->when($filters['data_fim'] ?? null, function ($query, $dataFim) {
                    $query->whereDate('data', '<=', $dataFim);
                })
                ->when($filters['tipo'] ?? null, function ($query, $tipo) {
                    $query->where('tipo', $tipo);
                })
                ->when($filters['categoria_id'] ?? null, function ($query, $categoriaId) {
                    $query->where('categoria_id', $categoriaId);
                })
                ->when($filters['propriedade_id'] ?? null, function ($query, $propriedadeId) {
                    $query->where('propriedade_id', $propriedadeId);
                })
                ->orderBy('data', 'desc')
                ->get();

            // Calcular totais
            $totalReceitas = $transacoes->where('tipo', 'receita')->sum('valor');
            $totalDespesas = $transacoes->where('tipo', 'despesa')->sum('valor');
            $saldo = $totalReceitas - $totalDespesas;
            $totalTransacoes = $transacoes->count();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $reportService = new \App\Services\ProfessionalReportService();

            // Obter informações da propriedade/usuário
            $user = auth()->user();
            $propriedadeNome = 'Rancho Fundo De Minas';
            $propriedadeInfo = 'CNPJ: 01.123.456/0001-00 | Telefone: (31) 99999-9999 | Endereço: Rod. Feliciano, 800 - CRZ - Montalvânia-MG';

            // 1. Cabeçalho profissional
            $linhaAtual = $reportService->addProfessionalHeader($sheet, $propriedadeNome, $propriedadeInfo);
            
            // 2. Título do relatório (com total de transações)
            $subtitulo = 'Sistema de Gestão Pecuária - AgroSis | Total de Transações: ' . $totalTransacoes;
            $linhaAtual = $reportService->addReportTitle($sheet, 'Relatório Financeiro', $subtitulo, $linhaAtual + 1);
            
            // 3. Cards de resumo (apenas 3 cards: Receitas, Despesas e Lucro)
            $cards = [
                [
                    'label' => "TOTAL\nRECEITAS",
                    'valor' => 'R$ ' . number_format($totalReceitas, 2, ',', '.'),
                    'cor' => 'FFD4F4DD' // Verde claro
                ],
                [
                    'label' => "TOTAL\nDESPESAS",
                    'valor' => 'R$ ' . number_format($totalDespesas, 2, ',', '.'),
                    'cor' => 'FFFFD4D4' // Rosa claro
                ],
                [
                    'label' => $saldo >= 0 ? 'LUCRO' : 'PREJUÍZO',
                    'valor' => 'R$ ' . number_format(abs($saldo), 2, ',', '.'),
                    'cor' => 'FFD4E4F4' // Azul claro
                ]
            ];
            
            $linhaAtual = $reportService->addSummaryCards($sheet, $cards, $linhaAtual);
            
            // 5. Tabela de transações (Valor ocupa 3 colunas finais: F, G, H)
            $colunas = ['Data', 'Tipo', 'Categoria', 'Descrição', 'Animal', 'Valor', '', ''];
            $linhaInicio = $reportService->addTableHeader($sheet, $colunas, $linhaAtual + 1);
            
            // Merge do cabeçalho "Valor" (F-G-H)
            $sheet->mergeCells('F' . ($linhaInicio - 1) . ':H' . ($linhaInicio - 1));
            
            $linhaAtual = $linhaInicio;
            $isAlternate = false;
            
            foreach ($transacoes as $transacao) {
                $tipoFormatado = ucfirst($transacao->tipo);
                
                // Dados
                $sheet->setCellValue('A' . $linhaAtual, $transacao->data->format('d/m/Y'));
                $sheet->setCellValue('B' . $linhaAtual, $tipoFormatado);
                $sheet->setCellValue('C' . $linhaAtual, $transacao->categoria->nome ?? '-');
                $sheet->setCellValue('D' . $linhaAtual, $transacao->descricao);
                $sheet->setCellValue('E' . $linhaAtual, $transacao->animal ? "Animal {$transacao->animal->identificacao}" : '-');
                $sheet->setCellValue('F' . $linhaAtual, 'R$ ' . number_format($transacao->valor, 2, ',', '.'));
                
                // Merge "Valor" nas 3 últimas colunas (F-G-H)
                $sheet->mergeCells('F' . $linhaAtual . ':H' . $linhaAtual);
                
                // Aplicar zebra striping
                if ($isAlternate) {
                    $sheet->getStyle('A' . $linhaAtual . ':H' . $linhaAtual)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF9FAFB');
                }
                
                // Colorir o tipo (Receita = verde, Despesa = vermelho)
                if ($transacao->tipo === 'receita') {
                    $sheet->getStyle('B' . $linhaAtual)->getFont()->getColor()->setARGB('FF16a34a');
                    $sheet->getStyle('B' . $linhaAtual)->getFont()->setBold(true);
                } else {
                    $sheet->getStyle('B' . $linhaAtual)->getFont()->getColor()->setARGB('FFDC2626');
                    $sheet->getStyle('B' . $linhaAtual)->getFont()->setBold(true);
                }
                
                // Colorir o valor
                $sheet->getStyle('F' . $linhaAtual)->getFont()->getColor()
                    ->setARGB($transacao->tipo === 'receita' ? 'FF16a34a' : 'FFDC2626');
                $sheet->getStyle('F' . $linhaAtual)->getFont()->setBold(true);
                $sheet->getStyle('F' . $linhaAtual)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                
                $linhaAtual++;
                $isAlternate = !$isAlternate;
            }
            
            // 6. Aplicar bordas
            $reportService->applyTableBorders($sheet, $linhaInicio - 1, $linhaAtual - 1, count($colunas));
            
            // 7. Rodapé
            $reportService->addFooter($sheet, $linhaAtual);
            
            // 8. Ajustar colunas
            $reportService->autoSizeColumns($sheet, count($colunas));

            // Gerar PDF
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
            
            $filename = 'relatorio_financeiro_' . date('Ymd_His') . '.pdf';
            
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            $filters = $request->only([
                'data_inicio',
                'data_fim',
                'tipo',
                'categoria_id',
                'propriedade_id'
            ]);

            $transacoes = TransacaoFinanceira::with(['categoria', 'propriedade', 'animal', 'lote'])
                ->when($filters['data_inicio'] ?? null, function ($query, $dataInicio) {
                    $query->whereDate('data', '>=', $dataInicio);
                })
                ->when($filters['data_fim'] ?? null, function ($query, $dataFim) {
                    $query->whereDate('data', '<=', $dataFim);
                })
                ->when($filters['tipo'] ?? null, function ($query, $tipo) {
                    $query->where('tipo', $tipo);
                })
                ->when($filters['categoria_id'] ?? null, function ($query, $categoriaId) {
                    $query->where('categoria_id', $categoriaId);
                })
                ->when($filters['propriedade_id'] ?? null, function ($query, $propriedadeId) {
                    $query->where('propriedade_id', $propriedadeId);
                })
                ->orderBy('data', 'desc')
                ->get();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalho
            $sheet->setCellValue('A1', 'RELATÓRIO DE TRANSAÇÕES FINANCEIRAS');
            $sheet->mergeCells('A1:H1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Colunas
            $sheet->setCellValue('A3', 'Data');
            $sheet->setCellValue('B3', 'Tipo');
            $sheet->setCellValue('C3', 'Categoria');
            $sheet->setCellValue('D3', 'Descrição');
            $sheet->setCellValue('E3', 'Valor');
            $sheet->setCellValue('F3', 'Status');
            $sheet->setCellValue('G3', 'Propriedade');
            $sheet->setCellValue('H3', 'Forma Pagamento');

            $sheet->getStyle('A3:H3')->getFont()->setBold(true);
            $sheet->getStyle('A3:H3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF16a34a');
            $sheet->getStyle('A3:H3')->getFont()->getColor()->setARGB('FFFFFFFF');

            // Dados
            $row = 4;
            foreach ($transacoes as $transacao) {
                $sheet->setCellValue('A' . $row, $transacao->data->format('d/m/Y'));
                $sheet->setCellValue('B' . $row, ucfirst($transacao->tipo));
                $sheet->setCellValue('C' . $row, $transacao->categoria->nome ?? '');
                $sheet->setCellValue('D' . $row, $transacao->descricao);
                $sheet->setCellValue('E' . $row, 'R$ ' . number_format($transacao->valor, 2, ',', '.'));
                $sheet->setCellValue('F' . $row, ucfirst($transacao->status ?? 'pago'));
                $sheet->setCellValue('G' . $row, $transacao->propriedade->nome ?? '');
                $sheet->setCellValue('H' . $row, $transacao->forma_pagamento ?? '');
                $row++;
            }

            // Ajustar largura das colunas
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Gerar Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $filename = 'relatorio_transacoes_' . date('Ymd_His') . '.xlsx';
            
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarCSV(Request $request)
    {
        try {
            $filters = $request->only([
                'data_inicio',
                'data_fim',
                'tipo',
                'categoria_id',
                'propriedade_id'
            ]);

            $transacoes = TransacaoFinanceira::with(['categoria', 'propriedade', 'animal', 'lote'])
                ->when($filters['data_inicio'] ?? null, function ($query, $dataInicio) {
                    $query->whereDate('data', '>=', $dataInicio);
                })
                ->when($filters['data_fim'] ?? null, function ($query, $dataFim) {
                    $query->whereDate('data', '<=', $dataFim);
                })
                ->when($filters['tipo'] ?? null, function ($query, $tipo) {
                    $query->where('tipo', $tipo);
                })
                ->when($filters['categoria_id'] ?? null, function ($query, $categoriaId) {
                    $query->where('categoria_id', $categoriaId);
                })
                ->when($filters['propriedade_id'] ?? null, function ($query, $propriedadeId) {
                    $query->where('propriedade_id', $propriedadeId);
                })
                ->orderBy('data', 'desc')
                ->get();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Cabeçalho
            $sheet->setCellValue('A1', 'Data');
            $sheet->setCellValue('B1', 'Tipo');
            $sheet->setCellValue('C1', 'Categoria');
            $sheet->setCellValue('D1', 'Descrição');
            $sheet->setCellValue('E1', 'Valor');
            $sheet->setCellValue('F1', 'Status');
            $sheet->setCellValue('G1', 'Propriedade');
            $sheet->setCellValue('H1', 'Forma Pagamento');

            // Dados
            $row = 2;
            foreach ($transacoes as $transacao) {
                $sheet->setCellValue('A' . $row, $transacao->data->format('d/m/Y'));
                $sheet->setCellValue('B' . $row, ucfirst($transacao->tipo));
                $sheet->setCellValue('C' . $row, $transacao->categoria->nome ?? '');
                $sheet->setCellValue('D' . $row, $transacao->descricao);
                $sheet->setCellValue('E' . $row, number_format($transacao->valor, 2, ',', '.'));
                $sheet->setCellValue('F' . $row, ucfirst($transacao->status ?? 'pago'));
                $sheet->setCellValue('G' . $row, $transacao->propriedade->nome ?? '');
                $sheet->setCellValue('H' . $row, $transacao->forma_pagamento ?? '');
                $row++;
            }

            // Gerar CSV
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setSheetIndex(0);
            
            $filename = 'relatorio_transacoes_' . date('Ymd_His') . '.csv';
            
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar CSV: ' . $e->getMessage()
            ], 500);
        }
    }
}
