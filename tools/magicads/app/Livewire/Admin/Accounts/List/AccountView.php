<?php

namespace App\Livewire\Admin\Accounts\List;

use Livewire\Component;
use App\Models\User;

class AccountView extends Component
{
    public User $user;

    public function mount(string $uid): void
    {
        $this->user = User::with([
            'activeSubscription.plan',
            'orders' => fn ($q) => $q->latest()->limit(5),
            'supportTickets' => fn ($q) => $q->latest()->limit(5),
        ])->where('user_id', $uid)->firstOrFail();
    }

    public function deleteUser(): void
    {
        $this->user->delete();

        toaster()->success(__('User deleted successfully'));
        $this->redirect(route('admin.accounts.list'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.accounts.users.view')
            ->title($this->user->name . ' | ' . config('app.name'));
    }
}
