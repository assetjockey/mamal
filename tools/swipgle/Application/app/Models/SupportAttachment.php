<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportAttachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'support_reply_id',
        'attachment',
    ];

    /**
     * Relationships
     */
    public function supportReply()
    {
        return $this->belongsTo(SupportReply::class, 'id');
    }
}
