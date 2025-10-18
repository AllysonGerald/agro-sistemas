<?php

namespace App\Services\Reports\Generators;

use App\Services\Reports\DataProviders\ProducerDataProvider;
use App\Services\Reports\Strategies\ProducerPdfExportStrategy;
use App\Services\Reports\Strategies\ProducerExcelExportStrategy;
use App\Services\Reports\Strategies\ProducerCsvExportStrategy;
use Illuminate\Http\Request;

class ProducerReportGenerator extends BaseReportGenerator
{
    public function __construct(
        private ProducerDataProvider $dataProvider
    ) {
        parent::__construct();

        // Usar estratégias específicas para produtores
        $this->exportStrategies = [
            'excel' => new ProducerExcelExportStrategy(),
            'csv' => new ProducerCsvExportStrategy(),
            'pdf' => new ProducerPdfExportStrategy(),
        ];
    }

    public function generateData(Request $request): array
    {
        return $this->dataProvider->getData($request);
    }

    public function getReportName(): string
    {
        return 'Relatório de Produtores Rurais';
    }

    public function getReportDescription(): string
    {
        return 'Relatório detalhado dos produtores rurais cadastrados no sistema';
    }

    protected function getGroupByOptions(): array
    {
        return [
            'municipio' => 'Por Município',
            'uf' => 'Por Estado',
            'tipo_documento' => 'Por Tipo de Documento',
        ];
    }

    protected function formatData(array $data): array
    {
        return $this->dataProvider->formatData($data);
    }
}
