<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportReply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'admin_id',
        'support_ticket_id',
        'message',
    ];

    /**
     * Relationships
     */
    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id', 'id');
    }

    public function supportAttachments()
    {
        return $this->hasMany(SupportAttachment::class, 'support_reply_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }
}
