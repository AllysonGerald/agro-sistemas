<?php

namespace Database\Factories;

use App\Models\UnidadeProducao;
use App\Models\Propriedade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnidadeProducao>
 */
class UnidadeProducaoFactory extends Factory
{
    protected $model = UnidadeProducao::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $culturas = ['milho', 'soja', 'cafe', 'cana_de_acucar', 'arroz', 'feijao_comum', 'trigo', 'algodao'];

        return [
            'nome_cultura' => $this->faker->randomElement($culturas),
            'area_total_ha' => $this->faker->randomFloat(2, 1, 1000),
            'coordenadas_geograficas' => [
                'latitude' => $this->faker->latitude(-33.0, 5.0), // Brasil
                'longitude' => $this->faker->longitude(-74.0, -34.0), // Brasil
            ],
            'propriedade_id' => Propriedade::factory(),
        ];
    }
}
