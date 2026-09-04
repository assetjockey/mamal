<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMessage;
use App\Models\EmailSetting;
use App\Models\GeneralSetting;
use App\Models\PageSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * PageController
 *
 * Serves the public, static marketing pages (home, contact, privacy, terms).
 * Each method is intentionally thin — these are content pages with no
 * business logic. If a page later needs form handling, SEO metadata,
 * feature flags, or A/B variants, extend the matching method here rather
 * than introducing a new controller per page.
 */
class PageController extends Controller
{
    /**
     * Landing / marketing home page.
     */
    public function home(): View
    {
        return view('welcome');
    }

    /**
     * Public contact page.
     */
    public function contact(): View
    {
        return view('contact');
    }

    /**
     * Handle a submission from the public contact form.
     *
     * Validates the input, then dispatches a {@see ContactFormMessage} through
     * the admin-configured SMTP account. The credentials in `email_settings`
     * are already applied to the runtime mail config on every boot by
     * AppServiceProvider::configureMailFromSettings(), so no per-send transport
     * wiring is needed here — this uses exactly the same connection as the
     * "Send test email" button in Admin → Backend → SMTP.
     *
     * The message is delivered to the site's public contact address (the
     * `contact_email` on general settings, falling back to the SMTP sender so a
     * misconfigured contact address never silently drops enquiries).
     */
    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255'],
            'company'    => ['nullable', 'string', 'max:150'],
            'subject'    => ['required', 'string', 'in:general,demo,enterprise,support,partnership'],
            'message'    => ['required', 'string', 'max:5000'],
        ]);

        $recipient = $this->contactRecipient();

        if (blank($recipient)) {
            return back()
                ->withInput()
                ->with('contact_error', __('Sorry, the contact form is not available right now. Please email us directly.'));
        }

        try {
            Mail::to($recipient)->send(new ContactFormMessage(
                firstName: $validated['first_name'],
                lastName: $validated['last_name'],
                email: $validated['email'],
                company: $validated['company'] ?? null,
                subjectKey: $validated['subject'],
                messageBody: $validated['message'],
            ));
        } catch (\Throwable $e) {
            Log::error('Contact form email failed to send', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('contact_error', __('We could not send your message right now. Please try again later.'));
        }

        return back()->with('contact_success', __("Thanks for reaching out! We'll get back to you shortly."));
    }

    /**
     * Resolve the address contact-form enquiries are delivered to.
     *
     * Prefers the public `contact_email` on general settings; falls back to the
     * configured SMTP "from" address so enquiries are never silently dropped
     * when the contact address hasn't been filled in. Returns null when neither
     * is set (fresh install / unconfigured mail).
     */
    private function contactRecipient(): ?string
    {
        // `contact_email` is not part of the base `general_settings` schema, so
        // guard the column existence before reading it — some installs add it,
        // others don't. Reading a missing column via query()->value() would
        // throw, whereas attribute access on a hydrated model is null-safe.
        if (Schema::hasTable('general_settings') && Schema::hasColumn('general_settings', 'contact_email')) {
            $contactEmail = GeneralSetting::query()->value('contact_email');

            if (filled($contactEmail)) {
                return $contactEmail;
            }
        }

        $fromAddress = Schema::hasTable('email_settings')
            ? EmailSetting::query()->value('from_address')
            : null;

        return filled($fromAddress) ? $fromAddress : null;
    }

    /**
     * Privacy policy. Renders admin-managed content when set, otherwise the
     * built-in default copy baked into the view.
     */
    public function privacy(): View
    {
        return view('privacy', [
            'pageSettings' => $this->pageSettings(),
        ]);
    }

    /**
     * Terms of service. Renders admin-managed content when set, otherwise the
     * built-in default copy baked into the view.
     */
    public function terms(): View
    {
        return view('terms', [
            'pageSettings' => $this->pageSettings(),
        ]);
    }

    /**
     * The single page-settings row, or null on fresh installs where the
     * table hasn't been migrated yet.
     */
    private function pageSettings(): ?PageSetting
    {
        return Schema::hasTable('page_settings')
            ? PageSetting::query()->first()
            : null;
    }
}
