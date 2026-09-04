<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    use VerifiesEmails;

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

        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
