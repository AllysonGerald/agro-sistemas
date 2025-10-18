<?php

namespace Database\Factories;

use App\Models\AtividadeSistema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AtividadeSistema>
 */
class AtividadeSistemaFactory extends Factory
{
    protected $model = AtividadeSistema::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipos = ['produtor_cadastrado', 'propriedade_cadastrada', 'rebanho_cadastrado', 'unidade_cadastrada', 'relatorio_gerado'];
        $tipo = $this->faker->randomElement($tipos);

        return [
            'titulo' => $this->faker->sentence(3),
            'descricao' => $this->faker->sentence(6),
            'tipo' => $tipo,
            'icone' => $this->faker->randomElement(['fas fa-user', 'fas fa-home', 'fas fa-cow', 'fas fa-seedling', 'fas fa-file-pdf']),
            'cor' => $this->faker->hexColor(),
            'usuario' => 'Sistema',
            'localizacao' => 'Sistema Agropecuário',
        ];
    }
}
