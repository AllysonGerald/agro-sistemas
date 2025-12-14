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
        Schema::create('categorias_financeiras', function (Blueprint $table) {
            $table->id();
            
            // Identificação
            $table->string('nome'); // Ex: Venda de Lote, Compra de Insumos, Pasto, Insumos
            $table->string('cor')->nullable(); // Cor para identificação visual (hex)
            $table->string('icone')->nullable(); // Ícone para UI
            
            // Tipo
            $table->enum('tipo', ['receita', 'despesa']); // Receita ou Despesa
            
            // Categoria pai (para subcategorias)
            $table->foreignId('categoria_pai_id')->nullable()->constrained('categorias_financeiras')->onDelete('cascade');
            
            // Status
            $table->boolean('ativo')->default(true);
            
            // Descrição
            $table->text('descricao')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_financeiras');
    }
};
