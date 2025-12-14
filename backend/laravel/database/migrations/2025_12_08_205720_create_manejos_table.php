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
        Schema::create('manejos', function (Blueprint $table) {
            $table->id();
            
            // Tipo de manejo
            $table->enum('tipo', [
                'pesagem',
                'vacinacao',
                'vermifugacao',
                'curativo',
                'castracao',
                'descorna',
                'marcacao',
                'transferencia',
                'exame',
                'outro'
            ]);
            
            // Data do manejo
            $table->date('data');
            $table->time('hora')->nullable();
            
            // Animal
            $table->foreignId('animal_id')->constrained('animais')->onDelete('cascade');
            
            // Detalhes específicos por tipo
            // Pesagem
            $table->decimal('peso', 8, 2)->nullable(); // Peso em kg
            
            // Medicamentos/Produtos
            $table->string('produto_aplicado')->nullable(); // Nome do produto (vacina, vermífugo, etc)
            $table->string('dose')->nullable(); // Dosagem aplicada
            $table->string('lote_produto')->nullable(); // Lote do produto
            $table->date('data_proxima_aplicacao')->nullable(); // Próxima data necessária
            
            // Responsável
            $table->string('responsavel')->nullable(); // Nome do responsável pelo manejo
            $table->string('veterinario')->nullable(); // Nome do veterinário (se aplicável)
            
            // Localização
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            
            // Observações e resultados
            $table->text('observacoes')->nullable();
            $table->enum('resultado', ['sucesso', 'pendente', 'falha'])->default('sucesso');
            
            // Custos
            $table->decimal('custo', 8, 2)->nullable(); // Custo do manejo
            
            // Anexos
            $table->json('fotos')->nullable(); // URLs de fotos do manejo
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo');
            $table->index('data');
            $table->index('animal_id');
            $table->index('propriedade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manejos');
    }
};
