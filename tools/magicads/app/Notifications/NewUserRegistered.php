<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New User Registered',
            'message' => "{$this->user->name} ({$this->user->email}) just registered.",
            'user_id' => $this->user->user_id,
            'url'     => route('admin.accounts.view', $this->user->user_id),
        ];
    }
}
