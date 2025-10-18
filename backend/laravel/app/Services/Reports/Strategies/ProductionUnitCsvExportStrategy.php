<?php

namespace App\Services\Reports\Strategies;

use App\Services\Reports\Contracts\ExportStrategyInterface;
use Illuminate\Http\Response;

class ProductionUnitCsvExportStrategy implements ExportStrategyInterface
{
    public function export(array $data, string $filename, array $options = []): Response
    {
        $csvData = $this->convertToCsv($data);

        return response($csvData, 200, [
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
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

        $output = fopen('php://temp', 'r+');

        // Adicionar cabeçalhos fixos
        $headers = [
            'Nome',
            'Área (ha)',
            'Coordenadas',
            'Propriedade',
            'Município',
            'Data Cadastro'
        ];
        fputcsv($output, $headers, ';');

        // Adicionar dados formatados
        foreach ($data as $item) {
            $row = [
                $item['cultura'] ?? '',
                $item['area_ha'] ?? '0,00',
                $item['coordenadas'] ?? '-',
                $item['propriedade'] ?? '',
                $item['municipio'] ?? '',
                $item['data_cadastro'] ?? ''
            ];

            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
