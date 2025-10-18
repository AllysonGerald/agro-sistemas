<?php

namespace App\Services\Reports\Contracts;

interface ExportStrategyInterface
{
    /**
     * Exporta os dados no formato específico
     */
    public function export(array $data, string $filename, array $options = []): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response;

    /**
     * Retorna o tipo MIME do formato
     */
    public function getMimeType(): string;

    /**
     * Retorna a extensão do arquivo
     */
    public function getFileExtension(): string;

    /**
     * Retorna o nome do formato
     */
    public function getFormatName(): string;
}
