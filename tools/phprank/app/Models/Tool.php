<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    /**
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function scopeSearchName(Builder $query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Get the category of the tool.
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    /**
     * Get the tool's URL.
     */
    public function getUrlAttribute()
    {
        return route('tools.' . str_replace('-', '_', $this->slug));
    }
}
