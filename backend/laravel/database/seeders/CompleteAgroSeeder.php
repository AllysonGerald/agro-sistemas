<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CompleteAgroSeeder extends Seeder
{
    /**
     * Seed completo do sistema com dados realistas
     */
    public function run(): void
    {
        // Limpar tabelas (em ordem reversa devido às foreign keys)
        // Para PostgreSQL, desabilitar triggers temporariamente
        DB::statement('SET session_replication_role = replica;');

        DB::table('reproducoes')->truncate();
        DB::table('manejos')->truncate();
        DB::table('transacoes_financeiras')->truncate();
        DB::table('estoque')->truncate();
        DB::table('animais')->truncate();
        DB::table('lotes')->truncate();
        DB::table('pastos')->truncate();
        DB::table('categorias_financeiras')->truncate();
        DB::table('rebanhos')->truncate();
        DB::table('unidades_producao')->truncate();
        DB::table('propriedades')->truncate();
        DB::table('produtores_rurais')->truncate();
        DB::table('users')->truncate();

        DB::statement('SET session_replication_role = DEFAULT;');

        // 1. USUÁRIOS
        $usuarios = [
            [
                'id' => 1,
                'name' => 'Allyson Gerald de Sousa Carvalho',
                'email' => 'allyson_gerald@outlook.com',
                'password' => Hash::make('Teste@2025'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'João Silva',
                'email' => 'joao.silva@agro.com',
                'password' => Hash::make('Senha@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('users')->insert($usuarios);

        // 2. PRODUTORES RURAIS
        $produtores = [
            [
                'id' => 1,
                'nome' => 'João da Silva Santos',
                'cpf_cnpj' => '123.456.789-00',
                'telefone' => '(86) 99999-1234',
                'email' => 'joao.santos@gmail.com',
                'endereco' => 'Rua das Flores, 100, Centro, Teresina - PI, CEP: 64000-000',
                'data_cadastro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nome' => 'Maria Oliveira Costa',
                'cpf_cnpj' => '987.654.321-00',
                'telefone' => '(86) 98888-5678',
                'email' => 'maria.costa@hotmail.com',
                'endereco' => 'Avenida Principal, 500, Fátima, Teresina - PI, CEP: 64001-000',
                'data_cadastro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nome' => 'José Carlos Almeida',
                'cpf_cnpj' => '456.789.123-00',
                'telefone' => '(86) 97777-9012',
                'email' => 'jose.almeida@yahoo.com',
                'endereco' => 'Rodovia PI-112, Km 15, Zona Rural, José de Freitas - PI, CEP: 64002-000',
                'data_cadastro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('produtores_rurais')->insert($produtores);

        // 3. PROPRIEDADES
        $propriedades = [
            [
                'id' => 1,
                'nome' => 'Fazenda Boa Vista',
                'produtor_id' => 1,
                'municipio' => 'Teresina',
                'uf' => 'PI',
                'inscricao_estadual' => '123456789',
                'area_total' => 250.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nome' => 'Sítio Santa Maria',
                'produtor_id' => 2,
                'municipio' => 'José de Freitas',
                'uf' => 'PI',
                'inscricao_estadual' => '987654321',
                'area_total' => 180.75,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nome' => 'Fazenda São José',
                'produtor_id' => 3,
                'municipio' => 'União',
                'uf' => 'PI',
                'inscricao_estadual' => '456789123',
                'area_total' => 450.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('propriedades')->insert($propriedades);

        // 4. UNIDADES DE PRODUÇÃO
        $unidades = [
            [
                'id' => 1,
                'nome_cultura' => 'Milho Híbrido',
                'area_total_ha' => 50.00,
                'coordenadas_geograficas' => json_encode(['lat' => -5.0892, 'lng' => -42.8019]),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nome_cultura' => 'Soja',
                'area_total_ha' => 75.50,
                'coordenadas_geograficas' => json_encode(['lat' => -5.0920, 'lng' => -42.8100]),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nome_cultura' => 'Capim Brachiaria',
                'area_total_ha' => 120.00,
                'coordenadas_geograficas' => json_encode(['lat' => -5.1000, 'lng' => -42.8200]),
                'propriedade_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('unidades_producao')->insert($unidades);

        // 5. REBANHOS
        $rebanhos = [
            [
                'id' => 1,
                'especie' => 'Bovino',
                'quantidade' => 150,
                'finalidade' => 'corte',
                'data_atualizacao' => now()->subDays(5),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'especie' => 'Bovino',
                'quantidade' => 80,
                'finalidade' => 'leite',
                'data_atualizacao' => now()->subDays(3),
                'propriedade_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'especie' => 'Caprino',
                'quantidade' => 45,
                'finalidade' => 'corte',
                'data_atualizacao' => now()->subDays(7),
                'propriedade_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('rebanhos')->insert($rebanhos);

        // 6. PASTOS
        $pastos = [
            [
                'id' => 1,
                'nome' => 'Pasto 1 - Brachiaria',
                'area_hectares' => 25.00,
                'tipo_capim' => 'Brachiaria Brizantha',
                'capacidade_suporte' => 3.5,
                'status' => 'disponivel',
                'data_ultimo_manejo' => now()->subDays(30),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nome' => 'Pasto 2 - Mombaça',
                'area_hectares' => 30.00,
                'tipo_capim' => 'Mombaça',
                'capacidade_suporte' => 4.0,
                'status' => 'em_uso',
                'data_ultimo_manejo' => now()->subDays(20),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nome' => 'Pasto 3 - Tanzânia',
                'area_hectares' => 20.00,
                'tipo_capim' => 'Tanzânia',
                'capacidade_suporte' => 3.0,
                'status' => 'em_recuperacao',
                'data_ultimo_manejo' => now()->subDays(10),
                'propriedade_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('pastos')->insert($pastos);

        // 7. CATEGORIAS FINANCEIRAS
        $categorias = [
            // Receitas
            ['id' => 1, 'nome' => 'Venda de Gado', 'tipo' => 'receita', 'descricao' => 'Vendas de animais', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nome' => 'Venda de Leite', 'tipo' => 'receita', 'descricao' => 'Vendas de produção leiteira', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nome' => 'Venda de Grãos', 'tipo' => 'receita', 'descricao' => 'Vendas de milho, soja, etc', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nome' => 'Outras Receitas', 'tipo' => 'receita', 'descricao' => 'Outras fontes de receita', 'created_at' => now(), 'updated_at' => now()],
            // Despesas
            ['id' => 5, 'nome' => 'Ração e Alimentação', 'tipo' => 'despesa', 'descricao' => 'Compra de ração e suplementos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nome' => 'Medicamentos', 'tipo' => 'despesa', 'descricao' => 'Vacinas e remédios', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nome' => 'Mão de Obra', 'tipo' => 'despesa', 'descricao' => 'Salários e encargos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'nome' => 'Manutenção', 'tipo' => 'despesa', 'descricao' => 'Reparos em geral', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'nome' => 'Combustível', 'tipo' => 'despesa', 'descricao' => 'Diesel e gasolina', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('categorias_financeiras')->insert($categorias);

        // 8. LOTES
        $lotes = [
            [
                'id' => 1,
                'nome' => 'Lote 001 - Nelore',
                'rebanho_id' => 1,
                'pasto_id' => 1,
                'quantidade_animais' => 45,
                'data_entrada' => now()->subMonths(3),
                'peso_medio' => 380.5,
                'objetivo' => 'Engorda para abate',
                'observacoes' => 'Animais em fase final de engorda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nome' => 'Lote 002 - Nelore',
                'rebanho_id' => 1,
                'pasto_id' => 2,
                'quantidade_animais' => 50,
                'data_entrada' => now()->subMonths(2),
                'peso_medio' => 320.0,
                'objetivo' => 'Recria',
                'observacoes' => 'Animais em recria, bom desenvolvimento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nome' => 'Lote 003 - Girolando',
                'rebanho_id' => 2,
                'pasto_id' => 3,
                'quantidade_animais' => 30,
                'data_entrada' => now()->subMonths(4),
                'peso_medio' => 450.0,
                'objetivo' => 'Produção de leite',
                'observacoes' => 'Vacas em lactação',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('lotes')->insert($lotes);

        // 9. ANIMAIS
        $animais = [];
        $animalId = 1;

        // Animais do Lote 1 (Nelore - Engorda)
        for ($i = 1; $i <= 45; $i++) {
            $animais[] = [
                'id' => $animalId++,
                'identificacao' => 'NEL' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'brinco' => '001' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'rebanho_id' => 1,
                'lote_id' => 1,
                'raca' => 'Nelore',
                'sexo' => $i % 3 == 0 ? 'femea' : 'macho',
                'data_nascimento' => now()->subYears(2)->subMonths(rand(0, 6)),
                'peso_atual' => 380.5 + rand(-50, 50),
                'status' => rand(1, 10) > 8 ? 'doente' : 'saudavel',
                'mae_id' => null,
                'pai_id' => null,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Animais do Lote 2 (Nelore - Recria)
        for ($i = 1; $i <= 50; $i++) {
            $animais[] = [
                'id' => $animalId++,
                'identificacao' => 'REC' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'brinco' => '002' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'rebanho_id' => 1,
                'lote_id' => 2,
                'raca' => 'Nelore',
                'sexo' => $i % 2 == 0 ? 'femea' : 'macho',
                'data_nascimento' => now()->subYears(1)->subMonths(rand(0, 6)),
                'peso_atual' => 320.0 + rand(-40, 40),
                'status' => 'saudavel',
                'mae_id' => null,
                'pai_id' => null,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Animais do Lote 3 (Girolando - Leite)
        for ($i = 1; $i <= 30; $i++) {
            $animais[] = [
                'id' => $animalId++,
                'identificacao' => 'GIR' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'brinco' => '003' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'rebanho_id' => 2,
                'lote_id' => 3,
                'raca' => 'Girolando',
                'sexo' => 'femea',
                'data_nascimento' => now()->subYears(3)->subMonths(rand(0, 12)),
                'peso_atual' => 450.0 + rand(-30, 30),
                'status' => 'saudavel',
                'mae_id' => null,
                'pai_id' => null,
                'observacoes' => 'Em lactação',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('animais')->insert($animais);

        // 10. TRANSAÇÕES FINANCEIRAS
        $transacoes = [
            // Receitas
            [
                'tipo' => 'receita',
                'categoria_id' => 1,
                'descricao' => 'Venda de 10 cabeças de gado Nelore',
                'valor' => 45000.00,
                'data' => now()->subDays(15),
                'forma_pagamento' => 'Transferência bancária',
                'propriedade_id' => 1,
                'animal_id' => null,
                'lote_id' => 1,
                'observacoes' => 'Venda para frigorífico local',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'receita',
                'categoria_id' => 2,
                'descricao' => 'Venda de leite - Março 2025',
                'valor' => 18500.00,
                'data' => now()->subDays(10),
                'forma_pagamento' => 'Depósito',
                'propriedade_id' => 2,
                'animal_id' => null,
                'lote_id' => 3,
                'observacoes' => 'Produção de 10.000 litros',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'receita',
                'categoria_id' => 3,
                'descricao' => 'Venda de milho - Safra 2024/2025',
                'valor' => 65000.00,
                'data' => now()->subDays(20),
                'forma_pagamento' => 'Cheque',
                'propriedade_id' => 1,
                'animal_id' => null,
                'lote_id' => null,
                'observacoes' => '500 sacas de 60kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Despesas
            [
                'tipo' => 'despesa',
                'categoria_id' => 5,
                'descricao' => 'Compra de ração concentrada',
                'valor' => 12500.00,
                'data' => now()->subDays(5),
                'forma_pagamento' => 'Boleto',
                'propriedade_id' => 1,
                'animal_id' => null,
                'lote_id' => null,
                'observacoes' => '50 sacas de 25kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'despesa',
                'categoria_id' => 6,
                'descricao' => 'Vacinas e vermífugos',
                'valor' => 3800.00,
                'data' => now()->subDays(8),
                'forma_pagamento' => 'PIX',
                'propriedade_id' => 1,
                'animal_id' => null,
                'lote_id' => null,
                'observacoes' => 'Vacinação completa do rebanho',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'despesa',
                'categoria_id' => 7,
                'descricao' => 'Salários - Março 2025',
                'valor' => 8500.00,
                'data' => now()->subDays(2),
                'forma_pagamento' => 'Transferência',
                'propriedade_id' => 1,
                'animal_id' => null,
                'lote_id' => null,
                'observacoes' => '3 funcionários',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'despesa',
                'categoria_id' => 9,
                'descricao' => 'Combustível - Março 2025',
                'valor' => 2200.00,
                'data' => now()->subDays(3),
                'forma_pagamento' => 'Dinheiro',
                'propriedade_id' => 1,
                'animal_id' => null,
                'lote_id' => null,
                'observacoes' => 'Diesel para tratores',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('transacoes_financeiras')->insert($transacoes);

        // 11. ESTOQUE
        $estoque = [
            [
                'nome' => 'Ração Concentrada 25kg',
                'tipo' => 'racao',
                'quantidade' => 120,
                'unidade' => 'sacos',
                'valor_unitario' => 85.00,
                'data_entrada' => now()->subDays(5),
                'data_validade' => now()->addMonths(6),
                'fornecedor' => 'Agropecuária Santos',
                'propriedade_id' => 1,
                'observacoes' => 'Estoque para 2 meses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Sal Mineral 20kg',
                'tipo' => 'suplemento',
                'quantidade' => 50,
                'unidade' => 'sacos',
                'valor_unitario' => 65.00,
                'data_entrada' => now()->subDays(10),
                'data_validade' => now()->addMonths(12),
                'fornecedor' => 'Nutrição Animal Ltda',
                'propriedade_id' => 1,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Vacina contra Febre Aftosa',
                'tipo' => 'medicamento',
                'quantidade' => 200,
                'unidade' => 'doses',
                'valor_unitario' => 12.50,
                'data_entrada' => now()->subDays(8),
                'data_validade' => now()->addMonths(3),
                'fornecedor' => 'VetMed Produtos',
                'propriedade_id' => 1,
                'observacoes' => 'Manter refrigerado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Vermífugo Ivermectina 500ml',
                'tipo' => 'medicamento',
                'quantidade' => 15,
                'unidade' => 'frascos',
                'valor_unitario' => 45.00,
                'data_entrada' => now()->subDays(8),
                'data_validade' => now()->addYears(2),
                'fornecedor' => 'VetMed Produtos',
                'propriedade_id' => 1,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Silagem de Milho',
                'tipo' => 'racao',
                'quantidade' => 5000,
                'unidade' => 'kg',
                'valor_unitario' => 0.45,
                'data_entrada' => now()->subDays(30),
                'data_validade' => now()->addMonths(4),
                'fornecedor' => 'Produção própria',
                'propriedade_id' => 2,
                'observacoes' => 'Safra 2024',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('estoque')->insert($estoque);

        // 12. MANEJOS
        $manejos = [
            [
                'tipo' => 'vacinacao',
                'descricao' => 'Vacinação contra Febre Aftosa',
                'data' => now()->subDays(8),
                'animal_id' => 1,
                'lote_id' => 1,
                'responsavel' => 'Dr. Pedro Veterinário',
                'custo' => 1500.00,
                'observacoes' => 'Vacinação de todo o lote 001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'pesagem',
                'descricao' => 'Pesagem mensal - Lote 001',
                'data' => now()->subDays(5),
                'animal_id' => null,
                'lote_id' => 1,
                'responsavel' => 'José - Funcionário',
                'custo' => 0.00,
                'observacoes' => 'Ganho médio de 0.8kg/dia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'vermifugacao',
                'descricao' => 'Aplicação de vermífugo',
                'data' => now()->subDays(12),
                'animal_id' => null,
                'lote_id' => 2,
                'responsavel' => 'Dr. Pedro Veterinário',
                'custo' => 800.00,
                'observacoes' => 'Prevenção de parasitas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'tratamento',
                'descricao' => 'Tratamento de mastite',
                'data' => now()->subDays(3),
                'animal_id' => 96,
                'lote_id' => 3,
                'responsavel' => 'Dr. Pedro Veterinário',
                'custo' => 250.00,
                'observacoes' => 'Animal GIR0001 com mastite leve',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'castracao',
                'descricao' => 'Castração de machos',
                'data' => now()->subDays(20),
                'animal_id' => null,
                'lote_id' => 2,
                'responsavel' => 'Dr. Pedro Veterinário',
                'custo' => 1200.00,
                'observacoes' => 'Castração de 20 bezerros',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('manejos')->insert($manejos);

        // 13. REPRODUÇÕES
        $reproducoes = [
            [
                'animal_id' => 96,
                'tipo_reproducao' => 'inseminacao_artificial',
                'data_cobertura' => now()->subDays(180),
                'data_prevista_parto' => now()->addDays(105),
                'touro_id' => null,
                'status' => 'gestante',
                'observacoes' => 'Primeira gestação',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'animal_id' => 97,
                'tipo_reproducao' => 'monta_natural',
                'data_cobertura' => now()->subDays(200),
                'data_prevista_parto' => now()->addDays(85),
                'touro_id' => null,
                'status' => 'gestante',
                'observacoes' => 'Segunda gestação, sem complicações',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'animal_id' => 98,
                'tipo_reproducao' => 'inseminacao_artificial',
                'data_cobertura' => now()->subDays(285),
                'data_prevista_parto' => now()->subDays(5),
                'touro_id' => null,
                'status' => 'parida',
                'observacoes' => 'Parto normal, bezerro saudável',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'animal_id' => 99,
                'tipo_reproducao' => 'inseminacao_artificial',
                'data_cobertura' => now()->subDays(30),
                'data_prevista_parto' => now()->addDays(255),
                'touro_id' => null,
                'status' => 'vazia',
                'observacoes' => 'Não confirmou prenhez',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('reproducoes')->insert($reproducoes);

        $this->command->info('✅ Seed completo executado com sucesso!');
        $this->command->info('📊 Dados inseridos:');
        $this->command->info('   • ' . count($usuarios) . ' usuários');
        $this->command->info('   • ' . count($produtores) . ' produtores rurais');
        $this->command->info('   • ' . count($propriedades) . ' propriedades');
        $this->command->info('   • ' . count($unidades) . ' unidades de produção');
        $this->command->info('   • ' . count($rebanhos) . ' rebanhos');
        $this->command->info('   • ' . count($pastos) . ' pastos');
        $this->command->info('   • ' . count($categorias) . ' categorias financeiras');
        $this->command->info('   • ' . count($lotes) . ' lotes');
        $this->command->info('   • ' . count($animais) . ' animais');
        $this->command->info('   • ' . count($transacoes) . ' transações financeiras');
        $this->command->info('   • ' . count($estoque) . ' itens em estoque');
        $this->command->info('   • ' . count($manejos) . ' manejos');
        $this->command->info('   • ' . count($reproducoes) . ' registros de reprodução');
    }
}

