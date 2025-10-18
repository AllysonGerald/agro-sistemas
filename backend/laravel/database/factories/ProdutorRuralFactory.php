<?php

namespace Database\Factories;

use App\Models\ProdutorRural;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdutorRural>
 */
class ProdutorRuralFactory extends Factory
{
    protected $model = ProdutorRural::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name,
            'cpf_cnpj' => $this->faker->numerify('###########'),
            'telefone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'endereco' => $this->faker->address,
            'data_cadastro' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }

    /**
     * Indicate that the producer is a company (CNPJ).
     */
    public function empresa(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => $this->faker->company . ' Ltda',
            'cpf_cnpj' => $this->faker->numerify('##############'),
        ]);
    }

    /**
     * Indicate that the producer is a person (CPF).
     */
    public function pessoa(): static
    {
        return $this->state(fn (array $attributes) => [
            'nome' => $this->faker->name,
            'cpf_cnpj' => $this->faker->numerify('###########'),
        ]);
    }
}
