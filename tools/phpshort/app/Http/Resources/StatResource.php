<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * Get any additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'status' => 200
        ];
    }
}
