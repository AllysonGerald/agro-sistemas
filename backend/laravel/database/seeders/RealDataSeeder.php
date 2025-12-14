<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seed com dados realistas...');

        // 1. USUÁRIOS
        $this->command->info('👤 Criando usuários...');
        DB::table('users')->insert([
            [
                'name' => 'Allyson Gerald de Sousa Carvalho',
                'email' => 'allyson_gerald@outlook.com',
                'password' => Hash::make('Teste@2025'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@agro.com',
                'password' => Hash::make('Senha@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. PRODUTORES RURAIS
        $this->command->info('👨‍🌾 Criando produtores rurais...');
        DB::table('produtores_rurais')->insert([
            [
                'nome' => 'João da Silva Santos',
                'cpf_cnpj' => '123.456.789-00',
                'telefone' => '(86) 99999-1234',
                'email' => 'joao.santos@gmail.com',
                'cep' => '64000-000',
                'logradouro' => 'Rua das Flores',
                'numero' => '100',
                'complemento' => null,
                'bairro' => 'Centro',
                'cidade' => 'Teresina',
                'estado' => 'PI',
                'inscricao_estadual' => '123456789',
                'car' => 'PI-2501234-ABCD1234567890EFGH',
                'tipo_pessoa' => 'fisica',
                'observacoes' => 'Produtor de gado de corte e leite. Possui certificação orgânica.',
                'data_cadastro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Maria Oliveira Costa',
                'cpf_cnpj' => '987.654.321-00',
                'telefone' => '(86) 98888-5678',
                'email' => 'maria.costa@hotmail.com',
                'cep' => '64001-000',
                'logradouro' => 'Avenida Principal',
                'numero' => '500',
                'complemento' => 'Sala 12',
                'bairro' => 'Fátima',
                'cidade' => 'Teresina',
                'estado' => 'PI',
                'inscricao_estadual' => '987654321',
                'car' => 'PI-2501234-WXYZ9876543210IJKL',
                'tipo_pessoa' => 'fisica',
                'observacoes' => 'Produção de hortaliças e criação de aves caipiras.',
                'data_cadastro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'José Carlos Almeida',
                'cpf_cnpj' => '456.789.123-00',
                'telefone' => '(86) 97777-9012',
                'email' => 'jose.almeida@yahoo.com',
                'cep' => '64600-000',
                'logradouro' => 'Rodovia PI-112',
                'numero' => 'Km 15',
                'complemento' => 'Zona Rural',
                'bairro' => 'Zona Rural',
                'cidade' => 'José de Freitas',
                'estado' => 'PI',
                'inscricao_estadual' => '456789123',
                'car' => 'PI-2501234-MNOP5432109876QRST',
                'tipo_pessoa' => 'fisica',
                'observacoes' => 'Criação extensiva de gado nelore. Propriedade com 250 hectares.',
                'data_cadastro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. PROPRIEDADES
        $this->command->info('🏡 Criando propriedades...');
        DB::table('propriedades')->insert([
            [
                'nome' => 'Fazenda Boa Vista',
                'produtor_id' => 1,
                'cep' => '64002-100',
                'logradouro' => 'Rodovia BR-343',
                'numero' => 'Km 8',
                'complemento' => 'Fazenda',
                'bairro' => 'Zona Rural',
                'municipio' => 'Teresina',
                'uf' => 'PI',
                'inscricao_estadual' => '123456789',
                'car' => 'PI-1501100-ABCD1234567890123456789012',
                'matricula' => '12345',
                'cartorio' => '1º Ofício de Registro de Imóveis de Teresina',
                'latitude' => -5.089203,
                'longitude' => -42.801942,
                'area_total' => 250.50,
                'area_preservada' => 50.00,
                'tipo_exploracao' => 'mista',
                'data_aquisicao' => now()->subYears(5)->toDateString(),
                'observacoes' => 'Propriedade com infraestrutura completa: curral, cercas elétricas, 2 açudes, casa sede e galpão. Possui energia elétrica e água encanada.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Sítio Santa Maria',
                'produtor_id' => 2,
                'cep' => '64600-000',
                'logradouro' => 'Estrada Vicinal',
                'numero' => 'S/N',
                'complemento' => 'Sítio',
                'bairro' => 'Zona Rural',
                'municipio' => 'José de Freitas',
                'uf' => 'PI',
                'inscricao_estadual' => '987654321',
                'car' => 'PI-1506500-WXYZ9876543210987654321098',
                'matricula' => '67890',
                'cartorio' => 'Registro de Imóveis de José de Freitas',
                'latitude' => -4.757851,
                'longitude' => -42.579462,
                'area_total' => 180.75,
                'area_preservada' => 36.15,
                'tipo_exploracao' => 'pecuaria',
                'data_aquisicao' => now()->subYears(8)->toDateString(),
                'observacoes' => 'Propriedade com pastagens renovadas, sistema de irrigação por aspersão e casa de trabalhador. Possui acesso por estrada de terra.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Fazenda São José',
                'produtor_id' => 3,
                'cep' => '64460-000',
                'logradouro' => 'Rodovia PI-112',
                'numero' => 'Km 15',
                'complemento' => 'Fazenda',
                'bairro' => 'Zona Rural',
                'municipio' => 'União',
                'uf' => 'PI',
                'inscricao_estadual' => '456789123',
                'car' => 'PI-1501200-MNOP5432109876543210987654',
                'matricula' => '54321',
                'cartorio' => 'Cartório de Registro de Imóveis de União',
                'latitude' => -4.586642,
                'longitude' => -42.861542,
                'area_total' => 450.00,
                'area_preservada' => 90.00,
                'tipo_exploracao' => 'agricultura',
                'data_aquisicao' => now()->subYears(12)->toDateString(),
                'observacoes' => 'Grande propriedade com foco em agricultura de grãos. Possui armazém, máquinas agrícolas, silos e sistema de irrigação por gotejamento. Rio perene atravessa a propriedade.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4. UNIDADES DE PRODUÇÃO
        $this->command->info('🌾 Criando unidades de produção...');
        DB::table('unidades_producao')->insert([
            [
                'nome_cultura' => 'Milho Híbrido',
                'area_total_ha' => 50.00,
                'coordenadas_geograficas' => json_encode(['lat' => -5.0892, 'lng' => -42.8019]),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome_cultura' => 'Soja',
                'area_total_ha' => 75.50,
                'coordenadas_geograficas' => json_encode(['lat' => -5.0920, 'lng' => -42.8100]),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome_cultura' => 'Capim Brachiaria',
                'area_total_ha' => 120.00,
                'coordenadas_geograficas' => json_encode(['lat' => -5.1000, 'lng' => -42.8200]),
                'propriedade_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. REBANHOS
        $this->command->info('🐄 Criando rebanhos...');
        DB::table('rebanhos')->insert([
            [
                'especie' => 'Bovino',
                'quantidade' => 150,
                'finalidade' => 'corte',
                'data_atualizacao' => now()->subDays(5),
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'especie' => 'Bovino',
                'quantidade' => 80,
                'finalidade' => 'leite',
                'data_atualizacao' => now()->subDays(3),
                'propriedade_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'especie' => 'Caprino',
                'quantidade' => 45,
                'finalidade' => 'corte',
                'data_atualizacao' => now()->subDays(7),
                'propriedade_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 6. PASTOS
        $this->command->info('🌱 Criando pastos...');
        DB::table('pastos')->insert([
            [
                'nome' => 'Pasto 1 - Brachiaria',
                'codigo' => 'P-001',
                'area_hectares' => 25.00,
                'tipo_pastagem' => 'Brachiaria Brizantha',
                'qualidade' => 'boa',
                'status' => 'disponivel',
                'capacidade_animais' => 85,
                'animais_atual' => 0,
                'propriedade_id' => 1,
                'tem_agua' => true,
                'tem_sombra' => true,
                'tem_cocho' => true,
                'tem_saleiro' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Pasto 2 - Mombaça',
                'codigo' => 'P-002',
                'area_hectares' => 30.00,
                'tipo_pastagem' => 'Mombaça',
                'qualidade' => 'excelente',
                'status' => 'ocupado',
                'capacidade_animais' => 120,
                'animais_atual' => 95,
                'propriedade_id' => 1,
                'tem_agua' => true,
                'tem_sombra' => true,
                'tem_cocho' => true,
                'tem_saleiro' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Pasto 3 - Tanzânia',
                'codigo' => 'P-003',
                'area_hectares' => 20.00,
                'tipo_pastagem' => 'Tanzânia',
                'qualidade' => 'regular',
                'status' => 'em_reforma',
                'capacidade_animais' => 60,
                'animais_atual' => 0,
                'propriedade_id' => 2,
                'tem_agua' => true,
                'tem_sombra' => false,
                'tem_cocho' => false,
                'tem_saleiro' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 7. CATEGORIAS FINANCEIRAS
        $this->command->info('💰 Criando categorias financeiras...');
        DB::table('categorias_financeiras')->insert([
            ['nome' => 'Venda de Gado', 'tipo' => 'receita', 'descricao' => 'Vendas de animais', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Venda de Leite', 'tipo' => 'receita', 'descricao' => 'Vendas de produção leiteira', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Venda de Grãos', 'tipo' => 'receita', 'descricao' => 'Vendas de milho, soja, etc', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Outras Receitas', 'tipo' => 'receita', 'descricao' => 'Outras fontes de receita', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Ração e Alimentação', 'tipo' => 'despesa', 'descricao' => 'Compra de ração e suplementos', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Medicamentos', 'tipo' => 'despesa', 'descricao' => 'Vacinas e remédios', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Mão de Obra', 'tipo' => 'despesa', 'descricao' => 'Salários e encargos', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Manutenção', 'tipo' => 'despesa', 'descricao' => 'Reparos em geral', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Combustível', 'tipo' => 'despesa', 'descricao' => 'Diesel e gasolina', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 8. LOTES
        $this->command->info('📦 Criando lotes...');
        DB::table('lotes')->insert([
            [
                'nome' => 'Lote 001 - Nelore',
                'codigo' => 'L-001',
                'finalidade' => 'engorda',
                'status' => 'ativo',
                'data_formacao' => now()->subMonths(3),
                'data_prevista_venda' => now()->addMonths(2),
                'propriedade_id' => 1,
                'pasto_id' => 1,
                'quantidade_animais' => 45,
                'peso_medio_inicial' => 330.0,
                'peso_medio_atual' => 380.5,
                'observacoes' => 'Animais em fase final de engorda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Lote 002 - Nelore',
                'codigo' => 'L-002',
                'finalidade' => 'recria',
                'status' => 'ativo',
                'data_formacao' => now()->subMonths(2),
                'data_prevista_venda' => now()->addMonths(6),
                'propriedade_id' => 1,
                'pasto_id' => 2,
                'quantidade_animais' => 50,
                'peso_medio_inicial' => 280.0,
                'peso_medio_atual' => 320.0,
                'observacoes' => 'Animais em recria, bom desenvolvimento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Lote 003 - Girolando',
                'codigo' => 'L-003',
                'finalidade' => 'reproducao',
                'status' => 'ativo',
                'data_formacao' => now()->subMonths(4),
                'data_prevista_venda' => null,
                'propriedade_id' => 2,
                'pasto_id' => 3,
                'quantidade_animais' => 30,
                'peso_medio_inicial' => 420.0,
                'peso_medio_atual' => 450.0,
                'observacoes' => 'Vacas em lactação',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 9. ANIMAIS (amostra de 30 animais)
        $this->command->info('🐮 Criando animais...');
        $animais = [];

        // 15 animais do Lote 1
        for ($i = 1; $i <= 15; $i++) {
            $animais[] = [
                'identificacao' => 'NEL' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nome_numero' => str_pad($i, 4, '0', STR_PAD_LEFT),
                'sexo' => $i % 3 == 0 ? 'femea' : 'macho',
                'raca' => 'Nelore',
                'categoria_atual' => $i % 3 == 0 ? 'novilha' : 'novilho',
                'situacao' => 'ativo',
                'data_nascimento' => now()->subYears(2)->subMonths(rand(0, 6)),
                'data_entrada' => now()->subMonths(3),
                'peso_entrada' => 330.0,
                'peso_atual' => 380.5 + rand(-50, 50),
                'lote_id' => 1,
                'propriedade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 10 animais do Lote 2 - Distribuídos nos últimos 4 meses
        for ($i = 1; $i <= 10; $i++) {
            $mesAtras = 4 - floor($i / 3); // Distribui entre 0-4 meses atrás
            $animais[] = [
                'identificacao' => 'REC' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nome_numero' => str_pad($i, 4, '0', STR_PAD_LEFT),
                'sexo' => $i % 2 == 0 ? 'femea' : 'macho',
                'raca' => 'Nelore',
                'categoria_atual' => 'bezerro',
                'situacao' => 'ativo',
                'data_nascimento' => now()->subYears(1)->subMonths(rand(0, 6)),
                'data_entrada' => now()->subMonths($mesAtras),
                'peso_entrada' => 280.0,
                'peso_atual' => 320.0 + rand(-40, 40),
                'lote_id' => 2,
                'propriedade_id' => 1,
                'created_at' => now()->subMonths($mesAtras),
                'updated_at' => now()->subMonths($mesAtras),
            ];
        }

        // 5 animais do Lote 3 - Distribuídos nos últimos 3 meses
        for ($i = 1; $i <= 5; $i++) {
            $mesAtras = $i - 1; // 0, 1, 2, 3, 4 meses atrás
            $animais[] = [
                'identificacao' => 'GIR' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nome_numero' => str_pad($i, 4, '0', STR_PAD_LEFT),
                'sexo' => 'femea',
                'raca' => 'Girolando',
                'categoria_atual' => 'vaca',
                'situacao' => 'ativo',
                'data_nascimento' => now()->subYears(3)->subMonths(rand(0, 12)),
                'data_entrada' => now()->subMonths($mesAtras),
                'peso_entrada' => 420.0,
                'peso_atual' => 450.0 + rand(-30, 30),
                'lote_id' => 3,
                'propriedade_id' => 2,
                'created_at' => now()->subMonths($mesAtras),
                'updated_at' => now()->subMonths($mesAtras),
            ];
        }

        DB::table('animais')->insert($animais);

        // 10. TRANSAÇÕES FINANCEIRAS - Distribuídas nos últimos 6 meses
        $this->command->info('💵 Criando transações financeiras distribuídas...');

        $transacoes = [];

        // Gerar transações para cada mês nos últimos 6 meses
        for ($mes = 0; $mes < 6; $mes++) {
            $dataBase = now()->copy()->subMonths($mes);
            $mesAno = $dataBase->format('M/Y');

            // 2 Receitas por mês
            $transacoes[] = [
                'tipo' => 'receita',
                'categoria_id' => 1,
                'descricao' => "Venda de gado - $mesAno",
                'valor' => rand(35000, 55000),
                'data' => $dataBase->copy()->day(rand(10, 25)),
                'forma_pagamento' => 'Transferência bancária',
                'propriedade_id' => 1,
                'lote_id' => 1,
                'observacoes' => 'Venda mensal de gado',
                'created_at' => $dataBase,
                'updated_at' => $dataBase,
            ];

            $transacoes[] = [
                'tipo' => 'receita',
                'categoria_id' => 2,
                'descricao' => "Venda de leite - $mesAno",
                'valor' => rand(15000, 22000),
                'data' => $dataBase->copy()->day(rand(5, 20)),
                'forma_pagamento' => 'Depósito',
                'propriedade_id' => 2,
                'lote_id' => 3,
                'observacoes' => 'Produção mensal de leite',
                'created_at' => $dataBase,
                'updated_at' => $dataBase,
            ];

            // 2 Despesas por mês
            $transacoes[] = [
                'tipo' => 'despesa',
                'categoria_id' => 5,
                'descricao' => "Ração - $mesAno",
                'valor' => rand(10000, 15000),
                'data' => $dataBase->copy()->day(rand(3, 15)),
                'forma_pagamento' => 'Boleto',
                'propriedade_id' => 1,
                'lote_id' => null,
                'observacoes' => 'Ração mensal',
                'created_at' => $dataBase,
                'updated_at' => $dataBase,
            ];

            $transacoes[] = [
                'tipo' => 'despesa',
                'categoria_id' => 7,
                'descricao' => "Salários - $mesAno",
                'valor' => 8500.00,
                'data' => $dataBase->copy()->day(5),
                'forma_pagamento' => 'Transferência',
                'propriedade_id' => 1,
                'lote_id' => null,
                'observacoes' => '3 funcionários',
                'created_at' => $dataBase,
                'updated_at' => $dataBase,
            ];
        }

        DB::table('transacoes_financeiras')->insert($transacoes);

        // 11. ESTOQUE
        $this->command->info('📦 Criando itens de estoque...');
        DB::table('estoque')->insert([
            [
                'nome' => 'Ração Concentrada 25kg',
                'categoria' => 'racao',
                'quantidade' => 120.00,
                'unidade_medida' => 'sacos',
                'valor_unitario' => 85.00,
                'valor_total' => 10200.00,
                'data_compra' => now()->subDays(5),
                'data_validade' => now()->addMonths(6),
                'fornecedor' => 'Agropecuária Santos',
                'propriedade_id' => 1,
                'observacoes' => 'Estoque para 2 meses',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Sal Mineral 20kg',
                'categoria' => 'suplemento',
                'quantidade' => 50.00,
                'unidade_medida' => 'sacos',
                'valor_unitario' => 65.00,
                'valor_total' => 3250.00,
                'data_compra' => now()->subDays(10),
                'data_validade' => now()->addMonths(12),
                'fornecedor' => 'Nutrição Animal Ltda',
                'propriedade_id' => 1,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Vacina contra Febre Aftosa',
                'categoria' => 'vacina',
                'quantidade' => 200.00,
                'unidade_medida' => 'doses',
                'valor_unitario' => 12.50,
                'valor_total' => 2500.00,
                'data_compra' => now()->subDays(8),
                'data_validade' => now()->addMonths(3),
                'fornecedor' => 'VetMed Produtos',
                'propriedade_id' => 1,
                'observacoes' => 'Manter refrigerado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Vermífugo Ivermectina 500ml',
                'categoria' => 'vermifugo',
                'quantidade' => 15.00,
                'unidade_medida' => 'frascos',
                'valor_unitario' => 45.00,
                'valor_total' => 675.00,
                'data_compra' => now()->subDays(8),
                'data_validade' => now()->addYears(2),
                'fornecedor' => 'VetMed Produtos',
                'propriedade_id' => 1,
                'observacoes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 12. MANEJOS
        $this->command->info('📋 Criando registros de manejo...');
        DB::table('manejos')->insert([
            [
                'tipo' => 'vacinacao',
                'data' => now()->subDays(8),
                'animal_id' => 1,
                'peso' => null,
                'produto_aplicado' => 'Vacina Febre Aftosa',
                'dose' => '2ml',
                'responsavel' => 'Dr. Pedro Veterinário',
                'veterinario' => 'Dr. Pedro Silva',
                'propriedade_id' => 1,
                'custo' => 50.00,
                'observacoes' => 'Vacinação anual',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'pesagem',
                'data' => now()->subDays(5),
                'animal_id' => 1,
                'peso' => 408.5,
                'produto_aplicado' => null,
                'dose' => null,
                'responsavel' => 'José - Funcionário',
                'veterinario' => null,
                'propriedade_id' => 1,
                'custo' => 0.00,
                'observacoes' => 'Pesagem mensal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'vermifugacao',
                'data' => now()->subDays(12),
                'animal_id' => 2,
                'peso' => null,
                'produto_aplicado' => 'Ivermectina',
                'dose' => '1ml/50kg',
                'responsavel' => 'Dr. Pedro Veterinário',
                'veterinario' => 'Dr. Pedro Silva',
                'propriedade_id' => 1,
                'custo' => 35.00,
                'observacoes' => 'Prevenção de parasitas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('');
        $this->command->info('✅ Seed completo executado com sucesso!');
        $this->command->info('📊 Resumo:');
        $this->command->info('   • 2 usuários');
        $this->command->info('   • 3 produtores rurais');
        $this->command->info('   • 3 propriedades');
        $this->command->info('   • 3 unidades de produção');
        $this->command->info('   • 3 rebanhos');
        $this->command->info('   • 3 pastos');
        $this->command->info('   • 9 categorias financeiras');
        $this->command->info('   • 3 lotes');
        $this->command->info('   • 30 animais (distribuídos ao longo de 6 meses)');
        $this->command->info('   • 24 transações financeiras (4 por mês x 6 meses)');
        $this->command->info('   • 4 itens em estoque');
        $this->command->info('   • 3 manejos');
        $this->command->info('');
    }
}

