<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Page
 *
 * @mixin Builder
 * @package App
 */
class Page extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name', 'slug', 'visibility', 'language', 'content'
    ];

    /**
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function scopeSearchName(Builder $query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')->orWhere('content', 'like', '%' . $value . '%');
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function scopeOfVisibility(Builder $query, $value)
    {
        return $query->where('visibility', '=', $value);
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function scopeOfLanguage(Builder $query, $value)
    {
        return $query->where('language', '=', $value);
    }
}
