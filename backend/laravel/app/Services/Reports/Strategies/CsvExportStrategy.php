<?php

namespace App\Services\Reports\Strategies;

use App\Services\Reports\Contracts\ExportStrategyInterface;
use Illuminate\Http\Response;

class CsvExportStrategy implements ExportStrategyInterface
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

        // Adicionar cabeçalhos fixos (igual ao PDF e Excel)
        $headers = [
            'Nome da Propriedade',
            'Município',
            'Estado',
            'Inscrição Estadual',
            'Área Total (ha)',
            'Produtor',
            'CPF/CNPJ',
            'E-mail',
            'Telefone',
            'Endereço',
            'Unidades',
            'Rebanhos'
        ];
        fputcsv($output, $headers, ';');

        // Adicionar dados formatados (igual ao PDF e Excel)
        foreach ($data as $item) {
            $unidades = $item['total_unidades'] ?? 0;
            $rebanhos = $item['total_rebanhos'] ?? 0;

            $row = [
                $item['nome_propriedade'] ?? '',
                $item['municipio'] ?? '',
                $item['uf'] ?? '',
                $item['inscricao_estadual'] ?? '',
                $item['area_total'] ?? '0,00',
                $item['produtor_nome'] ?? '',
                $item['produtor_cpf_cnpj'] ?? '',
                $item['produtor_email'] ?? '',
                $item['produtor_telefone'] ?? '',
                $item['produtor_endereco'] ?? '',
                $unidades,
                $rebanhos
            ];

            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
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
