<div class="px-4 pb-8 pt-5 sm:px-5 xl:px-6">
    @php
        $brandQrForeground = $brandVisual['primary_color'] ?? '#2563eb';
        $brandQrWatermark = ! empty($brandVisual['logo_url']) ? 5 : 0;
    @endphp
    <div class="mx-auto max-w-[96rem] space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('portal.short-links.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                <i class="fa-light fa-arrow-left"></i>
                {{ __('Back to links') }}
            </a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('portal.short-links.analytics', ['link' => $link->id]) }}" class="inline-flex h-10 items-center gap-2 rounded-[0.75rem] border bg-white px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                    <i class="fa-light fa-chart-line"></i>
                    {{ __('Analytics') }}
                </a>
                <button type="button" class="inline-flex h-10 items-center gap-2 rounded-[0.75rem] border bg-white px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-data x-on:click="navigator.clipboard.writeText(@js($link->shortUrl())); window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'success', message: @js(__('Short link copied.')) } }))">
                    <i class="fa-light fa-copy"></i>
                    {{ __('Copy link') }}
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.75rem] border bg-white text-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="saveEdit" title="{{ __('Save') }}">
                    <i class="fa-light fa-floppy-disk"></i>
                </button>
            </div>
        </div>

        <section class="rounded-[1.2rem] border bg-white p-5 shadow-[0_26px_80px_-62px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-[0.9rem]" style="background-color: {{ $brandVisual['primary_color'] ?? 'var(--theme-accent)' }}; color: white;">
                            @if (! empty($brandVisual['logo_url']))
                                <img src="{{ $brandVisual['logo_url'] }}" alt="" class="h-full w-full object-cover">
                            @else
                                <i class="fa-light fa-layer-group"></i>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="break-all text-xl font-semibold sm:text-2xl" style="color: var(--theme-header-text-color);">{{ $link->shortUrl() }}</h1>
                                <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.6rem] border" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-data x-on:click="navigator.clipboard.writeText(@js($link->shortUrl()))" title="{{ __('Copy') }}">
                                    <i class="fa-light fa-copy"></i>
                                </button>
                                @if ($link->password_hash)
                                    <i class="fa-light fa-lock text-lg" style="color: var(--theme-header-text-color);" title="{{ __('Password protected') }}"></i>
                                @endif
                                @if ($link->redirect_rules)
                                    <x-ui.badge variant="primary">{{ __('Rules') }}</x-ui.badge>
                                @endif
                            </div>
                            <div class="mt-4 flex min-w-0 items-center gap-3">
                                <i class="fa-light fa-globe" style="color: var(--theme-header-text-color);"></i>
                                <p class="truncate text-sm" style="color: var(--theme-header-text-color);">{{ $link->destination_url }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm" style="color: var(--theme-muted-text-color);">
                    <span class="inline-flex items-center gap-2"><i class="fa-light fa-shield-check"></i>{{ $link->name }}</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-light fa-calendar"></i>{{ $link->created_at?->format('M j, Y') }}</span>
                    <x-ui.badge :variant="$link->status === 'active' && $link->moderation_status !== 'blocked' ? 'success' : 'neutral'">
                        {{ $link->moderation_status === 'blocked' ? __('Blocked') : str($link->status)->title() }}
                    </x-ui.badge>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <main class="space-y-5">
                <section class="rounded-[1.15rem] border bg-white p-5 shadow-[0_24px_70px_-58px_rgba(15,23,42,0.45)] lg:p-6" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                    <div class="mb-5 flex flex-col gap-2 border-b pb-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Short link settings') }}</p>
                            <h2 class="mt-1 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Edit short link') }}</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button type="button" variant="outline" wire:click="cancelEdit">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="button" wire:click="saveEdit">
                                <i class="fa-light fa-floppy-disk"></i>
                                {{ __('Save changes') }}
                            </x-ui.button>
                        </div>
                    </div>

                    @include('appshortlinks::partials.edit-form', ['link' => $link])
                </section>
            </main>

            <aside class="space-y-5 xl:sticky xl:top-5 xl:self-start">
                <section class="rounded-[1.15rem] border bg-white p-4 shadow-[0_20px_60px_-54px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Link preview') }}</h3>
                            <x-ui.tooltip :text="__('Shows the title, description, and image used when this short link is shared. Fetch metadata to update it from the destination page.')" placement="top">
                                <x-slot:trigger>
                                    <button type="button" class="inline-flex h-5 w-5 items-center justify-center rounded-full text-sm" style="color: var(--theme-muted-text-color);" aria-label="{{ __('Link preview info') }}">
                                        <i class="fa-light fa-circle-info"></i>
                                    </button>
                                </x-slot:trigger>
                            </x-ui.tooltip>
                        </div>
                        <x-ui.dialog :title="__('Edit link preview')" :description="__('Update the title, description, and image shown when this short link is shared.')" width="lg" dismissible>
                            <x-slot:trigger>
                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Edit preview') }}">
                                    <i class="fa-light fa-pen"></i>
                                </button>
                            </x-slot:trigger>

                            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
                                <div class="space-y-4">
                                    <x-ui.input wire:model.live.debounce.250ms="editForm.og_title" :label="__('Preview title')" :placeholder="__('Title shown on social previews')" :error="$errors->first('og_title')" />
                                    <x-ui.textarea wire:model.live.debounce.250ms="editForm.og_description" :label="__('Preview description')" rows="4" :placeholder="__('Short description shown under the title')" :error="$errors->first('og_description')" />
                                    <x-ui.image-picker
                                        wire:model.live="editForm.og_image"
                                        :value="$editForm['og_image'] ?? ''"
                                        :label="__('Preview image')"
                                        :error="$errors->first('og_image')"
                                        :help="__('Choose an image from your file library, upload a new image, or import from an image URL.')"
                                        :button-label="__('Choose image')"
                                        context="portal"
                                        layout="compact"
                                    />
                                    <div class="flex flex-wrap gap-2">
                                        <x-ui.button type="button" variant="outline" size="sm" wire:click="fetchEditMetadata" wire:loading.attr="disabled" wire:target="fetchEditMetadata">
                                            <i class="fa-light fa-wand-magic-sparkles" wire:loading.remove wire:target="fetchEditMetadata"></i>
                                            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="fetchEditMetadata"></i>
                                            <span wire:loading.remove wire:target="fetchEditMetadata">{{ __('Fetch metadata') }}</span>
                                            <span wire:loading wire:target="fetchEditMetadata">{{ __('Fetching...') }}</span>
                                        </x-ui.button>
                                        <x-ui.button type="button" variant="outline" size="sm" wire:click="$set('editForm.og_image', '')">
                                            <i class="fa-light fa-image-slash"></i>{{ __('Clear image') }}
                                        </x-ui.button>
                                    </div>
                                </div>

                                <aside class="self-start rounded-[1rem] border bg-white p-3 shadow-sm" style="border-color: var(--theme-border-color);">
                                    <div class="overflow-hidden rounded-[0.85rem]" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                                        @if (filled($editForm['og_image']))
                                            <img src="{{ $editForm['og_image'] }}" alt="" class="aspect-[1.91/1] w-full object-cover">
                                        @else
                                            <div class="flex aspect-[1.91/1] w-full items-center justify-center">
                                                <div class="rounded-[1rem] border-2 border-dashed border-sky-300 bg-white p-6 text-sky-400">
                                                    <i class="fa-light fa-plus text-2xl"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mt-4 line-clamp-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $editForm['og_title'] ?: __('No title') }}</p>
                                    <p class="mt-2 line-clamp-2 text-sm" style="color: var(--theme-muted-text-color);">{{ $editForm['og_description'] ?: __('No description') }}</p>
                                </aside>
                            </div>

                            <x-slot:footer>
                                <div class="flex justify-end gap-2">
                                    <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                    <x-ui.button type="button" wire:click="saveEdit" x-on:click="open = false">{{ __('Save preview') }}</x-ui.button>
                                </div>
                            </x-slot:footer>
                        </x-ui.dialog>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-[0.9rem]" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                        @if ($link->og_image)
                            <img src="{{ $link->og_image }}" alt="" class="h-48 w-full object-cover">
                        @else
                            <div class="flex h-48 items-center justify-center">
                                <div class="inline-flex h-24 w-24 items-center justify-center rounded-[1rem] border-2 border-dashed text-3xl" style="border-color: rgba(var(--theme-accent-rgb),0.55); color: var(--theme-accent);">
                                    <i class="fa-light fa-plus"></i>
                                </div>
                            </div>
                        @endif
                    </div>
                    <p class="mt-4 line-clamp-2 font-semibold" style="color: var(--theme-header-text-color);">{{ $link->og_title ?: __('No title') }}</p>
                    <p class="mt-2 line-clamp-3 text-sm" style="color: var(--theme-muted-text-color);">{{ $link->og_description ?: __('No description') }}</p>
                </section>

                <section class="rounded-[1.15rem] border bg-white p-4 shadow-[0_20px_60px_-54px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR Code') }}</h3>
                            <x-ui.tooltip :text="__('Download or customize this QR code for print, packaging, campaigns, or quick sharing.')" placement="top" max-width="16rem">
                                <x-slot:trigger>
                                    <button type="button" class="inline-flex h-5 w-5 items-center justify-center rounded-full text-sm" style="color: var(--theme-muted-text-color);" aria-label="{{ __('QR code info') }}">
                                        <i class="fa-light fa-circle-info"></i>
                                    </button>
                                </x-slot:trigger>
                            </x-ui.tooltip>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" x-data x-on:click="downloadShortLinkEditQr(@js($downloadName))" title="{{ __('Download QR') }}">
                                <i class="fa-light fa-download"></i>
                            </button>
                            <x-ui.dialog :title="__('Customize and download your QR code')" width="3xl" dismissible>
                                <x-slot:trigger>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Design QR code') }}">
                                        <i class="fa-light fa-pen"></i>
                                    </button>
                                </x-slot:trigger>

                                <div
                                    class="grid max-h-[76vh] gap-6 overflow-y-auto pr-1 lg:grid-cols-[minmax(0,1fr)_18rem]"
                                    x-data="{
                                        bg: '#ffffff',
                                        fg: @js($brandQrForeground),
                                        margin: 0,
                                        watermark: @js($brandQrWatermark),
                                        size: '512',
                                        format: 'png',
                                        downloadQr() {
                                            window.downloadCustomizedShortLinkEditQr({
                                                filename: @js($downloadName),
                                                bg: this.bg,
                                                fg: this.fg,
                                                preserveColors: this.fg === @js($brandQrForeground),
                                                margin: this.margin,
                                                watermark: this.watermark,
                                                size: this.size,
                                                format: this.format,
                                            });
                                        },
                                    }"
                                    x-init="$nextTick(() => setTimeout(() => window.paintShortLinkEditQrPreview && window.paintShortLinkEditQrPreview({ bg, fg, margin, watermark }), 80))"
                                    x-effect="window.paintShortLinkEditQrPreview && window.paintShortLinkEditQrPreview({ bg, fg, margin, watermark })"
                                >
                                    <div class="space-y-5">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <x-ui.color-picker x-model="bg" :label="__('Background color')" value="#ffffff" />
                                            <x-ui.color-picker x-model="fg" :label="__('Foreground color')" :value="$brandQrForeground" />
                                        </div>

                                        <x-ui.field :label="__('Margin')">
                                            <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-3">
                                                <span class="inline-flex h-10 items-center justify-center rounded-[0.75rem] bg-slate-400 text-sm font-bold text-white" x-text="margin"></span>
                                                <input x-model.number="margin" type="range" min="0" max="8" step="1" class="w-full accent-teal-600">
                                            </div>
                                        </x-ui.field>

                                        <x-ui.field :label="__('Watermark size')">
                                            <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-3">
                                                <span class="inline-flex h-10 items-center justify-center rounded-[0.75rem] bg-slate-400 text-sm font-bold text-white" x-text="Number(watermark).toFixed(1)"></span>
                                                <input x-model.number="watermark" type="range" min="0" max="10" step="0.5" class="w-full accent-teal-600">
                                            </div>
                                        </x-ui.field>

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <x-ui.select x-model="size" :label="__('Export size')">
                                                <option value="128">128 px</option>
                                                <option value="256">256 px</option>
                                                <option value="512">512 px</option>
                                                <option value="1024">1024 px</option>
                                                <option value="2048">2048 px</option>
                                            </x-ui.select>
                                            <x-ui.select x-model="format" :label="__('Format')">
                                                <option value="png">PNG</option>
                                                <option value="svg">SVG</option>
                                            </x-ui.select>
                                        </div>

                                        <x-ui.button type="button" x-on:click.prevent.stop="downloadQr()">
                                            <i class="fa-light fa-download"></i>
                                            {{ __('Download') }}
                                        </x-ui.button>
                                    </div>

                                    <aside class="rounded-[1rem] border p-5" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <div class="rounded-[1rem] p-5" x-bind:style="`background-color: ${bg}`">
                                            <div
                                                id="short-link-edit-qr-preview"
                                                class="flex items-center justify-center [&_svg]:h-44 [&_svg]:w-44"
                                                x-html="window.customizedShortLinkEditQrSvg ? window.customizedShortLinkEditQrSvg({ bg, fg, margin, watermark, preserveColors: fg === @js($brandQrForeground) }) : ''"
                                            ></div>
                                        </div>
                                        <p class="mt-4 break-all text-center text-xs" style="color: var(--theme-muted-text-color);">{{ $link->shortUrl() }}</p>
                                    </aside>
                                </div>
                            </x-ui.dialog>
                        </div>
                    </div>
                    <div class="mt-4 flex h-52 items-center justify-center rounded-[0.9rem] bg-white p-5 [&_svg]:h-40 [&_svg]:w-40" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                        {!! $qrSvg !!}
                    </div>
                    <template id="short-link-edit-qr-svg">{!! $qrSvg !!}</template>
                </section>

                <section class="rounded-[1.15rem] border bg-white p-4 shadow-[0_20px_60px_-54px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.75rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-sparkles"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Best time to share') }}</h3>
                                <x-ui.badge variant="neutral">{{ __('Analytics') }}</x-ui.badge>
                            </div>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Recommendations appear after this link receives enough clicks by time, device, and location.') }}</p>
                            <div class="mt-4 rounded-[0.9rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Optimal window') }}</span>
                                    <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link->last_clicked_at ? __('Calculating') : __('Available after clicks') }}</span>
                                </div>
                            </div>
                            @if (Route::has('portal.short-links.analytics'))
                                <a href="{{ route('portal.short-links.analytics', ['link' => $link->id]) }}" wire:navigate class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-[0.75rem] border text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                    <i class="fa-light fa-chart-line"></i>
                                    {{ __('Open link analytics') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>

@once
    <script>
        window.downloadShortLinkEditQr = function (filename) {
            const template = document.getElementById('short-link-edit-qr-svg');
            if (!template) return;

            const source = template.innerHTML.trim();
            const safeName = (filename || 'short-link').replace(/[^a-z0-9-_]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase() || 'short-link';
            const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${safeName}-qr.svg`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        };

        window.customizedShortLinkEditQrSvg = function ({ bg = '#ffffff', fg = '#000000', margin = 0, watermark = 5, preserveColors = false }) {
            const template = document.getElementById('short-link-edit-qr-svg');
            if (!template) return '';

            const parser = new DOMParser();
            const doc = parser.parseFromString(template.innerHTML.trim(), 'image/svg+xml');
            const svg = doc.documentElement;
            const isLight = (value) => ['#fff', '#ffffff', 'white', 'rgb(255,255,255)', 'rgb(255, 255, 255)'].includes(String(value || '').trim().toLowerCase());

            if (!preserveColors) {
                svg.querySelectorAll('[fill]').forEach((node) => {
                    const fill = node.getAttribute('fill');
                    if (!fill || fill === 'none') return;
                    const isBackgroundRect = node.tagName.toLowerCase() === 'rect'
                        && node.getAttribute('width') === '100%'
                        && node.getAttribute('height') === '100%';

                    if (isBackgroundRect) {
                        node.setAttribute('fill', bg);
                        return;
                    }

                    if (fill.startsWith('url(')) {
                        node.setAttribute('fill', fg);
                        return;
                    }
                    node.setAttribute('fill', isLight(fill) ? bg : fg);
                });

                svg.querySelectorAll('[stroke]').forEach((node) => {
                    const stroke = node.getAttribute('stroke');
                    if (!stroke || stroke === 'none') return;
                    if (stroke.startsWith('url(')) {
                        node.setAttribute('stroke', fg);
                        return;
                    }
                    node.setAttribute('stroke', isLight(stroke) ? bg : fg);
                });
            } else {
                svg.querySelectorAll('[fill]').forEach((node) => {
                    const fill = node.getAttribute('fill');
                    const isBackgroundRect = node.tagName.toLowerCase() === 'rect'
                        && node.getAttribute('width') === '100%'
                        && node.getAttribute('height') === '100%';

                    if (isBackgroundRect || isLight(fill)) {
                        node.setAttribute('fill', bg);
                    }
                });
            }

            svg.querySelectorAll('text').forEach((node) => {
                if (Number(watermark) <= 0) {
                    node.setAttribute('display', 'none');
                } else {
                    node.setAttribute('font-size', String(Number(watermark) * 3.2));
                    if (!preserveColors) {
                        node.setAttribute('fill', fg);
                    }
                }
            });

            const viewBox = (svg.getAttribute('viewBox') || '0 0 256 256').split(/\s+/).map(Number);
            const width = viewBox[2] || 256;
            const height = viewBox[3] || 256;
            const logoScale = Math.max(0, Math.min(10, Number(watermark) || 0)) / 5;

            svg.querySelectorAll('image').forEach((imageNode) => {
                if (logoScale <= 0) {
                    imageNode.setAttribute('display', 'none');
                    return;
                }

                const nextSize = Math.max(8, Math.round(width * 0.145 * logoScale));
                const nextX = Math.round((width - nextSize) / 2);
                imageNode.setAttribute('x', String(nextX));
                imageNode.setAttribute('y', String(nextX));
                imageNode.setAttribute('width', String(nextSize));
                imageNode.setAttribute('height', String(nextSize));
                imageNode.removeAttribute('display');

                const frameSize = Math.round(nextSize * 1.32);
                const frameX = Math.round((width - frameSize) / 2);
                const nearbyRects = Array.from(svg.querySelectorAll('rect')).filter((rect) => {
                    const rectWidth = Number(rect.getAttribute('width') || 0);
                    const rectHeight = Number(rect.getAttribute('height') || 0);
                    const rectX = Number(rect.getAttribute('x') || 0);
                    const rectY = Number(rect.getAttribute('y') || 0);

                    return rectWidth > width * 0.08
                        && rectWidth < width * 0.32
                        && Math.abs(rectWidth - rectHeight) < 2
                        && Math.abs((rectX + rectWidth / 2) - width / 2) < width * 0.08
                        && Math.abs((rectY + rectHeight / 2) - height / 2) < height * 0.08;
                });

                nearbyRects.forEach((rect) => {
                    rect.setAttribute('x', String(frameX));
                    rect.setAttribute('y', String(frameX));
                    rect.setAttribute('width', String(frameSize));
                    rect.setAttribute('height', String(frameSize));
                    rect.setAttribute('rx', String(Math.round(frameSize * 0.24)));
                    rect.removeAttribute('display');
                });
            });

            const padding = Math.max(0, Number(margin) || 0) * 8;
            const serialized = new XMLSerializer().serializeToString(svg);

            if (padding <= 0) {
                return serialized;
            }

            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width + padding * 2} ${height + padding * 2}"><rect width="100%" height="100%" fill="${bg}"/><svg x="${padding}" y="${padding}" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">${serialized.replace(/^<svg[^>]*>|<\/svg>$/g, '')}</svg></svg>`;
        };

        window.paintShortLinkEditQrPreview = function (options) {
            const preview = document.getElementById('short-link-edit-qr-preview');
            if (!preview) return;

            preview.innerHTML = window.customizedShortLinkEditQrSvg(options);
        };

        window.downloadCustomizedShortLinkEditQr = function (options) {
            const svg = window.customizedShortLinkEditQrSvg(options);
            if (!svg) return;

            const safeName = (options.filename || 'short-link').replace(/[^a-z0-9-_]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase() || 'short-link';
            const format = String(options.format || 'png').toLowerCase();

            if (format === 'svg') {
                const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `${safeName}-qr.svg`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
                return;
            }

            const size = Math.max(128, Number(options.size) || 512);
            const image = new Image();
            const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);

            image.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = size;
                canvas.height = size;
                const context = canvas.getContext('2d');
                context.fillStyle = options.bg || '#ffffff';
                context.fillRect(0, 0, size, size);
                context.drawImage(image, 0, 0, size, size);
                URL.revokeObjectURL(url);

                canvas.toBlob((pngBlob) => {
                    if (!pngBlob) return;
                    const pngUrl = URL.createObjectURL(pngBlob);
                    const link = document.createElement('a');
                    link.href = pngUrl;
                    link.download = `${safeName}-qr.png`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(pngUrl);
                }, 'image/png');
            };

            image.src = url;
        };
    </script>
@endonce
