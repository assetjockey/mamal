<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'monitor' => $this->monitor,
            'url' => $this->url,
            'cause' => $this->cause,
            'country' => $this->country,
            'city' => $this->city,
            'comment' => $this->comment,
            'alerted' => $this->alerted,
            'duration' => $this->started_at->diffInMicroseconds($this->ended_at ?: Carbon::now()),
            'token' => $this->token,
            'monitor_token' => $this->monitor_token,
            'acknowledged_at' => $this->acknowledged_at,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at
        ];
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
