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
            $table->string('municipio');
            $table->string('uf', 2);
            $table->string('inscricao_estadual')->nullable();
            $table->decimal('area_total', 10, 2); // hectares
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
