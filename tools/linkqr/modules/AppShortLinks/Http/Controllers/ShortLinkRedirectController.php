<?php

namespace Modules\AppShortLinks\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AppShortLinkAccess\Support\ShortLinkAccess;
use Modules\AppShortLinkAnalytics\Support\ShortLinkClickRecorder;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppShortLinks\Models\AppShortLinkAbuseReport;

class ShortLinkRedirectController
{
    public function __construct(
        protected ShortLinkAccess $access,
        protected ShortLinkBranding $branding,
        protected ShortLinkClickRecorder $clickRecorder,
    ) {}

    public function show(Request $request, string $code): RedirectResponse|View
    {
        $shortLink = $this->findShortLink($code);

        if (! $this->access->canRedirect($shortLink)) {
            abort(404);
        }

        if (! $this->access->isUnlocked($request, $shortLink)) {
            return view('appshortlinks::public.password', ['shortLink' => $shortLink]);
        }

        return $this->recordAndRedirect($request, $shortLink);
    }

    public function unlock(Request $request, string $code): RedirectResponse
    {
        $shortLink = $this->findShortLink($code);

        if (! $this->access->canRedirect($shortLink)) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (! $this->access->unlock($request, $shortLink, (string) $validated['password'])) {
            return back()->withErrors(['password' => __('The password is incorrect.')]);
        }

        return redirect()->route('short-links.public.redirect', ['code' => $shortLink->short_code]);
    }

    public function report(string $code): View
    {
        return view('appshortlinks::public.report-abuse', [
            'shortLink' => $this->findShortLink($code),
        ]);
    }

    public function storeReport(Request $request, string $code): RedirectResponse
    {
        $shortLink = $this->findShortLink($code);
        $validated = $request->validate([
            'reason' => ['required', 'in:phishing,malware,spam,impersonation,other'],
            'details' => ['nullable', 'string', 'max:2000'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
        ]);

        AppShortLinkAbuseReport::query()->create([
            'app_short_link_id' => $shortLink->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'reporter_email' => $validated['reporter_email'] ?? null,
            'ip_address' => hash('sha256', (string) $request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'status' => 'open',
        ]);

        if ($shortLink->moderation_status !== 'blocked') {
            $shortLink->forceFill([
                'moderation_status' => 'pending',
                'moderation_note' => __('Abuse report submitted.'),
            ])->save();
        }

        return back()->with('reported', true);
    }

    protected function recordAndRedirect(Request $request, AppShortLink $shortLink): RedirectResponse|View
    {
        $destination = $this->branding->destination($shortLink, $request);

        $this->clickRecorder->record($request, $shortLink);

        $pixelSnippets = $this->branding->pixelSnippets($shortLink);

        if ($pixelSnippets !== []) {
            return view('appqrcodes::public.redirect', [
                'destination' => $destination,
                'pixelSnippets' => $pixelSnippets,
            ]);
        }

        return redirect()->away($destination);
    }

    protected function findShortLink(string $code): AppShortLink
    {
        return AppShortLink::query()
            ->where('short_code', $code)
            ->firstOrFail();
    }
}
