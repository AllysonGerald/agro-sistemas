<?php

namespace App\Services\Reports\Contracts;

use Illuminate\Http\Request;

interface ReportGeneratorInterface
{
    /**
     * Gera dados do relatório
     */
    public function generateData(Request $request): array;

    /**
     * Exporta o relatório no formato especificado
     */
    public function export(Request $request, string $format): mixed;

    /**
     * Retorna as opções de filtros disponíveis
     */
    public function getFilterOptions(): array;

    /**
     * Retorna o nome do relatório
     */
    public function getReportName(): string;

    /**
     * Retorna a descrição do relatório
     */
    public function getReportDescription(): string;
}
