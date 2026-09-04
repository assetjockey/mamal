<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'ip',
        'storage_provider_id',
        'unique_id',
        'link',
        'sender_email',
        'sender_name',
        'emails',
        'subject',
        'message',
        'password',
        'download_notify',
        'expiry_notify',
        'expiry_at',
        'type',
        'status',
        'cancellation_reason',
        'downloaded_at',
        'files_deleted_at',
        'read_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'emails' => 'object',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transferFiles()
    {
        return $this->hasMany(TransferFile::class, 'transfer_id', 'id');
    }

    public function storageProvider()
    {
        return $this->belongsTo(StorageProvider::class, 'storage_provider_id', 'id');
    }
}
