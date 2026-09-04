<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    /**
     * The user service instance.
     */
    private UserService $userService;

    /**
     * Create a new controller instance.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Update the Locale preference.
     */
    public function updateLocale(Request $request): RedirectResponse
    {
        if (array_key_exists($request->input('locale'), config('app.locales'))) {
            if (Auth::check()) {
                $this->userService->update($request->user(), ['locale' => $request->input('locale')]);
            }
        }

        return redirect()->back()->withCookie('locale', $request->input('locale'), (60 * 24 * 365 * 10));
    }
}
