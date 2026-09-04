<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceSetting extends Model
{
    protected $fillable = ['currency', 'tax', 'billing_info', 'decimal_points'];
}
