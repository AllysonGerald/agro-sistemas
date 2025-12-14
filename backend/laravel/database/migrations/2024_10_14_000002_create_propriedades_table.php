<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propriedades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');

            // Endereço
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('municipio');
            $table->string('uf', 2);

            // Documentação
            $table->string('inscricao_estadual')->nullable();
            $table->string('car', 50)->nullable()->comment('Cadastro Ambiental Rural');
            $table->string('matricula')->nullable()->comment('Número de matrícula do imóvel');
            $table->string('cartorio')->nullable()->comment('Cartório de registro');

            // Localização
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Áreas
            $table->decimal('area_total', 10, 2); // hectares
            $table->decimal('area_preservada', 10, 2)->nullable()->comment('Área de preservação/reserva legal');

            // Tipo e datas
            $table->enum('tipo_exploracao', ['pecuaria', 'agricultura', 'mista', 'silvicultura', 'outro'])->nullable();
            $table->date('data_aquisicao')->nullable();

            // Outros
            $table->text('observacoes')->nullable();

            $table->foreignId('produtor_id')->constrained('produtores_rurais')->onDelete('cascade');
            $table->timestamps();

            $table->index(['municipio', 'uf']);
            $table->index('produtor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propriedades');
    }
};
