<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscriber extends Model
{
    protected $guarded = [];

    protected $table = 'subscribers';

    protected $casts = [
        'active_until' => 'datetime',
    ];

    /**
     * Subscription belongs to a single user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Plan belongs to a single user
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Scope: leverages composite index ['user_id', 'status', 'active_until']
    public function scopeActive($query)
    {
        return $query->where('subscribers.status', 'active')
                     ->where('subscribers.active_until', '>', now());
    }

    // Attribute accessor: check if THIS subscription is active
    public function getIsActiveAttribute()
    {
        return $this->status === 'active' 
               && $this->active_until 
               && $this->active_until->isFuture();
    }
}
