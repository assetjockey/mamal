<?php

namespace Modules\AppShortLinks\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;
use Modules\AppShortLinkQr\Support\ShortLinkQrRenderer;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Edit Short Link')]
class ShortLinkEdit extends Component
{
    public AppShortLink $link;

    public array $editForm = [
        'name' => '',
        'folder' => '',
        'campaign' => '',
        'tags' => '',
        'destination_url' => '',
        'custom_code' => '',
        'custom_domain_id' => '',
        'utm_preset_id' => '',
        'tracking_pixel_ids' => [],
        'status' => 'active',
        'expires_at' => '',
        'click_limit' => '',
        'password' => '',
        'clear_password' => false,
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
        'redirect_rules' => '',
    ];

    public array $abVariants = [];

    public function mount(AppShortLink $shortLink): void
    {
        abort_unless((int) $shortLink->owner_user_id === $this->ownerId(), 404);

        $this->link = $shortLink;
        $this->fillForm();
    }

    public function fetchEditMetadata(): void
    {
        $url = trim((string) data_get($this->editForm, 'destination_url'));

        if ($url === '') {
            return;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->dispatch('app-toast', type: 'warning', message: __('Enter a valid destination URL first.'));

            return;
        }

        $metadata = $this->fetchMetadata($url);

        if ($metadata === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('No metadata found for this URL.'));

            return;
        }

        foreach (['og_title' => 'title', 'og_description' => 'description', 'og_image' => 'image'] as $field => $key) {
            if (filled($metadata[$key] ?? null) && blank(data_get($this->editForm, $field))) {
                data_set($this->editForm, $field, $metadata[$key]);
            }
        }

        if (blank(data_get($this->editForm, 'name')) && filled($metadata['title'] ?? null)) {
            data_set($this->editForm, 'name', $metadata['title']);
        }

        $this->dispatch('app-toast', type: 'success', message: __('Link preview metadata fetched.'));
    }

    public function saveEdit(): void
    {
        $this->syncAbVariantsToRedirectRules();

        $validated = validator($this->editForm, [
            'name' => ['required', 'string', 'max:160'],
            'folder' => ['nullable', 'string', 'max:120'],
            'campaign' => ['nullable', 'string', 'max:160'],
            'tags' => ['nullable', 'string', 'max:500'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'custom_code' => ['required', 'alpha_dash:ascii', 'min:3', 'max:48', Rule::unique('app_short_links', 'short_code')->ignore($this->link->id)],
            'custom_domain_id' => ['nullable', 'integer', Rule::exists('custom_domains', 'id')->where('owner_user_id', $this->ownerId())->where('status', 'verified')],
            'utm_preset_id' => ['nullable', 'integer', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $this->ownerId())],
            'tracking_pixel_ids' => ['array'],
            'tracking_pixel_ids.*' => [Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $this->ownerId())],
            'status' => ['required', 'in:active,paused'],
            'expires_at' => ['nullable', 'date'],
            'click_limit' => ['nullable', 'integer', 'min:1', 'max:999999999'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'clear_password' => ['boolean'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'redirect_rules' => ['nullable', 'json'],
        ])->validate();

        $payload = [
            'name' => $validated['name'],
            'folder' => $validated['folder'] ?: null,
            'campaign' => $validated['campaign'] ?: null,
            'tags' => $this->parseTags((string) ($validated['tags'] ?? '')),
            'destination_url' => $validated['destination_url'],
            'short_code' => str((string) $validated['custom_code'])->lower()->value(),
            'custom_domain_id' => $validated['custom_domain_id'] ?: null,
            'utm_preset_id' => $validated['utm_preset_id'] ?: null,
            'tracking_pixel_ids' => $this->cleanIds($validated['tracking_pixel_ids'] ?? []),
            'status' => $validated['status'],
            'expires_at' => $validated['expires_at'] ?: null,
            'click_limit' => $validated['click_limit'] ?: null,
            'og_title' => $validated['og_title'] ?: null,
            'og_description' => $validated['og_description'] ?: null,
            'og_image' => $validated['og_image'] ?: null,
            'redirect_rules' => filled($validated['redirect_rules'] ?? null) ? json_decode((string) $validated['redirect_rules'], true) : null,
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password_hash'] = Hash::make((string) $validated['password']);
        } elseif ((bool) ($validated['clear_password'] ?? false)) {
            $payload['password_hash'] = null;
        }

        $this->link->update($payload);
        $this->link = $this->link->fresh();
        $this->fillForm();

        $this->dispatch('app-toast', type: 'success', message: __('Short link updated.'));
    }

    public function createAbTest(): void
    {
        $destination = trim((string) data_get($this->editForm, 'destination_url'));
        $variantUrl = $destination !== ''
            ? (str_contains($destination, '?') ? $destination.'&variant=b' : $destination.'?variant=b')
            : 'https://example.com/variant-b';

        $this->abVariants = [
            ['variant_key' => 'A', 'name' => __('Variant A'), 'url' => $destination ?: 'https://example.com/a', 'weight' => 50, 'status' => 'active', 'winner' => false],
            ['variant_key' => 'B', 'name' => __('Variant B'), 'url' => $variantUrl, 'weight' => 50, 'status' => 'active', 'winner' => false],
        ];

        $this->syncAbVariantsToRedirectRules();
    }

    public function applyEditRedirectTemplate(string $type): void
    {
        if ($type === 'ab') {
            $this->createAbTest();

            return;
        }

        $destination = trim((string) data_get($this->editForm, 'destination_url')) ?: 'https://example.com/a';
        $variantUrl = str_contains($destination, '?') ? $destination.'&variant=b' : $destination.'?variant=b';

        $rules = match ($type) {
            'geo' => [
                ['url' => $destination, 'countries' => ['US', 'CA']],
                ['url' => $variantUrl, 'countries' => ['VN']],
            ],
            'device' => [
                ['url' => $destination, 'devices' => ['desktop']],
                ['url' => $variantUrl, 'devices' => ['mobile']],
            ],
            'time' => [
                ['url' => $destination, 'starts_at' => now()->startOfDay()->toIso8601String(), 'ends_at' => now()->endOfDay()->toIso8601String()],
                ['url' => $variantUrl, 'starts_at' => now()->addDay()->startOfDay()->toIso8601String()],
            ],
            default => [],
        };

        $this->abVariants = [];
        $this->editForm['redirect_rules'] = $rules ? json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
    }

    public function addAbVariant(): void
    {
        $keys = range('A', 'Z');
        $used = collect($this->abVariants)->pluck('variant_key')->map(fn ($key) => strtoupper((string) $key))->all();
        $key = collect($keys)->first(fn (string $candidate): bool => ! in_array($candidate, $used, true)) ?: chr(65 + count($this->abVariants));

        $this->abVariants[] = [
            'variant_key' => $key,
            'name' => __('Variant :key', ['key' => $key]),
            'url' => trim((string) data_get($this->editForm, 'destination_url')) ?: 'https://example.com/'.$key,
            'weight' => 10,
            'status' => 'active',
            'winner' => false,
        ];

        $this->normalizeAbWeights();
        $this->syncAbVariantsToRedirectRules();
    }

    public function removeAbVariant(int $index): void
    {
        unset($this->abVariants[$index]);
        $this->abVariants = array_values($this->abVariants);
        $this->syncAbVariantsToRedirectRules();
    }

    public function toggleAbVariant(int $index): void
    {
        if (! isset($this->abVariants[$index])) {
            return;
        }

        $this->abVariants[$index]['status'] = ($this->abVariants[$index]['status'] ?? 'active') === 'paused' ? 'active' : 'paused';
        $this->abVariants[$index]['winner'] = $this->abVariants[$index]['status'] === 'paused' ? false : (bool) ($this->abVariants[$index]['winner'] ?? false);
        $this->syncAbVariantsToRedirectRules();
    }

    public function markAbWinner(int $index): void
    {
        foreach ($this->abVariants as $variantIndex => $variant) {
            $this->abVariants[$variantIndex]['winner'] = $variantIndex === $index;
            if ($variantIndex === $index) {
                $this->abVariants[$variantIndex]['status'] = 'active';
            }
        }

        $this->syncAbVariantsToRedirectRules();
    }

    public function clearAbWinner(): void
    {
        foreach ($this->abVariants as $index => $variant) {
            $this->abVariants[$index]['winner'] = false;
        }

        $this->syncAbVariantsToRedirectRules();
    }

    public function autoSelectAbWinner(string $metric = 'clicks'): void
    {
        $stats = $this->abVariantStats();
        $winner = collect($stats)
            ->filter(fn (array $row): bool => ($row['status'] ?? 'active') !== 'paused')
            ->sortByDesc(fn (array $row): int|float => $metric === 'ctr' ? (float) $row['ctr_index'] : (int) $row['clicks'])
            ->first();

        if (! $winner || (int) ($winner['clicks'] ?? 0) === 0) {
            $this->dispatch('app-toast', type: 'warning', message: __('No variant click data yet.'));

            return;
        }

        $index = collect($this->abVariants)->search(fn (array $variant): bool => (string) ($variant['variant_key'] ?? '') === (string) $winner['variant_key']);

        if ($index !== false) {
            $this->markAbWinner((int) $index);
            $this->dispatch('app-toast', type: 'success', message: __('A/B winner selected.'));
        }
    }

    public function cancelEdit()
    {
        return $this->redirectRoute('portal.short-links.index', navigate: true);
    }

    public function render(): View
    {
        $branding = app(ShortLinkBranding::class);

        return view('appshortlinks::edit', [
            'domains' => $branding->domains($this->ownerId()),
            'utmPresets' => $branding->utmPresets($this->ownerId()),
            'pixels' => $branding->pixels($this->ownerId()),
            'brandVisual' => $branding->visualSettings($this->ownerId()),
            'qrSvg' => app(ShortLinkQrRenderer::class)->svg($this->link),
            'downloadName' => str($this->link->name ?: 'short-link')->slug('-')->value() ?: 'short-link',
            'abStats' => $this->abVariantStats(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Edit Short Link')]);
    }

    protected function ownerId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function fillForm(): void
    {
        $this->editForm = [
            'name' => (string) $this->link->name,
            'folder' => (string) $this->link->folder,
            'campaign' => (string) $this->link->campaign,
            'tags' => implode(', ', (array) $this->link->tags),
            'destination_url' => (string) $this->link->destination_url,
            'custom_code' => (string) $this->link->short_code,
            'custom_domain_id' => (string) ($this->link->custom_domain_id ?: ''),
            'utm_preset_id' => (string) ($this->link->utm_preset_id ?: ''),
            'tracking_pixel_ids' => (array) ($this->link->tracking_pixel_ids ?: []),
            'status' => (string) $this->link->status,
            'expires_at' => $this->link->expires_at?->format('Y-m-d\TH:i') ?: '',
            'click_limit' => $this->link->click_limit ? (string) $this->link->click_limit : '',
            'password' => '',
            'clear_password' => false,
            'og_title' => (string) $this->link->og_title,
            'og_description' => (string) $this->link->og_description,
            'og_image' => (string) $this->link->og_image,
            'redirect_rules' => $this->link->redirect_rules ? json_encode($this->link->redirect_rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '',
        ];

        $this->abVariants = $this->variantsFromRules((array) $this->link->redirect_rules);
    }

    protected function syncAbVariantsToRedirectRules(): void
    {
        if ($this->abVariants === []) {
            return;
        }

        $rules = collect($this->abVariants)
            ->map(function (array $variant): array {
                return [
                    'type' => 'ab',
                    'variant_key' => strtoupper(substr((string) ($variant['variant_key'] ?? 'A'), 0, 3)),
                    'name' => trim((string) ($variant['name'] ?? '')),
                    'url' => trim((string) ($variant['url'] ?? '')),
                    'weight' => max(0, min(100, (int) ($variant['weight'] ?? 0))),
                    'status' => ($variant['status'] ?? 'active') === 'paused' ? 'paused' : 'active',
                    'winner' => (bool) ($variant['winner'] ?? false),
                ];
            })
            ->filter(fn (array $variant): bool => filled($variant['url']))
            ->values()
            ->all();

        $this->editForm['redirect_rules'] = $rules ? json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
    }

    protected function variantsFromRules(array $rules): array
    {
        return collect($rules)
            ->filter(fn ($rule): bool => is_array($rule) && filled($rule['url'] ?? null) && ((string) ($rule['type'] ?? '') === 'ab' || array_key_exists('weight', $rule)))
            ->values()
            ->map(function (array $rule, int $index): array {
                $key = (string) ($rule['variant_key'] ?? chr(65 + $index));

                return [
                    'variant_key' => strtoupper(substr($key, 0, 3)),
                    'name' => (string) ($rule['name'] ?? __('Variant :key', ['key' => strtoupper(substr($key, 0, 3))])),
                    'url' => (string) $rule['url'],
                    'weight' => (int) ($rule['weight'] ?? 1),
                    'status' => (string) ($rule['status'] ?? 'active'),
                    'winner' => (bool) ($rule['winner'] ?? false),
                ];
            })
            ->all();
    }

    protected function normalizeAbWeights(): void
    {
        $count = count($this->abVariants);

        if ($count === 0) {
            return;
        }

        $weight = (int) floor(100 / $count);
        $remainder = 100 - ($weight * $count);

        foreach ($this->abVariants as $index => $variant) {
            $this->abVariants[$index]['weight'] = $weight + ($index === 0 ? $remainder : 0);
        }
    }

    protected function abVariantStats(): array
    {
        $variants = collect($this->abVariants);

        if ($variants->isEmpty()) {
            return [];
        }

        $clicks = AppShortLinkClick::query()
            ->where('app_short_link_id', $this->link->id)
            ->get(['metadata']);
        $total = max(1, $clicks->count());
        $counts = $clicks
            ->map(fn (AppShortLinkClick $click): string => (string) data_get($click->metadata, 'variant.key', ''))
            ->filter()
            ->countBy();
        $totalWeight = max(1, (int) $variants->sum(fn (array $variant): int => max(0, (int) ($variant['weight'] ?? 0))));

        return $variants->map(function (array $variant) use ($counts, $total, $totalWeight): array {
            $key = (string) ($variant['variant_key'] ?? '');
            $clicks = (int) ($counts[$key] ?? 0);
            $weight = max(0, (int) ($variant['weight'] ?? 0));
            $expected = round(($weight / $totalWeight) * 100, 1);
            $observed = round(($clicks / $total) * 100, 1);

            return $variant + [
                'clicks' => $clicks,
                'split' => $observed,
                'expected_split' => $expected,
                'ctr_index' => $expected > 0 ? round(($observed / $expected) * 100, 1) : 0,
            ];
        })->values()->all();
    }

    protected function fetchMetadata(string $url): array
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(4)
                ->withHeaders(['User-Agent' => 'LinkQRPro metadata crawler'])
                ->get($url);
        } catch (\Throwable) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $html = $this->normalizeFetchedHtml((string) $response->body(), (string) $response->header('Content-Type'));

        if ($html === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        $meta = function (string $selector) use ($xpath): string {
            $node = $xpath->query($selector)->item(0);

            return trim((string) $node?->attributes?->getNamedItem('content')?->nodeValue);
        };

        $titleNode = $xpath->query('//title')->item(0);
        $title = $meta('//meta[@property="og:title"]')
            ?: $meta('//meta[@name="twitter:title"]')
            ?: trim((string) $titleNode?->textContent);
        $description = $meta('//meta[@property="og:description"]')
            ?: $meta('//meta[@name="description"]')
            ?: $meta('//meta[@name="twitter:description"]');
        $image = $meta('//meta[@property="og:image"]')
            ?: $meta('//meta[@name="twitter:image"]');

        if ($image && ! str_starts_with($image, 'http')) {
            if (str_starts_with($image, '//')) {
                $image = (string) parse_url($url, PHP_URL_SCHEME).':'.$image;
            } else {
                $image = rtrim((string) parse_url($url, PHP_URL_SCHEME), ':').'://'.parse_url($url, PHP_URL_HOST).'/'.ltrim($image, '/');
            }
        }

        return array_filter([
            'title' => str($title)->limit(160, '')->value(),
            'description' => str($description)->limit(500, '')->value(),
            'image' => filter_var($image, FILTER_VALIDATE_URL) ? $image : null,
        ]);
    }

    protected function normalizeFetchedHtml(string $html, string $contentType = ''): string
    {
        if ($html === '') {
            return '';
        }

        $encoding = null;

        if (preg_match('/charset\s*=\s*["\']?([a-zA-Z0-9._-]+)/i', $contentType, $match)) {
            $encoding = strtoupper($match[1]);
        }

        if (! $encoding && preg_match('/<meta[^>]+charset\s*=\s*["\']?\s*([a-zA-Z0-9._-]+)/i', $html, $match)) {
            $encoding = strtoupper($match[1]);
        }

        if (! $encoding && preg_match('/<meta[^>]+content\s*=\s*["\'][^"\']*charset\s*=\s*([a-zA-Z0-9._-]+)/i', $html, $match)) {
            $encoding = strtoupper($match[1]);
        }

        $aliases = [
            'GB2312' => 'GB18030',
            'GBK' => 'GB18030',
            'CP936' => 'GB18030',
            'BIG5' => 'BIG5',
            'BIG-5' => 'BIG5',
            'CP950' => 'BIG5',
        ];
        $encoding = $aliases[$encoding] ?? $encoding;

        if (! $encoding) {
            $encoding = mb_detect_encoding($html, ['UTF-8', 'GB18030', 'BIG5', 'CP950', 'Shift_JIS', 'EUC-JP', 'ISO-8859-1'], true) ?: null;
        }

        if ($encoding && strtoupper($encoding) !== 'UTF-8') {
            $converted = @mb_convert_encoding($html, 'UTF-8', $encoding);

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        if (! mb_check_encoding($html, 'UTF-8')) {
            $converted = @mb_convert_encoding($html, 'UTF-8', 'GB18030');

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        return $html;
    }

    protected function cleanIds(array $ids): array
    {
        return collect($ids)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
    }

    protected function parseTags(string $tags): array
    {
        return str($tags)->explode(',')
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
