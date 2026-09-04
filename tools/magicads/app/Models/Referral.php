<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $guarded = [];

    protected $table = "referrals";

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
