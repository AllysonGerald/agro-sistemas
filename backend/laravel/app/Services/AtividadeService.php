<?php

namespace App\Services;

use App\Models\AtividadeSistema;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;

/**
 * Serviço de Atividades do Sistema
 *
 * Gerencia o registro e recuperação de atividades do sistema
 * para exibição no dashboard.
 *
 * @package App\Services
 * @author Sistema Agropecuário
 * @version 1.0.0
 */
class AtividadeService
{
    /**
     * Obter atividades recentes
     */
    public function obterRecentes($limite = 10): array
    {
        // Buscar atividades da tabela de atividades do sistema
        $atividades = AtividadeSistema::obterRecentes($limite);

        // Se não houver atividades suficientes, popular com dados dos modelos
        if ($atividades->count() < 5) {
            $this->popularAtividadesIniciais();
            $atividades = AtividadeSistema::obterRecentes($limite);
        }

        return $atividades->toArray();
    }

    /**
     * Popular atividades iniciais baseadas nos dados existentes
     */
    private function popularAtividadesIniciais(): void
    {
        // Verificar se já existem atividades para evitar duplicação
        if (AtividadeSistema::count() > 0) {
            return;
        }

        // Produtores recentes
        $produtoresRecentes = ProdutorRural::latest()
            ->take(5)
            ->get();

        foreach ($produtoresRecentes as $produtor) {
            AtividadeSistema::registrarProdutor($produtor, 'cadastrado');
        }

        // Propriedades recentes
        $propriedadesRecentes = Propriedade::with('produtor')
            ->latest()
            ->take(5)
            ->get();

        foreach ($propriedadesRecentes as $propriedade) {
            AtividadeSistema::registrarPropriedade($propriedade, 'cadastrada');
        }

        // Rebanhos recentes
        $rebanhosRecentes = Rebanho::with('propriedade')
            ->latest()
            ->take(5)
            ->get();

        foreach ($rebanhosRecentes as $rebanho) {
            AtividadeSistema::registrarRebanho($rebanho, 'cadastrado');
        }

        // Unidades de produção recentes
        $unidadesRecentes = UnidadeProducao::with('propriedade')
            ->latest()
            ->take(5)
            ->get();

        foreach ($unidadesRecentes as $unidade) {
            AtividadeSistema::registrarUnidadeProducao($unidade, 'cadastrada');
        }

        // Adicionar algumas atividades de relatórios
        $totalPropriedades = Propriedade::count();
        $totalRebanhos = Rebanho::count();
        $totalUnidades = UnidadeProducao::count();

        if ($totalPropriedades > 0) {
            AtividadeSistema::registrarRelatorio('propriedades por município', 'pdf', $totalPropriedades);
        }

        if ($totalRebanhos > 0) {
            AtividadeSistema::registrarRelatorio('animais por espécie', 'excel', $totalRebanhos);
        }

        if ($totalUnidades > 0) {
            AtividadeSistema::registrarRelatorio('hectares por cultura', 'pdf', $totalUnidades);
        }

        // Adicionar algumas atividades de relatórios gerais
        AtividadeSistema::registrarRelatorio('produtores rurais', 'excel', $produtoresRecentes->count());
        AtividadeSistema::registrarRelatorio('propriedades rurais', 'pdf', $propriedadesRecentes->count());
        AtividadeSistema::registrarRelatorio('unidades de produção', 'excel', $unidadesRecentes->count());
        AtividadeSistema::registrarRelatorio('rebanhos', 'pdf', $rebanhosRecentes->count());
    }

    /**
     * Registrar atividade de produtor
     */
    public function registrarProdutor($produtor, $acao = 'cadastrado'): void
    {
        AtividadeSistema::registrarProdutor($produtor, $acao);
    }

    /**
     * Registrar atividade de propriedade
     */
    public function registrarPropriedade($propriedade, $acao = 'cadastrada'): void
    {
        AtividadeSistema::registrarPropriedade($propriedade, $acao);
    }

    /**
     * Registrar atividade de rebanho
     */
    public function registrarRebanho($rebanho, $acao = 'cadastrado'): void
    {
        AtividadeSistema::registrarRebanho($rebanho, $acao);
    }

    /**
     * Registrar atividade de unidade de produção
     */
    public function registrarUnidadeProducao($unidade, $acao = 'cadastrada'): void
    {
        AtividadeSistema::registrarUnidadeProducao($unidade, $acao);
    }

    /**
     * Registrar atividade de relatório
     */
    public function registrarRelatorio($tipoRelatorio, $formato, $totalRegistros = null): void
    {
        AtividadeSistema::registrarRelatorio($tipoRelatorio, $formato, $totalRegistros);
    }
}
