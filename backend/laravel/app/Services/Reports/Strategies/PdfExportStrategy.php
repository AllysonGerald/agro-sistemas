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

class PdfExportStrategy implements ExportStrategyInterface
{
    public function export(array $data, string $filename, array $options = []): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar margens e orientação
        $this->configurePageSettings($sheet);

        // Adicionar cabeçalho
        $this->addHeader($sheet, $options['title'] ?? 'Relatório', $options['subtitle'] ?? '');

        // Adicionar dados
        $this->addData($sheet, $data, $options);

        // Aplicar formatação
        $this->applyFormatting($sheet, $data);

        // Gerar PDF
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
        $sheet->getPageMargins()->setTop(1.5);
        $sheet->getPageMargins()->setRight(0.75);
        $sheet->getPageMargins()->setLeft(0.75);
        $sheet->getPageMargins()->setBottom(1.5);
    }

    private function addHeader($sheet, string $title, string $subtitle): void
    {
        // Título principal com melhor legibilidade
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:G1'); // Expandido para cobrir todas as colunas
        $sheet->getStyle('A1')->getFont()
            ->setBold(true)
            ->setSize(18) // Reduzido de 20 para 18 para melhor proporção
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2E7D32');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(40); // Aumentado para 40

        // Data de geração com melhor legibilidade
        $sheet->setCellValue('A2', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:G2'); // Expandido para cobrir todas as colunas
        $sheet->getStyle('A2')->getFont()
            ->setSize(12) // Aumentado de 11 para 12
            ->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('333333')); // Cor mais escura para melhor contraste
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(25); // Aumentado de 20 para 25
    }

    private function addData($sheet, array $data, array $options): void
    {
        $startRow = 4;
        $currentRow = $startRow;

        // Adicionar cabeçalhos das colunas se existirem
        if (isset($data[0]) && is_array($data[0])) {
            $headers = array_keys($data[0]);
            $col = 'A';

            // Formatar cabeçalhos com melhor legibilidade
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $currentRow, $this->formatHeader($header));
                $sheet->getStyle($col . $currentRow)->getFont()
                    ->setBold(true)
                    ->setSize(14) // Aumentado de 12 para 14
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle($col . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('2E7D32'); // Verde mais escuro para melhor contraste
                $sheet->getStyle($col . $currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($col . $currentRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $col++;
            }
            $sheet->getRowDimension($currentRow)->setRowHeight(35); // Aumentado de 25 para 35
            $currentRow++;

            // Adicionar dados com formatação melhorada
            $rowIndex = 0;
            foreach ($data as $row) {
                $col = 'A';
                $isEvenRow = ($rowIndex % 2) == 0;

                foreach ($row as $value) {
                    $sheet->setCellValue($col . $currentRow, $value);

                    // Formatação da célula com melhor legibilidade
                    $sheet->getStyle($col . $currentRow)->getFont()
                        ->setSize(12) // Aumentado de 10 para 12
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000')); // Preto puro para melhor contraste
                    $sheet->getStyle($col . $currentRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($isEvenRow ? 'F8F9FA' : 'FFFFFF');
                    $sheet->getStyle($col . $currentRow)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT); // Alinhamento à esquerda para melhor leitura
                    $sheet->getStyle($col . $currentRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));

                    $col++;
                }
                $sheet->getRowDimension($currentRow)->setRowHeight(30); // Aumentado de 20 para 30
                $currentRow++;
                $rowIndex++;
            }
        }
    }

    private function applyFormatting($sheet, array $data): void
    {
        // Ajustar largura das colunas com valores específicos para melhor legibilidade
        $sheet->getColumnDimension('A')->setWidth(25); // Nome - mais largo
        $sheet->getColumnDimension('B')->setWidth(18); // CPF/CNPJ
        $sheet->getColumnDimension('C')->setWidth(30); // E-mail - mais largo
        $sheet->getColumnDimension('D')->setWidth(15); // Telefone
        $sheet->getColumnDimension('E')->setWidth(40); // Endereço - mais largo
        $sheet->getColumnDimension('F')->setWidth(20); // Total Propriedades
        $sheet->getColumnDimension('G')->setWidth(18); // Total Hectares

        // Aplicar formatação geral
        $sheet->getStyle('A:Z')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Adicionar bordas externas mais suaves
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $lastCol . $lastRow)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E7D32'));

        // Configurar orientação da página para melhor aproveitamento
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Ajustar margens para melhor aproveitamento do espaço
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
    }

    private function formatHeader(string $header): string
    {
        $headers = [
            'nome' => 'Nome',
            'cpf_cnpj' => 'CPF/CNPJ',
            'email' => 'E-mail',
            'telefone' => 'Telefone',
            'endereco' => 'Endereço',
            'total_propriedades' => 'Total de Propriedades',
            'total_hectares' => 'Total de Hectares',
            'municipio' => 'Município',
            'uf' => 'Estado',
            'especie' => 'Espécie',
            'quantidade' => 'Quantidade',
            'finalidade' => 'Finalidade',
            'propriedade' => 'Propriedade',
            'cultura' => 'Cultura',
            'area_total' => 'Área Total (ha)',
            'produtor' => 'Produtor',
        ];

        return $headers[$header] ?? ucfirst(str_replace('_', ' ', $header));
    }
}
