<?php

namespace App\Livewire\Admin\Accounts\List;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AccountCreate extends Component
{
    // Auth
    public string $email                 = '';
    public string $password              = '';
    public string $password_confirmation = '';

    // Personal
    public string $name         = '';
    public string $company      = '';
    public string $phone_number = '';
    public string $website      = '';

    // Address
    public string $address     = '';
    public string $city        = '';
    public string $postal_code = '';
    public string $country     = '';

    // Account
    public string $group   = 'user';
    public string $status  = 'active';
    public int    $credits = 0;

    protected function rules(): array
    {
        return [
            'email'                 => 'required|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'name'                  => 'required|string|max:255',
            'company'               => 'nullable|string|max:255',
            'phone_number'          => 'nullable|string|max:50',
            'website'               => 'nullable|url|max:255',
            'address'               => 'nullable|string|max:255',
            'city'                  => 'nullable|string|max:100',
            'postal_code'           => 'nullable|string|max:20',
            'country'               => 'nullable|string|max:100',
            'group'                 => 'required|in:user,admin,subscriber',
            'status'                => 'required|in:active,inactive,suspended,pending',
            'credits'               => 'required|integer|min:0',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $user_id = $this->generateUserId();

        $user = User::create([
            'user_id'      => $user_id,
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'company'      => $data['company'],
            'phone_number' => $data['phone_number'],
            'website'      => $data['website'],
            'address'      => $data['address'],
            'city'         => $data['city'],
            'postal_code'  => $data['postal_code'],
            'country'      => $data['country'],
            'avatar'       => 'img/users/avatar.jpg',
            'referral_id'  => strtoupper(Str::random(15)),
        ]);

        $user->forceFill([
            'group'   => $data['group'],
            'status'  => $data['status'],
            'credits' => $data['credits'],
        ])->save();

        // Mirror the registration flow (CreateNewUser): assign the Spatie role
        // so model_has_roles is populated. Without this, manually created users
        // have a `group` but no actual role/permissions.
        $user->assignRole($data['group']);

        toaster()->success(__('User created successfully'));

        $this->redirect(route('admin.accounts.list'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.accounts.users.create')
            ->title(__('Create User') . ' | ' . config('app.name'));
    }

    private function generateUserId()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        // Generate first segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $code .= '-';
        
        // Generate second segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $code .= '-';
        
        // Generate third segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return strtolower($code);
    }
}
