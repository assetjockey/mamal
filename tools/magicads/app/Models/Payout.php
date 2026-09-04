<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = ['user_id', 'total', 'gateway', 'status', 'request_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
