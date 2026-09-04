<?php

namespace App\Livewire\Admin\Notifications\System;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SystemNotification extends Component
{
    use WithPagination;

    public function markAsRead(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function delete(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.notifications.system.system_notification', [
            'notifications' => Auth::user()->notifications()->paginate(20),
        ]);
    }
}
