<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Recipe::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'ingredients' => implode(', ', $this->faker->randomElements(['salt', 'pepper', 'sugar', 'flour', 'butter'], 3)),
            'prep_time' => $this->faker->numberBetween(5, 60),
            'cook_time' => $this->faker->numberBetween(10, 120),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'description' => $this->faker->paragraph(),
        ];
    }
}
