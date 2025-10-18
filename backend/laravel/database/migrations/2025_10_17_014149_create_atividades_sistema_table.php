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
        Schema::create('atividades_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // produtor_cadastrado, propriedade_cadastrada, rebanho_cadastrado, unidade_cadastrada, relatorio_gerado
            $table->string('titulo');
            $table->text('descricao');
            $table->string('icone');
            $table->string('cor');
            $table->string('usuario')->nullable();
            $table->string('localizacao')->nullable();
            $table->json('dados_extras')->nullable(); // Para armazenar dados específicos de cada atividade
            $table->timestamps();
            $table->index(['tipo', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades_sistema');
    }
};
