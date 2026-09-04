<?php

namespace Modules\AppLinkBio\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppLinkBio\Models\LinkBioEvent;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppLinkBio\Models\LinkBioShortLink;

class PublicShortLinkController extends Controller
{
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $shortLink = LinkBioShortLink::query()
            ->with('page')
            ->where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $page = $shortLink->page;
        abort_unless($page && $page->is_published && (! LinkBioPage::hasStatusColumn() || $page->status === 'published'), 404);

        LinkBioEvent::query()->create([
            'link_bio_page_id' => (int) $page->id,
            'type' => 'view',
            'source' => 'short_link',
            'url' => $shortLink->target_url,
            'ip_hash' => $request->ip() ? hash('sha256', (string) $request->ip()) : null,
            'user_agent' => Str::limit((string) ($request->userAgent() ?? ''), 255, ''),
            'referer' => Str::limit((string) ($request->headers->get('referer') ?? ''), 1000, ''),
            'metadata' => [
                'short_link_id' => (int) $shortLink->id,
                'code' => $shortLink->code,
                'host' => $request->getHost(),
            ],
        ]);

        return redirect()->route('link-bio.public.show', ['slug' => $page->slug]);
    }
}
