<?php

namespace App\Services\Reports\Generators;

use App\Services\Reports\DataProviders\ProductionUnitDataProvider;
use App\Services\Reports\Strategies\ProductionUnitPdfExportStrategy;
use App\Services\Reports\Strategies\ProductionUnitExcelExportStrategy;
use App\Services\Reports\Strategies\ProductionUnitCsvExportStrategy;
use Illuminate\Http\Request;

class ProductionUnitReportGenerator extends BaseReportGenerator
{
    public function __construct(
        private ProductionUnitDataProvider $dataProvider
    ) {
        parent::__construct();

        // Usar estratégias específicas para unidades de produção
        $this->exportStrategies = [
            'excel' => new ProductionUnitExcelExportStrategy(),
            'csv' => new ProductionUnitCsvExportStrategy(),
            'pdf' => new ProductionUnitPdfExportStrategy(),
        ];
    }

    public function generateData(Request $request): array
    {
        return $this->dataProvider->getData($request);
    }

    public function getReportName(): string
    {
        return 'Relatório de Unidades de Produção';
    }

    public function getReportDescription(): string
    {
        return 'Relatório detalhado das unidades de produção cadastradas no sistema';
    }

    protected function getGroupByOptions(): array
    {
        return [
            'cultura' => 'Por Cultura',
            'propriedade' => 'Por Propriedade',
            'municipio' => 'Por Município',
        ];
    }

    protected function formatData(array $data): array
    {
        return $this->dataProvider->formatData($data);
    }
}
