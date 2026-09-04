<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'ticket_id', 
        'message', 
        'role',
        'attachment'
    ];

    /**
     * Support ticket belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Support message belongs to a ticket
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
