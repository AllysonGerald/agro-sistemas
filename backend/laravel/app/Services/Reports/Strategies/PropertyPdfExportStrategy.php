<?php

namespace App\Services\Reports\Strategies;

use App\Services\Reports\Contracts\ExportStrategyInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PropertyPdfExportStrategy implements ExportStrategyInterface
{
    public function export(array $data, string $filename, array $options = []): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar margens e orientação
        $this->configurePageSettings($sheet);

        // Adicionar cabeçalho
        $this->addHeader($sheet, $options['title'] ?? 'Relatório de Propriedades Rurais', $options['subtitle'] ?? '');

        // Adicionar dados
        $this->addData($sheet, $data, $options);

        // Aplicar formatação
        $this->applyFormatting($sheet, $data);

        // Gerar arquivo
        $writer = new Mpdf($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.pdf', [
            'Content-Type' => $this->getMimeType(),
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
        return 'PDF';
    }

    private function configurePageSettings($sheet): void
    {
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $sheet->getPageMargins()
            ->setTop(0.5)
            ->setRight(0.5)
            ->setLeft(0.5)
            ->setBottom(0.5);
    }

    private function addHeader($sheet, string $title, string $subtitle): void
    {
        // Título principal com design moderno
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:F1');

        // Configurar estilo do título com cor branca forçada
        $sheet->getStyle('A1')->getFont()
            ->setBold(true)
            ->setSize(24)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));

        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1B5E20'); // Verde escuro

        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(50);

        // Aplicar estilo novamente para garantir
        $sheet->getStyle('A1:F1')->getFont()
            ->setBold(true)
            ->setSize(24)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));

        // Subtítulo e data
        $sheet->setCellValue('A2', $subtitle . ' • Gerado em: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()
            ->setSize(14)
            ->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('666666'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(30);

        // Linha separadora elegante
        $sheet->setCellValue('A3', '');
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');
        $sheet->getRowDimension(3)->setRowHeight(3);
    }

    private function addData($sheet, array $data, array $options): void
    {
        $startRow = 5;
        $currentRow = $startRow;

        // Adicionar cabeçalhos das colunas
        if (isset($data[0]) && is_array($data[0])) {
            $this->addColumnHeaders($sheet, $currentRow);
            $currentRow += 2;

            // Adicionar dados com formatação moderna
            foreach ($data as $index => $item) {
                $this->addDataRow($sheet, $item, $currentRow, $index);
                $currentRow += 2;
            }
        }
    }

    private function addColumnHeaders($sheet, int $row): void
    {
        $headers = [
            'A' => 'Nome',
            'B' => 'Município',
            'C' => 'Estado',
            'D' => 'Inscrição Estadual',
            'E' => 'Área Total (ha)',
            'F' => 'Produtor'
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()
                ->setBold(true)
                ->setSize(12)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('388E3C');
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
        $this->formatDataCell($sheet, 'A' . $row, $bgColor, $textColor, 12);

        // Município
        $sheet->setCellValue('B' . $row, $item['municipio'] ?? '');
        $this->formatDataCell($sheet, 'B' . $row, $bgColor, $textColor, 12);

        // Estado
        $sheet->setCellValue('C' . $row, $item['uf'] ?? '');
        $this->formatDataCell($sheet, 'C' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        // Inscrição Estadual
        $sheet->setCellValue('D' . $row, $item['inscricao_estadual'] ?? '');
        $this->formatDataCell($sheet, 'D' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        // Área Total
        $area = $item['area_total'] ?? '0,00';
        $sheet->setCellValue('E' . $row, $area);
        $this->formatDataCell($sheet, 'E' . $row, $bgColor, $textColor, 12, Alignment::HORIZONTAL_CENTER);

        // Produtor (em verde)
        $sheet->setCellValue('F' . $row, $item['produtor_nome'] ?? '');
        $this->formatDataCell($sheet, 'F' . $row, $bgColor, '2E7D32', 12); // Verde para produtor

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

        // Bordas sutis para células de dados
        $sheet->getStyle($cell)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E0E0E0'));
    }

    private function applyFormatting($sheet, array $data): void
    {
        // Ajustar largura das colunas
        $sheet->getColumnDimension('A')->setWidth(25); // Nome
        $sheet->getColumnDimension('B')->setWidth(20); // Município
        $sheet->getColumnDimension('C')->setWidth(10); // Estado
        $sheet->getColumnDimension('D')->setWidth(18); // Inscrição Estadual
        $sheet->getColumnDimension('E')->setWidth(15); // Área Total
        $sheet->getColumnDimension('F')->setWidth(30); // Produtor

        // Aplicar formatação geral
        $sheet->getStyle('A:F')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Adicionar bordas externas bem definidas
        $lastRow = $sheet->getHighestRow();

        // Borda externa principal
        $sheet->getStyle('A5:F' . $lastRow)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));

        // Bordas internas
        $sheet->getStyle('A5:F' . $lastRow)->getBorders()->getInside()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E0E0E0'));
    }
}
