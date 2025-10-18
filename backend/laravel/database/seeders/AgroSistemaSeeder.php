<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;
use App\Enums\AnimalSpeciesEnum;
use App\Enums\CropTypeEnum;
use App\Enums\LivestockPurposeEnum;
use Carbon\Carbon;

class AgroSistemaSeeder extends Seeder
{
    public function run(): void
    {
        // Criar Produtores Rurais
        $produtores = [
            [
                'nome' => 'João Silva dos Santos',
                'cpf_cnpj' => '12345678901',
                'telefone' => '11987654321',
                'email' => 'joao.silva@email.com',
                'endereco' => 'Rua das Flores, 123, Centro, São Paulo, SP',
                'data_cadastro' => Carbon::now()
            ],
            [
                'nome' => 'Fazenda Agropecuária Ltda',
                'cpf_cnpj' => '12345678000195',
                'telefone' => '11912345678',
                'email' => 'contato@fazendaagro.com.br',
                'endereco' => 'Rodovia SP-001, Km 45, Zona Rural, Ribeirão Preto, SP',
                'data_cadastro' => Carbon::now()
            ],
            [
                'nome' => 'Maria Oliveira Costa',
                'cpf_cnpj' => '98765432100',
                'telefone' => '11999887766',
                'email' => 'maria.costa@gmail.com',
                'endereco' => 'Estrada Municipal, 456, Sítio Esperança, Campinas, SP',
                'data_cadastro' => Carbon::now()
            ]
        ];

        foreach ($produtores as $produtorData) {
            $produtor = ProdutorRural::create($produtorData);

            // Criar propriedades para cada produtor
            $this->criarPropriedades($produtor);
        }
    }

    private function criarPropriedades(ProdutorRural $produtor): void
    {
        $propriedadesData = [
            [
                'nome' => 'Fazenda Santa Clara',
                'municipio' => 'Ribeirão Preto',
                'uf' => 'SP',
                'inscricao_estadual' => '123456789',
                'area_total' => 500.50
            ],
            [
                'nome' => 'Sítio Boa Vista',
                'municipio' => 'Campinas',
                'uf' => 'SP',
                'inscricao_estadual' => '987654321',
                'area_total' => 250.75
            ]
        ];

        foreach ($propriedadesData as $propData) {
            $propData['produtor_id'] = $produtor->id;
            $propriedade = Propriedade::create($propData);

            // Criar unidades de produção
            $this->criarUnidadesProducao($propriedade);

            // Criar rebanhos
            $this->criarRebanhos($propriedade);
        }
    }

    private function criarUnidadesProducao(Propriedade $propriedade): void
    {
        $culturas = CropTypeEnum::cases();

        for ($i = 0; $i < 2; $i++) {
            $cultura = $culturas[array_rand($culturas)];

            UnidadeProducao::create([
                'nome_cultura' => $cultura->value,
                'area_total_ha' => rand(10, 100),
                'coordenadas_geograficas' => null,
                'propriedade_id' => $propriedade->id
            ]);
        }
    }

    private function criarRebanhos(Propriedade $propriedade): void
    {
        $especies = AnimalSpeciesEnum::cases();
        $finalidades = LivestockPurposeEnum::cases();

        for ($i = 0; $i < 2; $i++) {
            $especie = $especies[array_rand($especies)];
            $finalidade = $finalidades[array_rand($finalidades)];

            Rebanho::create([
                'especie' => $especie->value,
                'quantidade' => rand(50, 500),
                'finalidade' => $finalidade->value,
                'data_atualizacao' => Carbon::now()->subDays(rand(1, 30)),
                'propriedade_id' => $propriedade->id
            ]);
        }
    }
}
