<?php

namespace App\Services\Reports\Generators;

use App\Services\Reports\Contracts\ReportGeneratorInterface;
use App\Services\Reports\DataProviders\PropertyDataProvider;
use App\Services\Reports\Strategies\PropertyPdfExportStrategy;
use App\Services\Reports\Strategies\PropertyExcelExportStrategy;
use App\Services\Reports\Strategies\PropertyCsvExportStrategy;

class PropertyReportGenerator extends BaseReportGenerator
{
    public function __construct(
        private PropertyDataProvider $dataProvider
    ) {
        parent::__construct();

        // Usar estratégias específicas para propriedades
        $this->exportStrategies = [
            'excel' => new PropertyExcelExportStrategy(),
            'csv' => new PropertyCsvExportStrategy(),
            'pdf' => new PropertyPdfExportStrategy(),
        ];
    }

    public function generate(string $format, array $filters = []): array
    {
        $data = $this->dataProvider->getData($filters);
        $formattedData = $this->dataProvider->formatData($data);

        return [
            'data' => $formattedData,
            'total' => count($formattedData),
            'filters' => $filters,
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];
    }

    public function export(\Illuminate\Http\Request $request, string $format): mixed
    {
        $filters = $request->all();
        $data = $this->dataProvider->getData($filters);
        $formattedData = $this->dataProvider->formatData($data);

        $filename = 'relatorio_propriedades_' . now()->format('Y_m_d_H_i_s');
        $options = [
            'title' => 'Relatório de Propriedades Rurais',
            'subtitle' => 'Relatório detalhado das propriedades rurais cadastradas no sistema'
        ];

        return $this->exportStrategies[$format]->export($formattedData, $filename, $options);
    }

    public function generateData(\Illuminate\Http\Request $request): array
    {
        $filters = $request->all();
        $data = $this->dataProvider->getData($filters);
        return $this->dataProvider->formatData($data);
    }

    protected function getGroupByOptions(): array
    {
        return [
            'municipio' => 'Município',
            'uf' => 'Estado',
            'produtor' => 'Produtor'
        ];
    }

    protected function formatData(array $data): array
    {
        return $this->dataProvider->formatData($data);
    }

    public function getReportName(): string
    {
        return 'Relatório de Propriedades Rurais';
    }

    public function getReportDescription(): string
    {
        return 'Relatório detalhado das propriedades rurais cadastradas no sistema';
    }
}
