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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            
            // Identificação
            $table->string('nome'); // Ex: Lote 3, Lote de Engorda A
            $table->string('codigo')->unique(); // Ex: L-001, L-ENG-01
            
            // Características
            $table->enum('finalidade', ['engorda', 'reproducao', 'cria', 'recria', 'terminacao'])->nullable();
            $table->enum('status', ['ativo', 'finalizado', 'em_formacao'])->default('ativo');
            
            // Datas
            $table->date('data_formacao')->nullable();
            $table->date('data_prevista_venda')->nullable();
            
            // Localização
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            $table->foreignId('pasto_id')->nullable()->constrained('pastos')->onDelete('set null');
            
            // Métricas
            $table->integer('quantidade_animais')->default(0);
            $table->decimal('peso_medio_inicial', 8, 2)->nullable();
            $table->decimal('peso_medio_atual', 8, 2)->nullable();
            
            // Observações
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('codigo');
            $table->index('status');
            $table->index('propriedade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
