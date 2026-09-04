<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $table = 'orders';

     protected function casts(): array
    {
        return [
            'valid_until' => 'datetime',
            'payment_proof_uploaded_at' => 'datetime',
            // Coupons plugin — {code, percentage, original_price, discount_amount}
            // snapshot of the discount applied at checkout (one-time plans only).
            'coupon' => 'array',
        ];
    }

    /**
     * Order belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order belongs to a plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
