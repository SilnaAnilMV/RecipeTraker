<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Recipe;


class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json       = File::get(database_path('seeders/recipe.json'));
        $recipes    = json_decode($json, true);

        foreach ($recipes as $recipe) {
            Recipe::create([
                'name'          => $recipe['name'],
                'ingredients'   => $recipe['ingredients'],
                'prep_time'     => $recipe['prep_time'],
                'cook_time'     => $recipe['cook_time'],
                'difficulty'    => $recipe['difficulty'],
                'description'   => $recipe['description'],
            ]);
        }
    }
}
