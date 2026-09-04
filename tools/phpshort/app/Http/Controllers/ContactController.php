<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMailRequest;
use App\Mail\ContactMail;
use App\Mail\ContactMail2;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the Contact page.
     */
    public function index(): View
    {
        return view('contact.index');
    }

    /**
     * Send the Contact email.
     */
    public function send(ContactMailRequest $request): RedirectResponse
    {
        if (!config('settings.contact_form')) {
            abort(404);
        }

        try {
            Mail::to(config('settings.contact_email'))->send(new ContactMail($request->validated()));
        } catch (Exception $e) {
            return redirect()->route('contact')->with('error', $e->getMessage());
        }

        return redirect()->route('contact')->with('success', __('Thank you!') . ' ' . __('We\'ve received your message.'));
    }
}
