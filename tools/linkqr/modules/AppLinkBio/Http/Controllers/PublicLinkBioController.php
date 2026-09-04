<?php

namespace Modules\AppLinkBio\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppBrandKit\Support\BrandOperations;
use Modules\AppLinkBio\Models\LinkBioEvent;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppLinkBio\Support\LinkBioTemplateCatalog;
use Modules\AppLinkBioABTesting\Support\LinkBioAbTesting;

class PublicLinkBioController extends Controller
{
    public function __invoke(Request $request, string $slug): View
    {
        $page = LinkBioPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $brandOps = app(BrandOperations::class);
        $brandKitId = (int) data_get($page->settings, 'brand_kit_id');
        $trackingPixelIds = (array) data_get($page->settings, 'tracking_pixel_ids', []);
        $pixels = $brandOps->activePixels($trackingPixelIds, (int) $page->owner_user_id);
        $customDomainId = (int) data_get($page->settings, 'custom_domain_id');
        $effectiveDomain = $brandOps->verifiedDomain($customDomainId, (int) $page->owner_user_id)
            ?: ($customDomainId > 0 ? null : $brandOps->defaultQrDomain((int) $page->owner_user_id));
        $this->recordEvent($request, $page, 'view', metadata: [
            'tracking_pixel_ids' => $pixels->pluck('id')->values()->all(),
            'custom_domain_id' => $effectiveDomain?->id,
        ]);

        return view('applinkbio::public.show', [
            'page' => $page,
            'theme' => LinkBioTemplateCatalog::find((string) $page->template_key),
            'brandKit' => $brandOps->brandKit($brandKitId, (int) $page->owner_user_id),
            'pixelSnippets' => $brandOps->pixelSnippets($pixels),
        ]);
    }

    public function click(Request $request, string $slug, int $block, int $item): RedirectResponse
    {
        $page = LinkBioPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $rawItem = (array) data_get($page->blocks ?? [], $block.'.items.'.$item, []);
        $variant = app(LinkBioAbTesting::class)->variantFor($request, $rawItem);
        $selectedItem = (array) $variant['item'];
        $url = trim((string) data_get($selectedItem, 'url', ''));

        if ($url === '') {
            return redirect()->route('link-bio.public.show', ['slug' => $page->slug]);
        }

        $brandOps = app(BrandOperations::class);
        $utmPreset = $brandOps->utmPreset(data_get($page->settings, 'utm_preset_id'), (int) $page->owner_user_id);
        $destination = $brandOps->applyUtm($url, $utmPreset, [
            'utm_content' => trim((string) data_get($selectedItem, 'utm_content', '')) ?: 'bio_'.$block.'_'.$item,
        ]);

        $this->recordEvent($request, $page, 'click', $block, $item, $destination, [
            'ab_variant' => $variant['key'],
            'item_label' => data_get($selectedItem, 'label'),
        ]);

        return redirect()->away($destination);
    }

    protected function recordEvent(Request $request, LinkBioPage $page, string $type, ?int $block = null, ?int $item = null, ?string $url = null, array $metadata = []): void
    {
        LinkBioEvent::query()->create([
            'link_bio_page_id' => (int) $page->id,
            'type' => $type,
            'block_index' => $block,
            'item_index' => $item,
            'url' => $url,
            'ip_hash' => $request->ip() ? hash('sha256', (string) $request->ip()) : null,
            'user_agent' => Str::limit((string) ($request->userAgent() ?? ''), 255, ''),
            'referer' => Str::limit((string) ($request->headers->get('referer') ?? ''), 1000, ''),
            'metadata' => array_merge([
                'path' => $request->path(),
                'host' => $request->getHost(),
                'brand_kit_id' => data_get($page->settings, 'brand_kit_id'),
                'custom_domain_id' => $metadata['custom_domain_id']
                    ?? (app(BrandOperations::class)->verifiedDomain((int) data_get($page->settings, 'custom_domain_id'), (int) $page->owner_user_id)
                        ?: app(BrandOperations::class)->defaultQrDomain((int) $page->owner_user_id))?->id,
                'utm_preset_id' => app(BrandOperations::class)->utmPreset(data_get($page->settings, 'utm_preset_id'), (int) $page->owner_user_id)?->id,
                'tracking_pixel_ids' => app(BrandOperations::class)
                    ->activePixels((array) data_get($page->settings, 'tracking_pixel_ids', []), (int) $page->owner_user_id)
                    ->pluck('id')
                    ->values()
                    ->all(),
            ], $metadata),
        ]);
    }
}
