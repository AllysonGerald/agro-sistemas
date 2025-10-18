<?php

namespace Database\Factories;

use App\Models\Rebanho;
use App\Models\Propriedade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rebanho>
 */
class RebanhoFactory extends Factory
{
    protected $model = Rebanho::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $especies = ['bovinos', 'suinos', 'caprinos'];
        $finalidades = ['corte', 'leite', 'reproducao', 'misto'];

        return [
            'especie' => $this->faker->randomElement($especies),
            'quantidade' => $this->faker->numberBetween(1, 1000),
            'finalidade' => $this->faker->randomElement($finalidades),
            'propriedade_id' => Propriedade::factory(),
        ];
    }
}
