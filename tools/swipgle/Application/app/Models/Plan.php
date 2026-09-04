<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'short_description',
        'color',
        'interval',
        'price', // 0 = free
        'auth',
        'storage_space', // null = Unlimited
        'transfer_size', // null = Unlimited
        'transfer_interval', // null = Unlimited
        'transfer_password',
        'transfer_notify',
        'transfer_expiry',
        'transfer_link',
        'advertisements',
        'custom_features',
        'featured_plan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_features' => 'object',
    ];
}
