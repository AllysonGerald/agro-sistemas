<?php

namespace App\Services\Reports\Contracts;

use Illuminate\Http\Request;

interface ReportDataProviderInterface
{
    /**
     * Busca os dados para o relatório
     */
    public function getData(Request $request): array;

    /**
     * Aplica filtros aos dados
     */
    public function applyFilters(array $data, array $filters): array;

    /**
     * Formata os dados para exibição
     */
    public function formatData(array $data): array;

    /**
     * Retorna metadados sobre os dados
     */
    public function getMetadata(array $data): array;
}
