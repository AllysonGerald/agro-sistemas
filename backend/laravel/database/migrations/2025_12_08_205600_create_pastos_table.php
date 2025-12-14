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
        Schema::create('pastos', function (Blueprint $table) {
            $table->id();
            
            // Identificação
            $table->string('nome'); // Ex: Pasto A, Pasto do Rio
            $table->string('codigo')->unique(); // Ex: P-001, P-RIO
            
            // Características
            $table->decimal('area_hectares', 10, 2); // Área em hectares
            $table->string('tipo_pastagem')->nullable(); // Ex: Braquiária, Tanzânia
            $table->enum('qualidade', ['excelente', 'boa', 'regular', 'ruim', 'degradada'])->nullable();
            $table->enum('status', ['disponivel', 'ocupado', 'em_reforma', 'em_descanso'])->default('disponivel');
            
            // Capacidade
            $table->integer('capacidade_animais')->nullable(); // Lotação máxima
            $table->integer('animais_atual')->default(0); // Quantidade atual de animais
            
            // Datas de manejo
            $table->date('data_ultima_reforma')->nullable();
            $table->date('data_entrada_descanso')->nullable();
            $table->date('data_prevista_liberacao')->nullable();
            
            // Localização
            $table->foreignId('propriedade_id')->constrained('propriedades')->onDelete('cascade');
            $table->json('coordenadas_geograficas')->nullable(); // lat, lng dos limites
            
            // Recursos
            $table->boolean('tem_agua')->default(false);
            $table->boolean('tem_sombra')->default(false);
            $table->boolean('tem_cocho')->default(false);
            $table->boolean('tem_saleiro')->default(false);
            
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
        Schema::dropIfExists('pastos');
    }
};
