<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estoque', function (Blueprint $table) {
            $table->id();
            
            // Identificação do produto
            $table->string('nome'); // Ex: Ração, Ivermectina, Sal Mineral
            $table->string('codigo')->unique()->nullable(); // Código do produto
            $table->string('marca')->nullable(); // Marca do produto
            
            // Categoria
            $table->enum('categoria', [
                'racao',
                'suplemento',
                'medicamento',
                'vacina',
                'vermifugo',
                'equipamento',
                'outro'
            ]);
            
            // Quantidade
            $table->decimal('quantidade', 10, 2); // Quantidade em estoque
            $table->string('unidade_medida'); // Ex: kg, L, unidade, dose
            $table->decimal('quantidade_minima', 10, 2)->nullable(); // Estoque mínimo
            
            // Valores
            $table->decimal('valor_unitario', 10, 2)->nullable(); // Valor por unidade
            $table->decimal('valor_total', 10, 2)->nullable(); // Valor total em estoque
            
            // Datas
            $table->date('data_compra')->nullable();
            $table->date('data_validade')->nullable();
            $table->string('lote')->nullable(); // Lote do produto
            
            // Localização
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            $table->string('localizacao_fisica')->nullable(); // Ex: Galpão A, Prateleira 3
            
            // Fornecedor
            $table->string('fornecedor')->nullable();
            
            // Status
            $table->enum('status', ['disponivel', 'baixo', 'vencido', 'esgotado'])->default('disponivel');
            
            // Observações
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('categoria');
            $table->index('status');
            $table->index('propriedade_id');
            $table->index('data_validade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque');
    }
};
