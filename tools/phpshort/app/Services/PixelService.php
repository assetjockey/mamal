<?php

namespace App\Services;

use App\Models\Pixel;
use Illuminate\Support\Facades\Auth;

class PixelService
{
    /**
     * Store a new pixel.
     */
    public function store(array $data): Pixel
    {
        $pixel = new Pixel;

        $pixel->user_id = Auth::user()->id;
        $pixel->name = $data['name'];
        $pixel->type = $data['type'];
        $pixel->value = $data['value'];

        $pixel->save();

        return $pixel;
    }

    /**
     * Update an existing pixel.
     */
    public function update(Pixel $pixel, array $data): Pixel
    {
        if (array_key_exists('name', $data)) {
            $pixel->name = $data['name'];
        }

        if (array_key_exists('type', $data)) {
            $pixel->type = $data['type'];
        }

        if (array_key_exists('value', $data)) {
            $pixel->value = $data['value'];
        }

        $pixel->save();

        return $pixel;
    }
}