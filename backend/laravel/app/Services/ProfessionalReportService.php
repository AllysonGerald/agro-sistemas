<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ProfessionalReportService
{
    /**
     * Adiciona cabeçalho profissional ao relatório
     */
    public function addProfessionalHeader($sheet, $propriedadeNome, $propriedadeInfo = null)
    {
        // Linha 1-2: Informações da propriedade/empresa
        $sheet->setCellValue('A1', $propriedadeNome);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        if ($propriedadeInfo) {
            $sheet->setCellValue('A2', $propriedadeInfo);
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2')->getFont()->setSize(9);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // Linha separadora verde
        $sheet->getStyle('A3:H3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF16a34a');
        $sheet->getRowDimension(3)->setRowHeight(3);

        return 4; // Retorna a próxima linha disponível
    }

    /**
     * Adiciona título do relatório
     */
    public function addReportTitle($sheet, $titulo, $subtitulo, $linhaInicio)
    {
        // Espaço antes do título
        $linhaInicio++;

        // Título principal
        $sheet->setCellValue('A' . $linhaInicio, $titulo);
        $sheet->mergeCells('A' . $linhaInicio . ':H' . $linhaInicio);
        $sheet->getStyle('A' . $linhaInicio)->getFont()
            ->setBold(true)
            ->setSize(18)
            ->getColor()->setARGB('FF16a34a');
        $sheet->getStyle('A' . $linhaInicio)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($linhaInicio)->setRowHeight(30);

        // Subtítulo
        $linhaSubtitulo = $linhaInicio + 1;
        $sheet->setCellValue('A' . $linhaSubtitulo, $subtitulo);
        $sheet->mergeCells('A' . $linhaSubtitulo . ':H' . $linhaSubtitulo);
        $sheet->getStyle('A' . $linhaSubtitulo)->getFont()->setSize(10)->getColor()->setARGB('FF64748b');
        $sheet->getStyle('A' . $linhaSubtitulo)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Linha separadora
        $linhaSeparador = $linhaSubtitulo + 1;
        $sheet->getStyle('A' . $linhaSeparador . ':H' . $linhaSeparador)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF16a34a');
        $sheet->getRowDimension($linhaSeparador)->setRowHeight(3);

        return $linhaSeparador + 2; // Retorna a próxima linha com espaçamento
    }

    /**
     * Adiciona informações do usuário e data
     */
    public function addUserInfo($sheet, $usuarioNome, $linha)
    {
        $dataGeracao = now()->format('d/m/Y H:i');
        $info = "Usuário: {$usuarioNome}     Data da Geração: {$dataGeracao}";

        $sheet->setCellValue('A' . $linha, $info);
        $sheet->mergeCells('A' . $linha . ':H' . $linha);
        $sheet->getStyle('A' . $linha)->getFont()->setSize(9)->getColor()->setARGB('FF64748b');
        $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return $linha + 1;
    }

    /**
     * Adiciona cards de resumo coloridos com larguras customizadas
     */
    public function addSummaryCards($sheet, $cards, $linhaInicio)
    {
        $linha = $linhaInicio;

        // Distribuição de colunas: [3, 2, 3] = 8 colunas total (3 cards apenas)
        $larguraCards = [3, 2, 3]; // TOTAL RECEITAS: 3 cols, DESPESAS: 2 cols, LUCRO: 3 cols

        $colunaAtual = 1;
        foreach ($cards as $index => $card) {
            $largura = $larguraCards[$index] ?? 2;
            $colunaInicio = $colunaAtual;
            $colunaFim = $colunaInicio + $largura - 1;
            $colunaAtual += $largura;

            // Converter índice de coluna para letra
            $letraInicio = chr(64 + $colunaInicio);
            $letraFim = chr(64 + $colunaFim);

            // Label do card
            $sheet->setCellValue($letraInicio . $linha, $card['label']);
            $sheet->mergeCells($letraInicio . $linha . ':' . $letraFim . $linha);
            $sheet->getStyle($letraInicio . $linha)->getFont()
                ->setSize(9)
                ->setBold(true)
                ->getColor()->setARGB('FF1e293b');
            $sheet->getStyle($letraInicio . $linha)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true); // Permitir quebra de linha

            // Cor de fundo do card
            $sheet->getStyle($letraInicio . $linha . ':' . $letraFim . $linha)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($card['cor']);

            // Borda do card
            $sheet->getStyle($letraInicio . $linha . ':' . $letraFim . $linha)->getBorders()
                ->getOutline()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('FFCCCCCC'));

            // Valor do card
            $linhaValor = $linha + 1;
            $sheet->setCellValue($letraInicio . $linhaValor, $card['valor']);
            $sheet->mergeCells($letraInicio . $linhaValor . ':' . $letraFim . $linhaValor);
            $sheet->getStyle($letraInicio . $linhaValor)->getFont()
                ->setSize(16)
                ->setBold(true)
                ->getColor()->setARGB('FF16a34a');
            $sheet->getStyle($letraInicio . $linhaValor)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($letraInicio . $linhaValor . ':' . $letraFim . $linhaValor)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($card['cor']);

            // Borda do valor
            $sheet->getStyle($letraInicio . $linhaValor . ':' . $letraFim . $linhaValor)->getBorders()
                ->getOutline()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('FFCCCCCC'));
        }

        // Ajustar altura das linhas
        $sheet->getRowDimension($linha)->setRowHeight(28);
        $sheet->getRowDimension($linha + 1)->setRowHeight(40);

        return $linha + 4; // Pula 2 linhas do card + 2 de espaço
    }

    /**
     * Adiciona cabeçalho de tabela verde
     */
    public function addTableHeader($sheet, $colunas, $linha)
    {
        $colIndex = 0;
        foreach ($colunas as $coluna) {
            $letra = chr(65 + $colIndex);
            if (!empty($coluna)) { // Só adiciona se não for vazio
                $sheet->setCellValue($letra . $linha, $coluna);
            }
            $colIndex++;
        }

        // Estilo do cabeçalho (sempre aplicar em todas as 8 colunas para manter alinhamento)
        $sheet->getStyle('A' . $linha . ':H' . $linha)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF16a34a']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFFFFFFF']
                ]
            ]
        ]);

        $sheet->getRowDimension($linha)->setRowHeight(30);

        return $linha + 1;
    }

    /**
     * Adiciona linha de dados na tabela com cores alternadas (sempre 8 colunas)
     */
    public function addTableRow($sheet, $dados, $linha, $isAlternate = false)
    {
        $colIndex = 0;
        foreach ($dados as $key => $valor) {
            $letra = chr(65 + $colIndex);
            $sheet->setCellValue($letra . $linha, $valor);

            // Alinhamento especial para valores monetários
            if (is_numeric($valor) || strpos($valor, 'R$') !== false) {
                $sheet->getStyle($letra . $linha)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            $colIndex++;
        }

        // Cor de fundo alternada (sempre 8 colunas para alinhar com cards)
        if ($isAlternate) {
            $sheet->getStyle('A' . $linha . ':H' . $linha)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF9FAFB');
        }

        return $linha + 1;
    }

    /**
     * Adiciona rodapé do relatório
     */
    public function addFooter($sheet, $linha)
    {
        $linha = $linha + 2;
        $texto = "Relatório gerado automaticamente pelo Sistema AgroSis.\n" .
                "Este documento contém informações confidenciais e de uso restrito do sistema.";

        $sheet->setCellValue('A' . $linha, $texto);
        $sheet->mergeCells('A' . $linha . ':H' . ($linha + 1));
        $sheet->getStyle('A' . $linha)->getFont()->setSize(9)->getColor()->setARGB('FF9CA3AF');
        $sheet->getStyle('A' . $linha)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension($linha)->setRowHeight(35);

        return $linha + 2;
    }

    /**
     * Aplica bordas à tabela (sempre 8 colunas para alinhar com cards)
     */
    public function applyTableBorders($sheet, $linhaInicio, $linhaFim, $numColunas = 8)
    {
        // Sempre usar 8 colunas (A até H) para alinhar com os cards
        $sheet->getStyle('A' . $linhaInicio . ':H' . $linhaFim)->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('FFCCCCCC'));
    }

    /**
     * Ajusta largura das colunas
     */
    public function autoSizeColumns($sheet, $numColunas)
    {
        for ($i = 0; $i < $numColunas; $i++) {
            $letra = chr(65 + $i);
            $sheet->getColumnDimension($letra)->setAutoSize(true);
        }
    }
}

