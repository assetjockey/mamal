<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'home_description',
        'home_keywords',
        'home_author',
        'home_title',
        'home_url',
        'login_description',
        'login_keywords',
        'login_author',
        'login_title',
        'login_url' ,
        'register_description',
        'register_keywords',
        'register_author',
        'register_title',
        'register_url',
    ];
}
