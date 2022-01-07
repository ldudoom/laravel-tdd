<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Agregamos en el factory tambien los datos para poder realizar la creacion de registros
            'url' => $this->faker->url,
            'description' => $this->faker->text,

            'user_id' => User::factory()->create(),
        ];
    }
}
