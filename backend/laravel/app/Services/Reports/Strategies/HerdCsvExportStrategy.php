<?php

namespace App\Services\Reports\Strategies;

use App\Services\Reports\Contracts\ExportStrategyInterface;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HerdCsvExportStrategy implements ExportStrategyInterface
{
    public function export(array $data, string $filename, array $options = []): BinaryFileResponse
    {
        $csvContent = $this->convertToCsv($data);

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tempFile, $csvContent);

        return response()->download($tempFile, $filename . '.csv', [
            'Content-Type' => $this->getMimeType(),
        ])->deleteFileAfterSend(true);
    }

    public function getMimeType(): string
    {
        return 'text/csv';
    }

    public function getFileExtension(): string
    {
        return 'csv';
    }

    public function getFormatName(): string
    {
        return 'CSV';
    }

    private function convertToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        // Cabeçalhos fixos para rebanhos
        $headers = [
            'Espécie',
            'Quantidade',
            'Finalidade',
            'Última Atualização',
            'Propriedade',
            'Município'
        ];

        $csv = '';

        // Adicionar cabeçalhos
        $csv .= '"' . implode('";"', $headers) . '"' . "\n";

        // Adicionar dados
        foreach ($data as $item) {
            $row = [
                $item['especie'] ?? '',
                number_format($item['quantidade'] ?? 0, 0, ',', '.'),
                $item['finalidade'] ?? '',
                $item['ultima_atualizacao'] ?? '',
                $item['propriedade'] ?? '',
                $item['municipio'] ?? ''
            ];

            // Escapar aspas duplas e adicionar aspas ao redor de cada campo
            $escapedRow = array_map(function($field) {
                $field = str_replace('"', '""', $field);
                return '"' . $field . '"';
            }, $row);

            $csv .= implode(';', $escapedRow) . "\n";
        }

        return $csv;
    }
}
