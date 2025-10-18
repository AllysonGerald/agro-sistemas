<?php

namespace App\Services\Reports\Generators;

use App\Services\Reports\Contracts\ReportGeneratorInterface;
use App\Services\Reports\Contracts\ExportStrategyInterface;
use App\Services\Reports\Strategies\ExcelExportStrategy;
use App\Services\Reports\Strategies\CsvExportStrategy;
use App\Services\Reports\Strategies\PdfExportStrategy;
use App\Services\Reports\Strategies\ModernPdfExportStrategy;
use Illuminate\Http\Request;

abstract class BaseReportGenerator implements ReportGeneratorInterface
{
    protected array $exportStrategies = [];

    public function __construct()
    {
        $this->exportStrategies = [
            'excel' => new ExcelExportStrategy(),
            'csv' => new CsvExportStrategy(),
            'pdf' => new ModernPdfExportStrategy(), // Usar a versão moderna
            'pdf_old' => new PdfExportStrategy(), // Manter versão antiga como backup
        ];
    }

    public function export(Request $request, string $format): mixed
    {
        $data = $this->generateData($request);
        $formattedData = $this->formatData($data);

        $strategy = $this->getExportStrategy($format);
        $filename = $this->generateFilename($request);

        $options = [
            'title' => $this->getReportName(),
            'subtitle' => $this->getReportDescription(),
        ];

        return $strategy->export($formattedData, $filename, $options);
    }

    public function getFilterOptions(): array
    {
        return [
            'formats' => $this->getAvailableFormats(),
            'date_range' => true,
            'group_by' => $this->getGroupByOptions(),
        ];
    }

    public function getAvailableStrategies(): array
    {
        return array_keys($this->exportStrategies);
    }

    protected function getExportStrategy(string $format): ExportStrategyInterface
    {
        if (!isset($this->exportStrategies[$format])) {
            throw new \InvalidArgumentException("Formato de exportação '{$format}' não suportado");
        }

        return $this->exportStrategies[$format];
    }

    protected function generateFilename(Request $request): string
    {
        $baseName = strtolower(str_replace(' ', '_', $this->getReportName()));
        $timestamp = now()->format('Y_m_d_H_i_s');

        return "{$baseName}_{$timestamp}";
    }

    protected function getAvailableFormats(): array
    {
        return [
            'excel' => 'Excel (.xlsx)',
            'csv' => 'CSV (.csv)',
            'pdf' => 'PDF (.pdf)',
        ];
    }

    abstract protected function getGroupByOptions(): array;
    abstract protected function formatData(array $data): array;
}
