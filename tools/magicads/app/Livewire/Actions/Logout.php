<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        // Send the user back to the localized home so the URL keeps the active locale.
        return redirect(LaravelLocalization::localizeUrl('/'));
    }
}
