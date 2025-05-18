<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'ingredients'   => explode(',', $this->ingredients),
            'prep_time'     => $this->prep_time,
            'cook_time'     => $this->cook_time,
            'total_time'    => $this->prep_time + $this->cook_time,
            'difficulty'    => $this->difficulty,
            'description'   => $this->description,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
