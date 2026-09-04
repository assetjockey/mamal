<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'ticket_id', 
        'category', 
        'priority', 
        'subject', 
        'status',
        'resolved_on'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'resolved_on' => 'datetime',
        ];
    }

    /**
     * Support ticket belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Support ticket has many messages
     */
    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id', 'ticket_id');
    }
}
