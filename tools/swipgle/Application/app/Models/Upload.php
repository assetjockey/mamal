<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'storage_provider_id',
        'ip',
        'name',
        'filename',
        'mime',
        'extension',
        'size',
        'path',
    ];

    public function storageProvider()
    {
        return $this->belongsTo(StorageProvider::class, 'storage_provider_id', 'id');
    }
}
