<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Http\Resources\RecipeResource;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    public function recipes(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated  = $request->validate([
                            'name'          => 'required|string',
                            'ingredients'   => 'required|string',
                            'ingredients.*' => 'string',
                            'prep_time'     => 'required|integer|min:0',
                            'cook_time'     => 'required|integer|min:0',
                            'difficulty'    => ['required', Rule::in(['easy', 'medium', 'hard'])],
                            'description'   => 'required|string',
                        ]);


            $recipe     = Recipe::create($validated);

            return new RecipeResource($recipe);
        }

        $recipes    = Recipe::all();
        return RecipeResource::collection($recipes);
    }

    public function show($id)
    {
        $recipe     = Recipe::find($id);
        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }
        return new RecipeResource($recipe);
    }

    public function update(Request $request, $id)
    {
        $recipe     = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        $validated = $request->validate([
                    'name'          => 'sometimes|required|string',
                    'ingredients'   => 'sometimes|required|string',
                    'ingredients.*' => 'string',
                    'prep_time'     => 'sometimes|required|integer|min:0',
                    'cook_time'     => 'sometimes|required|integer|min:0',
                    'difficulty'    => ['sometimes', 'required', Rule::in(['easy', 'medium', 'hard'])],
                    'description'   => 'sometimes|required|string',
                ]);

        $recipe->update($validated);

        return new RecipeResource($recipe);
    }

    public function destroy($id)
    {
        $recipe     = Recipe::find($id);
        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }
        $recipe->delete();
        return response()->json(null, 204);
    }

    public function filterByDifficulty($level)
    {
        if (!in_array($level, ['easy', 'medium', 'hard'])) {
            return response()->json(['message' => 'Invalid difficulty level'], 400);
        }
        $recipes    = Recipe::where('difficulty', $level)->get();
        return RecipeResource::collection($recipes);
    }

    // Search by cooking time and ingredients
    public function search(Request $request)
    {
        $request->validate([
            'ingredients'   => 'required|array|min:1',
            'ingredients.*' => 'string',
            'min_time'      => 'required|integer|min:0',
            'max_time'      => 'required|integer|gte:min_time',
        ]);

        $requestedIngredients   = $request->ingredients;
        $minTime                = intval($request->min_time);
        $maxTime                = intval($request->max_time);



        $requestedIngredients   = array_map('trim', $requestedIngredients);


        $recipes                = Recipe::where(function ($query) use ($requestedIngredients) {
                                    foreach ($requestedIngredients as $ingredient) {
                                        $query->where('ingredients', 'LIKE', '%' . $ingredient . '%');
                                    }
                                })
                                ->whereRaw('(prep_time + cook_time) BETWEEN ? AND ?', [$minTime, $maxTime])
                                ->get();

        return RecipeResource::collection($recipes);
    }
}
