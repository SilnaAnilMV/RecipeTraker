<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\RecipeController; 
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;


Route::middleware('guest')->group(function () {
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Default route to get authenticated user
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


// Recipe API routes (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::match(['get', 'post'],'recipes', [RecipeController::class, 'recipes']);
    Route::get('recipes/{id}', [RecipeController::class, 'show']);
    Route::match(['put', 'patch'], 'recipes/{id}', [RecipeController::class, 'update']);
    Route::get('recipes/difficulty/{level}', [RecipeController::class, 'filterByDifficulty']);
    Route::get('search/recipes', [RecipeController::class, 'search']);
    Route::delete('recipes/{id}', [RecipeController::class, 'destroy']);

});
