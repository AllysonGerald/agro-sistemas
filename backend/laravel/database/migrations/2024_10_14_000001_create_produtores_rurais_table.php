<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtores_rurais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf_cnpj', 18)->unique();
            $table->string('telefone', 20);
            $table->string('email')->unique();

            // Endereço detalhado
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();

            // Documentos e informações complementares
            $table->string('inscricao_estadual', 20)->nullable();
            $table->string('car', 50)->nullable()->comment('Cadastro Ambiental Rural');
            $table->enum('tipo_pessoa', ['fisica', 'juridica'])->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamp('data_cadastro')->useCurrent();
            $table->timestamps();

            $table->index(['nome', 'cpf_cnpj']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtores_rurais');
    }
};
