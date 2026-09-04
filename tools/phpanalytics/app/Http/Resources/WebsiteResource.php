<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
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
            'domain' => $this->domain,
            'privacy' => $this->privacy,
            'email' => $this->email,
            'password' => ($this->password ? true : false),
            'exclude_bots' => $this->exclude_bots,
            'exclude_ips' => $this->exclude_ips,
            'favorite' => $this->favorited_at ? true : false,
            'favorited_at' => $this->favorited_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
