<?php

namespace App\Services;

use App\Models\Space;
use Illuminate\Support\Facades\Auth;

class SpaceService
{
    /**
     * Store a new space.
     */
    public function store(array $data): Space
    {
        $space = new Space;

        $space->user_id = Auth::user()->id;
        $space->name = $data['name'];

        if (array_key_exists('color', $data)) {
            $space->color = $data['color'];
        } else {
            $space->color = 1;
        }

        $space->save();

        return $space;
    }

    /**
     * Update an existing space.
     */
    public function update(Space $space, array $data): Space
    {
        if (array_key_exists('name', $data)) {
            $space->name = $data['name'];
        }

        if (array_key_exists('color', $data) && array_key_exists($data['color'], spaceColors())) {
            $space->color = $data['color'];
        }

        $space->save();

        return $space;
    }
}