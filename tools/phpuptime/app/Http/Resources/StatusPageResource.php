<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'domain' => $this->domain,
            'slug' => $this->slug,
            'url' => $this->url,
            'logo_url' => $this->logo_url,
            'favicon_url' => $this->favicon_url,
            'monitors' => $this->monitors,
            'logo' => $this->logo,
            'favicon' => $this->favicon,
            'website_url' => $this->website_url,
            'contact_url' => $this->contact_url,
            'custom_js' => $this->custom_js,
            'custom_css' => $this->custom_css,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'noindex' => $this->noindex,
            'privacy' => $this->privacy,
            'password' => ($this->password ? true : false),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
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
