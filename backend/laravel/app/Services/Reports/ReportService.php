<?php

namespace App\Services\Reports;

use App\Services\Reports\Generators\PropertyReportGenerator;
use App\Services\Reports\Generators\AnimalReportGenerator;
use App\Services\Reports\Generators\ProductionUnitReportGenerator;
use App\Services\Reports\Generators\ProducerReportGenerator;
use App\Services\Reports\DataProviders\PropertyDataProvider;
use App\Services\Reports\DataProviders\AnimalDataProvider;
use App\Services\Reports\DataProviders\ProductionUnitDataProvider;
use App\Services\Reports\DataProviders\ProducerDataProvider;
use App\Services\PropriedadeService;
use App\Services\RebanhoService;
use App\Services\UnidadeProducaoService;
use App\Services\ProdutorRuralService;
use App\Enums\AnimalSpeciesEnum;
use App\Enums\CropTypeEnum;
use App\Models\Propriedade;
use App\Models\AtividadeSistema;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ReportService
{
    private array $generators = [];

    public function __construct(
        private PropriedadeService $propriedadeService,
        private RebanhoService $rebanhoService,
        private UnidadeProducaoService $unidadeService,
        private ProdutorRuralService $produtorService
    ) {
        $this->initializeGenerators();
    }

    private function initializeGenerators(): void
    {
        $propertyDataProvider = new PropertyDataProvider($this->propriedadeService);
        $animalDataProvider = new AnimalDataProvider($this->rebanhoService);
        $cropDataProvider = new ProductionUnitDataProvider($this->unidadeService);
        $producerDataProvider = new ProducerDataProvider($this->produtorService);

        $this->generators = [
            'propriedades' => new PropertyReportGenerator($propertyDataProvider),
            'rebanhos' => new AnimalReportGenerator($animalDataProvider),
            'unidades' => new ProductionUnitReportGenerator($cropDataProvider),
            'unidades-producao' => new ProductionUnitReportGenerator($cropDataProvider),
            'produtores' => new ProducerReportGenerator($producerDataProvider),
        ];
    }

    public function getDashboardData(): array
    {
        return [
            'propriedades_por_municipio' => $this->propriedadeService->getByMunicipio(),
            'animais_por_especie' => $this->rebanhoService->getQuantidadeByEspecie(),
            'hectares_por_cultura' => $this->unidadeService->getAreaByCultura(),
            'total_propriedades' => Propriedade::count(),
            'total_produtores' => \App\Models\ProdutorRural::count(),
            'total_animais' => \App\Models\Rebanho::sum('quantidade'),
            'total_hectares' => \App\Models\UnidadeProducao::sum('area_total_ha'),
        ];
    }

    public function getReportData(string $type, Request $request): array
    {
        if (!isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Tipo de relatório '{$type}' não encontrado");
        }

        return $this->generators[$type]->generateData($request);
    }

    public function exportReport(string $type, Request $request): mixed
    {
        if (!isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Tipo de relatório '{$type}' não encontrado");
        }

        $format = $request->get('formato', 'pdf');

        // Registrar atividade
        $this->registrarAtividadeRelatorio($type, $format);

        return $this->generators[$type]->export($request, $format);
    }

    public function getFilterOptions(): array
    {
        return [
            'especies_animais' => AnimalSpeciesEnum::options(),
            'tipos_cultura' => CropTypeEnum::options(),
            'municipios' => Propriedade::distinct()
                ->orderBy('municipio')
                ->pluck('municipio'),
            'ufs' => Propriedade::distinct()
                ->orderBy('uf')
                ->pluck('uf'),
        ];
    }

    public function getAvailableReports(): array
    {
        return [
            'propriedades' => [
                'name' => 'Propriedades Rurais',
                'description' => 'Relatório de propriedades rurais',
                'generator' => $this->generators['propriedades'],
            ],
            'rebanhos' => [
                'name' => 'Rebanhos',
                'description' => 'Relatório de rebanhos',
                'generator' => $this->generators['rebanhos'],
            ],
            'unidades' => [
                'name' => 'Unidades de Produção',
                'description' => 'Relatório de unidades de produção',
                'generator' => $this->generators['unidades'],
            ],
            'produtores' => [
                'name' => 'Produtores Rurais',
                'description' => 'Relatório de produtores rurais',
                'generator' => $this->generators['produtores'],
            ],
        ];
    }

    /**
     * Registrar atividade de relatório gerado
     */
    private function registrarAtividadeRelatorio($tipoRelatorio, $formato)
    {
        try {
            // Mapear tipos de relatório para nomes amigáveis
            $nomesRelatorios = [
                'produtores' => 'Produtores Rurais',
                'propriedades' => 'Propriedades Rurais',
                'rebanhos' => 'Rebanhos',
                'unidades' => 'Unidades de Produção',
                'animais-por-especie' => 'Animais por Espécie',
                'propriedades-por-municipio' => 'Propriedades por Município',
                'hectares-por-cultura' => 'Hectares por Nome',
                'rebanhos-por-produtor' => 'Rebanhos por Produtor'
            ];

            $nomeRelatorio = $nomesRelatorios[$tipoRelatorio] ?? $tipoRelatorio;
            AtividadeSistema::registrarRelatorio($nomeRelatorio, $formato);
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o processo
            Log::warning('Erro ao registrar atividade de relatório: ' . $e->getMessage());
        }
    }
}
