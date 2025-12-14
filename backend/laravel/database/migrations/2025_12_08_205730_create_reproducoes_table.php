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
        Schema::create('reproducoes', function (Blueprint $table) {
            $table->id();
            
            // Tipo de reprodução
            $table->enum('tipo', ['monta_natural', 'inseminacao_artificial', 'fertilizacao_in_vitro'])->default('monta_natural');
            
            // Animais envolvidos
            $table->foreignId('femea_id')->constrained('animais')->onDelete('cascade');
            $table->foreignId('macho_id')->nullable()->constrained('animais')->onDelete('set null');
            $table->string('touro_nome')->nullable(); // Nome do touro se não estiver cadastrado
            $table->string('raca_touro')->nullable(); // Raça do touro
            
            // Datas importantes
            $table->date('data_cobertura')->nullable(); // Data da cobertura/inseminação
            $table->date('data_prevista_parto')->nullable(); // Data prevista do parto (+ 9 meses)
            $table->date('data_diagnostico')->nullable(); // Data do diagnóstico de gestação
            $table->date('data_parto')->nullable(); // Data real do parto
            
            // Status
            $table->enum('status', [
                'aguardando_diagnostico',
                'prenha',
                'vazia',
                'abortou',
                'parto_realizado'
            ])->default('aguardando_diagnostico');
            
            // Detalhes do parto
            $table->integer('numero_crias')->default(1);
            $table->enum('tipo_parto', ['normal', 'cesariana', 'assistido'])->nullable();
            $table->enum('dificuldade_parto', ['facil', 'medio', 'dificil'])->nullable();
            
            // Crias
            $table->json('crias')->nullable(); // IDs dos animais nascidos
            
            // Responsável
            $table->string('responsavel')->nullable();
            $table->string('veterinario')->nullable();
            
            // Localização
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            
            // Observações
            $table->text('observacoes')->nullable();
            
            // Custos
            $table->decimal('custo_total', 10, 2)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('femea_id');
            $table->index('macho_id');
            $table->index('status');
            $table->index('data_prevista_parto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reproducoes');
    }
};
