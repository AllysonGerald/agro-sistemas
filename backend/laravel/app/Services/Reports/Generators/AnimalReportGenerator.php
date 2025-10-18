<?php

namespace App\Services\Reports\Generators;

use App\Services\Reports\DataProviders\AnimalDataProvider;
use App\Services\Reports\Strategies\HerdPdfExportStrategy;
use App\Services\Reports\Strategies\HerdExcelExportStrategy;
use App\Services\Reports\Strategies\HerdCsvExportStrategy;
use Illuminate\Http\Request;

class AnimalReportGenerator extends BaseReportGenerator
{
    public function __construct(
        private AnimalDataProvider $dataProvider
    ) {
        parent::__construct();

        // Usar estratégias específicas para rebanhos
        $this->exportStrategies = [
            'excel' => new HerdExcelExportStrategy(),
            'csv' => new HerdCsvExportStrategy(),
            'pdf' => new HerdPdfExportStrategy(),
        ];
    }

    public function generateData(Request $request): array
    {
        return $this->dataProvider->getData($request);
    }

    public function getReportName(): string
    {
        return 'Relatório de Rebanhos';
    }

    public function getReportDescription(): string
    {
        return 'Relatório detalhado dos rebanhos cadastrados no sistema';
    }

    protected function getGroupByOptions(): array
    {
        return [
            'especie' => 'Por Espécie',
            'finalidade' => 'Por Finalidade',
            'propriedade' => 'Por Propriedade',
        ];
    }

    protected function formatData(array $data): array
    {
        return $this->dataProvider->formatData($data);
    }
}
