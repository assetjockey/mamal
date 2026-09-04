<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the website that owns the stat.
     */
    public function website()
    {
        return $this->belongsTo('App\Models\Website');
    }

    /**
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function scopeSearch(Builder $query, $value, $column)
    {
        return $query->where($column, 'like', '%' . $value . '%');
    }
}
