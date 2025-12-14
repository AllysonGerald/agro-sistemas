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
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Preferências do Sistema
            $table->string('tema')->default('claro'); // claro, escuro, auto
            $table->string('idioma')->default('pt-BR');
            $table->string('formato_data')->default('DD/MM/YYYY');
            $table->string('moeda')->default('BRL');

            // Notificações
            $table->boolean('notificacao_email')->default(true);
            $table->boolean('notificacao_estoque')->default(true);
            $table->boolean('notificacao_manejo')->default(true);
            $table->boolean('notificacao_financeiro')->default(false);

            $table->timestamps();

            // Índice único por usuário
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};

