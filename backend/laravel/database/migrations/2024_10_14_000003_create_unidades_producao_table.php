<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_producao', function (Blueprint $table) {
            $table->id();
            $table->string('nome_cultura');
            $table->decimal('area_total_ha', 8, 2);
            $table->json('coordenadas_geograficas')->nullable(); // {"lat": -23.5505, "lng": -46.6333}
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            $table->timestamps();

            $table->index(['nome_cultura']);
            $table->index('propriedade_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_producao');
    }
};
