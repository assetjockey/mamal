<?php

namespace App\Livewire\Admin\Accounts\List;

use Livewire\Component;
use App\Models\User;

class AccountEdit extends Component
{
    public User $user;

    // Personal
    public string $name        = '';
    public string $company     = '';
    public string $phone_number = '';
    public string $website     = '';

    // Address
    public string $address     = '';
    public string $city        = '';
    public string $postal_code = '';
    public string $country     = '';

    // Account
    public string $group       = '';
    public string $status      = '';
    public int    $credits     = 0;

    public function mount(string $uid): void
    {
        $this->user = User::where('user_id', $uid)->firstOrFail();

        $this->fill([
            'name'         => $this->user->name         ?? '',
            'company'      => $this->user->company      ?? '',
            'phone_number' => $this->user->phone_number ?? '',
            'website'      => $this->user->website      ?? '',
            'address'      => $this->user->address      ?? '',
            'city'         => $this->user->city         ?? '',
            'postal_code'  => $this->user->postal_code  ?? '',
            'country'      => $this->user->country      ?? '',
            'group'        => $this->user->group        ?? 'user',
            'status'       => $this->user->status       ?? 'active',
            'credits'      => $this->user->credits      ?? 0,
        ]);
    }

    protected function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'company'      => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'website'      => 'nullable|url|max:255',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'group'        => 'required|in:user,admin,subscriber',
            'status'       => 'required|in:active,inactive,suspended,pending',
            'credits'      => 'required|integer|min:0',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->user->forceFill([
            'name'         => $data['name'],
            'company'      => $data['company'],
            'phone_number' => $data['phone_number'],
            'website'      => $data['website'],
            'address'      => $data['address'],
            'city'         => $data['city'],
            'postal_code'  => $data['postal_code'],
            'country'      => $data['country'],
            'group'        => $data['group'],
            'status'       => $data['status'],
            'credits'      => $data['credits'],
        ])->save();

        toaster()->success(__('User updated successfully'));

        $this->redirect(route('admin.accounts.view', $this->user->user_id), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.accounts.users.edit')
            ->title(__('Edit') . ' ' . $this->user->name . ' | ' . config('app.name'));
    }
}
