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
        Schema::create('animais', function (Blueprint $table) {
            $table->id();
            
            // Identificação básica
            $table->string('identificacao')->unique(); // Ex: MT-8999, MT-4028
            $table->string('nome_numero')->nullable(); // Ex: 999, 0043
            $table->string('foto_url')->nullable(); // URL da foto do animal
            
            // Características
            $table->enum('sexo', ['macho', 'femea'])->default('macho');
            $table->string('raca')->nullable(); // Ex: Nelore, Angus
            $table->enum('categoria_atual', ['bezerro', 'bezerra', 'novilho', 'novilha', 'boi', 'vaca', 'touro'])->nullable();
            $table->enum('situacao', ['ativo', 'vendido', 'morto', 'transferido'])->default('ativo');
            
            // Datas importantes
            $table->date('data_nascimento')->nullable();
            $table->date('data_entrada')->nullable();
            $table->integer('idade_meses')->nullable(); // Idade em meses
            $table->integer('total_dias_ativo')->default(0);
            
            // Peso e medidas
            $table->decimal('peso_entrada', 8, 2)->nullable(); // Peso na entrada (kg)
            $table->decimal('peso_atual', 8, 2)->nullable(); // Peso atual (kg)
            
            // Origem e genealogia
            $table->string('origem_materna')->nullable();
            $table->string('origem_paterna')->nullable();
            
            // Relacionamentos
            $table->foreignId('rebanho_id')->nullable()->constrained('rebanhos')->onDelete('set null');
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->onDelete('set null');
            
            // Informações adicionais
            $table->string('finalidade_lote')->nullable(); // Ex: Engorda, Reprodução
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('identificacao');
            $table->index('sexo');
            $table->index('situacao');
            $table->index('propriedade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animais');
    }
};
