<?php

namespace Tests\Unit;

use Tests\TestCase;   
use App\Models\User;
use App\Models\Recipe;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecipeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_can_add_recipe()
    {
        $payload = [
            'name'        => 'Tomato Pasta',
            'ingredients' => 'tomato, pasta, salt',
            'prep_time'   => 10,
            'cook_time'   => 20,
            'difficulty'  => 'easy',
            'description' => 'Simple and delicious tomato pasta.',
        ];

        $response = $this->postJson('/api/recipes', $payload);

        $response->assertStatus(201) // Laravel returns 201 on successful resource creation
                ->assertJsonFragment([
                    'name'        => 'Tomato Pasta',
                    'prep_time'   => 10,
                    'cook_time'   => 20,
                    'difficulty'  => 'easy',
                ]);

        $this->assertDatabaseHas('recipes', [
            'name' => 'Tomato Pasta',
        ]);
    }

    public function test_can_list_recipes()
    {
        Recipe::factory()->create([
            'name'        => 'Grilled Cheese',
            'ingredients' => 'bread, cheese, butter',
            'prep_time'   => 5,
            'cook_time'   => 5,
            'difficulty'  => 'easy',
            'description' => 'Classic grilled cheese sandwich.',
        ]);

        $response = $this->getJson('/api/recipes');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'name' => 'Grilled Cheese',
                ]);
    }



    public function test_can_show_single_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->getJson("/api/recipes/{$recipe->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $recipe->id, 'name' => $recipe->name]);
    }

    public function test_can_update_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $payload = [
            'name' => 'Updated Recipe Name',
            'difficulty' => 'medium',
        ];

        $response = $this->putJson("/api/recipes/{$recipe->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Recipe Name', 'difficulty' => 'medium']);
    }

    public function test_update_returns_404_on_missing_recipe(): void
    {
        $response = $this->putJson('/api/recipes/999999', [
            'name' => 'Should fail',
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_recipe(): void
    {
        $recipe = Recipe::factory()->create();

        $response = $this->deleteJson("/api/recipes/{$recipe->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_delete_returns_404_on_missing_recipe(): void
    {
        $response = $this->deleteJson('/api/recipes/999999');
        $response->assertStatus(404);
    }

    public function test_filter_by_difficulty_returns_correct_recipes(): void
    {
        Recipe::factory()->create(['difficulty' => 'easy']);
        Recipe::factory()->create(['difficulty' => 'hard']);

        $response = $this->getJson('/api/recipes/difficulty/easy');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['difficulty' => 'easy']);
    }

    public function test_filter_by_difficulty_invalid_level_returns_400(): void
    {
        $response = $this->getJson('/api/recipes/difficulty/invalid');

        $response->assertStatus(400);
    }

    public function test_search_by_ingredients_and_time_returns_expected_results(): void
    {
        $recipe1 = Recipe::factory()->create([
            'ingredients' => 'salt,pepper',
            'prep_time' => 5,
            'cook_time' => 10,
        ]);
        $recipe2 = Recipe::factory()->create([
            'ingredients' => 'salt,sugar',
            'prep_time' => 20,
            'cook_time' => 15,
        ]);

        $payload = [
            'ingredients' => ['salt', 'pepper'],
            'min_time' => 10,
            'max_time' => 20,
        ];

        // Pass query parameters as query string using http_build_query
        $response = $this->getJson('/api/search/recipes?' . http_build_query($payload));

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $recipe1->id])
                 ->assertJsonMissing(['id' => $recipe2->id]);
    }

    public function test_search_by_ingredients_and_time_validation_errors(): void
    {
        // Pass invalid query params as a query string (not as second arg to getJson)
        $query = http_build_query([
            'ingredients' => [],  // empty array - invalid
            'min_time' => -1,     // invalid min_time
            'max_time' => 0,
        ]);

        $response = $this->getJson('/api/search/recipes?' . $query);

        $response->assertStatus(422)  // validation error
                 ->assertJsonValidationErrors(['ingredients', 'min_time']);
    }
}
