<?php

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }

    /**
     * Render the component using the active theme's view finder.
     *
     * See Profile::render() for the rationale behind explicitly resolving
     * the view by name instead of relying on Livewire's default fallback.
     */
    public function render(): View
    {
        return view('livewire.settings.delete-user-form');
    }
}
