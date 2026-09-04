<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatarUrl,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'plan' => collect($this->plan)->only(['id', 'name', 'features']),
            'brand' => $this->brand,
            'default_privacy' => $this->default_privacy,
            'default_export_detailed' => $this->default_export_detailed,
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
