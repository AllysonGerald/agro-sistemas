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
        Schema::create('transacoes_financeiras', function (Blueprint $table) {
            $table->id();
            
            // Identificação
            $table->string('descricao'); // Ex: Venda de todo lote 009, Compra de insumos para o gado
            
            // Tipo e categoria
            $table->enum('tipo', ['receita', 'despesa']); // Receita ou Despesa
            $table->foreignId('categoria_id')->constrained('categorias_financeiras')->onDelete('restrict');
            
            // Valor
            $table->decimal('valor', 10, 2); // Valor da transação
            
            // Data
            $table->date('data'); // Data da transação
            
            // Relacionamentos
            $table->foreignId('animal_id')->nullable()->constrained('animais')->onDelete('set null');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->onDelete('set null');
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            
            // Informações adicionais
            $table->string('forma_pagamento')->nullable(); // Ex: Dinheiro, PIX, Cartão, Boleto
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pago');
            $table->text('observacoes')->nullable();
            
            // Anexos
            $table->string('comprovante_url')->nullable(); // URL do comprovante/nota fiscal
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo');
            $table->index('data');
            $table->index('status');
            $table->index('propriedade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes_financeiras');
    }
};
