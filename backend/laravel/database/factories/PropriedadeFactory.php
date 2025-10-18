<?php

namespace Database\Factories;

use App\Models\Propriedade;
use App\Models\ProdutorRural;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Propriedade>
 */
class PropriedadeFactory extends Factory
{
    protected $model = Propriedade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->randomElement(['Fazenda', 'Sítio', 'Chácara']) . ' ' . $this->faker->lastName,
            'municipio' => $this->faker->city,
            'uf' => $this->faker->stateAbbr,
            'inscricao_estadual' => $this->faker->numerify('#########'),
            'area_total' => $this->faker->randomFloat(2, 10, 1000),
            'produtor_id' => ProdutorRural::factory(),
        ];
    }

    /**
     * Indicate that the property is in São Paulo state.
     */
    public function sãoPaulo(): static
    {
        return $this->state(fn (array $attributes) => [
            'uf' => 'SP',
            'municipio' => $this->faker->randomElement([
                'São Paulo',
                'Campinas',
                'Ribeirão Preto',
                'Araraquara',
                'Piracicaba',
                'Sorocaba'
            ]),
        ]);
    }

    /**
     * Indicate that the property is large (over 500 hectares).
     */
    public function grande(): static
    {
        return $this->state(fn (array $attributes) => [
            'area_total' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    /**
     * Indicate that the property is small (under 100 hectares).
     */
    public function pequena(): static
    {
        return $this->state(fn (array $attributes) => [
            'area_total' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }
}
