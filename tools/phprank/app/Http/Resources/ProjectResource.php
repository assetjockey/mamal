<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'reports' => $this->reports,
            'score' => number_format($this->score / $this->reports),
            'created_at' => $this->created_at
        ];
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return [
            'status' => 200
        ];
    }
}
