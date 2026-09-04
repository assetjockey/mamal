<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * The path the user should be redirected to.
     */
    protected string $redirectTo;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->redirectTo = route('dashboard');

        $this->middleware('guest');
    }
}
