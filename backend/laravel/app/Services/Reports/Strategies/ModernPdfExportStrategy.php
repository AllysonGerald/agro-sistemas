<?php

namespace App\Services\Reports\Strategies;

use App\Services\Reports\Contracts\ExportStrategyInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ModernPdfExportStrategy implements ExportStrategyInterface
{
    public function export(array $data, string $filename, array $options = []): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar página
        $this->setupPage($sheet);

        // Adicionar cabeçalho moderno
        $this->addModernHeader($sheet, $options);

        // Adicionar dados em formato de cards
        $this->addModernData($sheet, $data, $options);

        // Aplicar formatação final
        $this->applyModernFormatting($sheet, $data);

        // Gerar PDF
        $writer = new Mpdf($spreadsheet);
        $filePath = tempnam(sys_get_temp_dir(), 'modern_pdf_report') . '.pdf';
        $writer->save($filePath);

        return response()->download($filePath, $filename . '.pdf', [
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
        ])->deleteFileAfterSend(true);
    }

    public function getMimeType(): string
    {
        return 'application/pdf';
    }

    public function getFileExtension(): string
    {
        return 'pdf';
    }

    public function getFormatName(): string
    {
        return 'PDF Moderno';
    }

    private function setupPage($sheet): void
    {
        // Configurar margens otimizadas
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);

        // Orientação paisagem para melhor aproveitamento
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    }

    private function addModernHeader($sheet, array $options): void
    {
        $title = $options['title'] ?? 'Relatório de Produtores Rurais';
        $subtitle = $options['subtitle'] ?? 'Sistema Agropecuário';

        // Título principal com design moderno
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:L1');

        // Configurar estilo do título com cor branca forçada
        $sheet->getStyle('A1')->getFont()
            ->setBold(true)
            ->setSize(24)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1B5E20'); // Verde escuro elegante
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(50);

        // Aplicar cor branca explicitamente na célula inteira
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1B5E20']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Subtítulo e data
        $sheet->setCellValue('A2', $subtitle . ' • Gerado em: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()
            ->setSize(14)
            ->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(30);

        // Linha separadora elegante
        $sheet->setCellValue('A3', '');
        $sheet->mergeCells('A3:L3');
        $sheet->getStyle('A3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');
        $sheet->getRowDimension(3)->setRowHeight(3);
    }

    private function addModernData($sheet, array $data, array $options): void
    {
        $startRow = 5;
        $currentRow = $startRow;

        if (empty($data)) {
            $this->addNoDataMessage($sheet, $currentRow);
            return;
        }

        // Adicionar cabeçalhos das colunas
        $this->addColumnHeaders($sheet, $currentRow);
        $currentRow += 2;

        // Adicionar dados em formato de tabela moderna
        foreach ($data as $index => $item) {
            $this->addDataRow($sheet, $item, $currentRow, $index);
            $currentRow += 2; // Espaçamento entre linhas
        }
    }

    private function addColumnHeaders($sheet, int $row): void
    {
        $headers = [
            'A' => 'Nome da Propriedade',
            'B' => 'Município',
            'C' => 'Estado',
            'D' => 'Inscrição Estadual',
            'E' => 'Área Total (ha)',
            'F' => 'Produtor',
            'G' => 'CPF/CNPJ',
            'H' => 'E-mail',
            'I' => 'Telefone',
            'J' => 'Endereço',
            'K' => 'Unidades',
            'L' => 'Rebanhos'
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()
                ->setBold(true)
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C'); // Verde médio
            $sheet->getStyle($col . $row)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            // Bordas bem definidas para cabeçalhos
            $sheet->getStyle($col . $row)->getBorders()->getTop()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle($col . $row)->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));
            $sheet->getStyle($col . $row)->getBorders()->getLeft()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($col . $row)->getBorders()->getRight()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        }

        $sheet->getRowDimension($row)->setRowHeight(35);
    }

    private function addDataRow($sheet, array $item, int $row, int $index): void
    {
        $isEven = ($index % 2) == 0;
        $bgColor = $isEven ? 'F8F9FA' : 'FFFFFF';
        $textColor = '212121';

        // Nome da Propriedade
        $sheet->setCellValue('A' . $row, $item['nome_propriedade'] ?? '');
        $this->formatDataCell($sheet, 'A' . $row, $bgColor, $textColor, 14);

        // Município
        $sheet->setCellValue('B' . $row, $item['municipio'] ?? '');
        $this->formatDataCell($sheet, 'B' . $row, $bgColor, $textColor, 12);

        // Estado
        $sheet->setCellValue('C' . $row, $item['uf'] ?? '');
        $this->formatDataCell($sheet, 'C' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        // Inscrição Estadual
        $sheet->setCellValue('D' . $row, $item['inscricao_estadual'] ?? '');
        $this->formatDataCell($sheet, 'D' . $row, $bgColor, $textColor, 10);

        // Área Total
        $sheet->setCellValue('E' . $row, $item['area_total'] ?? '0,00');
        $this->formatDataCell($sheet, 'E' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        // Produtor
        $sheet->setCellValue('F' . $row, $item['produtor_nome'] ?? '');
        $this->formatDataCell($sheet, 'F' . $row, $bgColor, $textColor, 12);

        // CPF/CNPJ do Produtor
        $sheet->setCellValue('G' . $row, $item['produtor_cpf_cnpj'] ?? '');
        $this->formatDataCell($sheet, 'G' . $row, $bgColor, $textColor, 10);

        // E-mail do Produtor
        $sheet->setCellValue('H' . $row, $item['produtor_email'] ?? '');
        $this->formatDataCell($sheet, 'H' . $row, $bgColor, $textColor, 10);

        // Telefone do Produtor
        $sheet->setCellValue('I' . $row, $item['produtor_telefone'] ?? '');
        $this->formatDataCell($sheet, 'I' . $row, $bgColor, $textColor, 10);

        // Endereço do Produtor
        $sheet->setCellValue('J' . $row, $item['produtor_endereco'] ?? '');
        $this->formatDataCell($sheet, 'J' . $row, $bgColor, $textColor, 10);

        // Total de Unidades
        $unidades = $item['total_unidades'] ?? 0;
        $sheet->setCellValue('K' . $row, $unidades);
        $this->formatDataCell($sheet, 'K' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        // Total de Rebanhos
        $rebanhos = $item['total_rebanhos'] ?? 0;
        $sheet->setCellValue('L' . $row, $rebanhos);
        $this->formatDataCell($sheet, 'L' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension($row)->setRowHeight(40);
    }

    private function formatDataCell($sheet, string $cell, string $bgColor, string $textColor, int $fontSize, string $alignment = Alignment::HORIZONTAL_LEFT): void
    {
        $sheet->getStyle($cell)->getFont()
            ->setSize($fontSize)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($textColor));
        $sheet->getStyle($cell)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($bgColor);
        $sheet->getStyle($cell)->getAlignment()
            ->setHorizontal($alignment)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Bordas bem definidas para células de dados
        $sheet->getStyle($cell)->getBorders()->getTop()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
        $sheet->getStyle($cell)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
        $sheet->getStyle($cell)->getBorders()->getLeft()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
        $sheet->getStyle($cell)->getBorders()->getRight()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
    }

    private function addNoDataMessage($sheet, int $row): void
    {
        $sheet->setCellValue('A' . $row, 'Nenhum dado encontrado para exibir');
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->getStyle('A' . $row)->getFont()
            ->setSize(16)
            ->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
        $sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(50);
    }

    private function applyModernFormatting($sheet, array $data): void
    {
        // Ajustar largura das colunas para layout moderno
        $sheet->getColumnDimension('A')->setWidth(25); // Nome da Propriedade
        $sheet->getColumnDimension('B')->setWidth(20); // Município
        $sheet->getColumnDimension('C')->setWidth(10); // Estado
        $sheet->getColumnDimension('D')->setWidth(20); // Inscrição Estadual
        $sheet->getColumnDimension('E')->setWidth(15); // Área Total
        $sheet->getColumnDimension('F')->setWidth(25); // Produtor
        $sheet->getColumnDimension('G')->setWidth(20); // CPF/CNPJ
        $sheet->getColumnDimension('H')->setWidth(30); // E-mail
        $sheet->getColumnDimension('I')->setWidth(15); // Telefone
        $sheet->getColumnDimension('J')->setWidth(35); // Endereço
        $sheet->getColumnDimension('K')->setWidth(12); // Unidades
        $sheet->getColumnDimension('L')->setWidth(12); // Rebanhos

        // Aplicar formatação geral
        $sheet->getStyle('A:L')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Adicionar bordas externas bem definidas
        $lastRow = $sheet->getHighestRow();

        // Borda externa principal
        $sheet->getStyle('A1:H' . $lastRow)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1B5E20'));

        // Bordas internas para melhor definição
        $sheet->getStyle('A1:H' . $lastRow)->getBorders()->getInside()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E0E0E0'));

        // Garantir que todas as células tenham bordas consistentes
        for ($row = 1; $row <= $lastRow; $row++) {
            for ($col = 'A'; $col <= 'H'; $col++) {
                $cell = $col . $row;
                $sheet->getStyle($cell)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
            }
        }
    }
}
