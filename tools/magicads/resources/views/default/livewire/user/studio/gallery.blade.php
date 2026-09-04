<div x-data="{ selectedAsset: @js($openAsset) }">
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">
            {{-- Top Bar --}}
            <div class="mb-6 flex items-center justify-between">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Ad Creatives') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-4">
                    <a href="{{ route('user.studio.images') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 flex items-center gap-1 transition">
                        <flux:icon.photo class="size-4" /> {{ __('Image Studio') }}
                    </a>
                    <a href="{{ route('user.studio.videos') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 flex items-center gap-1 transition">
                        <flux:icon.film class="size-4" /> {{ __('Video Studio') }}
                    </a>
                </div>
            </div>

            {{-- Header --}}
            <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-zinc-800 dark:text-zinc-100">{{ __('Gallery') }}</h1>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('All your AI-generated ad creatives in one place') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Type Filter Pills --}}
                    <div class="flex items-center bg-zinc-100 dark:bg-(--default-element-light-bg-color) rounded-lg p-0.5">
                        @foreach(['all' => __('All'), 'image' => __('Images'), 'video' => __('Videos')] as $key => $label)
                            <button wire:click="$set('typeFilter', '{{ $key }}')" class="px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ $typeFilter === $key ? 'bg-white text-zinc-800 shadow-sm dark:bg-neutral-700 dark:text-zinc-200' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    {{-- Sort --}}
                    <flux:select wire:model.live="sortOrder" aria-label="{{ __('Sort order') }}" class="!w-28 !text-xs">
                        <flux:select.option value="newest">{{ __('Newest') }}</flux:select.option>
                        <flux:select.option value="oldest">{{ __('Oldest') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>

            {{-- Grid --}}
            @if($assets->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                    @foreach($assets as $asset)
                        @php
                            $breakdown = $asset->promptBreakdown();
                            $cardDesc = $breakdown['description'] ?: $asset->prompt;
                        @endphp
                        <div class="group rounded-xl overflow-hidden bg-white border border-zinc-200 dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" @click="selectedAsset = @js([
                            'id' => $asset->id,
                            'type' => $asset->type,
                            'prompt' => $asset->prompt,
                            'provider' => $asset->provider,
                            'preset_slug' => $asset->preset_slug,
                            'width' => $asset->width,
                            'height' => $asset->height,
                            'duration' => $asset->duration,
                            'file_path' => $asset->fileUrl(),
                            'mime_type' => $asset->mime_type,
                            'file_size' => $asset->file_size,
                            'created_at' => $asset->created_at->format('M d, Y · h:i A'),
                            'overlay_text' => $asset->generation_meta['overlayText'] ?? null,
                            'cta_text' => $asset->generation_meta['ctaText'] ?? null,
                            'brand_primary' => $asset->generation_meta['brandPrimaryColor'] ?? null,
                            'brand_secondary' => $asset->generation_meta['brandSecondaryColor'] ?? null,
                            'force_overlay' => $asset->generation_meta['forceOverlay'] ?? null,
                            'reference_image' => $asset->generation_meta['referenceImagePath'] ?? null,
                            'breakdown' => $breakdown,
                        ]) ">
                            {{-- Thumbnail --}}
                            <div class="relative aspect-square bg-zinc-100 dark:bg-(--default-element-bg-color) overflow-hidden">
                                @if($asset->type === 'image' && $asset->file_path)
                                    <img src="{{ $asset->fileUrl() }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                                @elseif($asset->type === 'video' && $asset->file_path)
                                    {{-- Video fills the box like an image; first frame as the poster --}}
                                    <video class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" muted playsinline preload="metadata">
                                        <source src="{{ $asset->fileUrl() }}#t=0.1" @if($asset->mime_type) type="{{ $asset->mime_type }}" @endif />
                                    </video>
                                    {{-- Centered play button --}}
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full text-white backdrop-blur-sm ring-1 ring-white/25 shadow-lg transition-transform duration-200 group-hover:scale-110"
                                              style="background-color: var(--db-brand-1, #4F46E5);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5"><path d="M8 5v14l11-7z"/></svg>
                                        </span>
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div class="text-center">
                                            <flux:icon.film class="size-10 text-zinc-300 dark:text-neutral-600 mx-auto mb-1" />
                                            @if($asset->duration)
                                                <span class="text-[10px] text-zinc-400">{{ $asset->duration }}s</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Overlay on hover --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                                        <span class="text-[10px] text-white/80 truncate max-w-[70%]">{{ Str::limit($cardDesc, 35) }}</span>
                                        <flux:icon.arrow-top-right-on-square class="size-4 text-white/80" />
                                    </div>
                                </div>

                                {{-- Type Badge --}}
                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider backdrop-blur-sm {{ $asset->type === 'image' ? 'bg-indigo-500/80 text-white' : 'bg-violet-500/80 text-white' }}">
                                        @if($asset->type === 'image')
                                            <flux:icon.photo class="size-2.5" />
                                        @else
                                            <flux:icon.film class="size-2.5" />
                                        @endif
                                        {{ ucfirst($asset->type) }}
                                    </span>
                                </div>

                                {{-- Dimensions Badge --}}
                                <div class="absolute top-2 right-2">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-mono bg-black/40 text-white/70 backdrop-blur-sm">{{ $asset->width }}×{{ $asset->height }}</span>
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="p-3">
                                <p class="text-[11px] font-medium text-zinc-700 dark:text-zinc-300 truncate">{{ Str::limit($cardDesc, 50) }}</p>
                                <div class="flex items-center justify-between mt-1.5">
                                    <span class="text-[10px] text-zinc-400">{{ $asset->provider }}</span>
                                    <span class="text-[10px] text-zinc-400">{{ $asset->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6 mb-4">
                    {{ $assets->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-20">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center">
                        <flux:icon.sparkles class="size-10 text-indigo-400" />
                    </div>
                    <h3 class="text-xl font-bold text-zinc-700 dark:text-zinc-300 mb-2">{{ __('Your gallery is empty') }}</h3>
                    <p class="text-sm text-zinc-500 mb-6 max-w-md mx-auto">{{ __('Start creating stunning ad creatives with AI. Your generated images and videos will appear here.') }}</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('user.studio.images') }}" wire:navigate class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                            <flux:icon.photo class="size-4" /> {{ __('Create Image') }}
                        </a>
                        <a href="{{ route('user.studio.videos') }}" wire:navigate class="px-5 py-2.5 border border-zinc-300 dark:border-neutral-600 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/10 transition flex items-center gap-2">
                            <flux:icon.film class="size-4" /> {{ __('Create Video') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>


    {{-- ============================================ --}}
    {{-- Detail Lightbox Modal                        --}}
    {{-- ============================================ --}}
    <div x-show="selectedAsset" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.escape.window="selectedAsset = null" class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="selectedAsset = null"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white dark:bg-(--default-element-bg-color) rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl flex flex-col md:flex-row" @click.stop>
            {{-- Left: Preview --}}
            <div class="md:w-3/5 bg-zinc-900 flex items-center justify-center min-h-[300px] md:min-h-0">
                <template x-if="selectedAsset?.type === 'image' && selectedAsset?.file_path">
                    <img :src="selectedAsset.file_path" alt="" class="max-w-full max-h-[70vh] object-contain" />
                </template>
                <template x-if="selectedAsset?.type === 'video' && selectedAsset?.file_path">
                    <video controls class="max-w-full max-h-[70vh]" preload="metadata">
                        <source :src="selectedAsset.file_path" :type="selectedAsset.mime_type">
                    </video>
                </template>
                <template x-if="!selectedAsset?.file_path">
                    <div class="text-center p-8">
                        <flux:icon.photo class="size-12 text-zinc-600 mx-auto mb-2" />
                        <p class="text-sm text-zinc-500">{{ __('Preview unavailable') }}</p>
                    </div>
                </template>
            </div>

            {{-- Right: Details --}}
            <div class="md:w-2/5 p-6 overflow-y-auto">
                {{-- Close --}}
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <template x-if="selectedAsset?.type === 'image'">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                <flux:icon.photo class="size-3" /> {{ __('Image') }}
                            </span>
                        </template>
                        <template x-if="selectedAsset?.type === 'video'">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300">
                                <flux:icon.film class="size-3" /> {{ __('Video') }}
                            </span>
                        </template>
                    </div>
                    <button @click="selectedAsset = null" class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-(--default-element-light-bg-color) flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-white/10 transition">
                        <flux:icon.x-mark class="size-4 text-zinc-500" />
                    </button>
                </div>

                {{-- Description Box — the user's own input text --}}
                <div class="mb-5 rounded-xl border border-zinc-200 dark:border-white/8 bg-zinc-50 dark:bg-(--default-element-light-bg-color) p-4" x-show="selectedAsset?.breakdown?.description" x-data="{ copied: false }">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <flux:icon.chat-bubble-bottom-center-text class="size-3.5 text-indigo-500" />
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</h4>
                        </div>
                        <button type="button"
                                @click="navigator.clipboard.writeText(selectedAsset?.breakdown?.description || ''); copied = true; setTimeout(() => copied = false, 1500)"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 hover:bg-white dark:hover:bg-white/5 transition"
                                :aria-label="copied ? '{{ __('Copied') }}' : '{{ __('Copy') }}'">
                            <template x-if="!copied">
                                <span class="inline-flex items-center gap-1"><flux:icon.clipboard-document class="size-3.5" /> {{ __('Copy') }}</span>
                            </template>
                            <template x-if="copied">
                                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><flux:icon.check class="size-3.5" /> {{ __('Copied') }}</span>
                            </template>
                        </button>
                    </div>
                    <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed" x-text="selectedAsset?.breakdown?.description"></p>
                </div>

                {{-- Ad Attributes — the structured selections, as individual chips --}}
                <div class="mb-5" x-show="selectedAsset?.breakdown?.attributes?.length">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Ad Attributes') }}</h4>
                    <div class="grid grid-cols-2 gap-2.5">
                        <template x-for="attr in (selectedAsset?.breakdown?.attributes || [])" :key="attr.label">
                            <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3">
                                <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400 mb-0.5" x-text="attr.label"></div>
                                <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 capitalize truncate" x-text="attr.value"></div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Brand Context Box — the folded-in brand block --}}
                <div class="mb-5 rounded-xl border border-zinc-200 dark:border-white/8 bg-zinc-50 dark:bg-(--default-element-light-bg-color) p-4" x-show="selectedAsset?.breakdown?.brand_context">
                    <div class="flex items-center gap-1.5 mb-2">
                        <flux:icon.sparkles class="size-3.5 text-amber-500" />
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Brand Context') }}</h4>
                    </div>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed" x-text="selectedAsset?.breakdown?.brand_context"></p>
                </div>

                {{-- Generation Settings — individual feature boxes --}}
                <div class="mb-5">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Generation Settings') }}</h4>
                    <div class="grid grid-cols-2 gap-2.5">
                        {{-- Model/Provider --}}
                        <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <flux:icon.cpu-chip class="size-3 text-zinc-400" />
                                <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Model') }}</span>
                            </div>
                            <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 truncate" x-text="selectedAsset?.provider"></div>
                        </div>

                        {{-- Preset --}}
                        <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <flux:icon.squares-2x2 class="size-3 text-zinc-400" />
                                <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Preset') }}</span>
                            </div>
                            <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 truncate" x-text="selectedAsset?.preset_slug"></div>
                        </div>

                        {{-- Duration (video only) --}}
                        <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3" x-show="selectedAsset?.duration">
                            <div class="flex items-center gap-1.5 mb-1">
                                <flux:icon.clock class="size-3 text-zinc-400" />
                                <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Duration') }}</span>
                            </div>
                            <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200"><span x-text="selectedAsset?.duration"></span>s</div>
                        </div>

                        {{-- Text Overlay --}}
                        <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3" x-show="selectedAsset?.overlay_text">
                            <div class="flex items-center gap-1.5 mb-1">
                                <flux:icon.language class="size-3 text-zinc-400" />
                                <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Overlay Text') }}</span>
                            </div>
                            <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 truncate" x-text="selectedAsset?.overlay_text"></div>
                        </div>

                        {{-- CTA Text --}}
                        <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3" x-show="selectedAsset?.cta_text">
                            <div class="flex items-center gap-1.5 mb-1">
                                <flux:icon.cursor-arrow-rays class="size-3 text-zinc-400" />
                                <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('CTA') }}</span>
                            </div>
                            <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 truncate" x-text="selectedAsset?.cta_text"></div>
                        </div>

                        {{-- Brand Colors --}}
                        <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3" x-show="selectedAsset?.brand_primary || selectedAsset?.brand_secondary">
                            <div class="flex items-center gap-1.5 mb-1">
                                <flux:icon.swatch class="size-3 text-zinc-400" />
                                <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Brand Colors') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <template x-if="selectedAsset?.brand_primary">
                                    <span class="w-4 h-4 rounded-full border border-zinc-200 dark:border-white/10" :style="'background:' + selectedAsset.brand_primary"></span>
                                </template>
                                <template x-if="selectedAsset?.brand_secondary">
                                    <span class="w-4 h-4 rounded-full border border-zinc-200 dark:border-white/10" :style="'background:' + selectedAsset.brand_secondary"></span>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Created date --}}
                <div class="mb-5 rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-3">
                    <div class="flex items-center gap-1.5 mb-1">
                        <flux:icon.calendar class="size-3 text-zinc-400" />
                        <span class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Created') }}</span>
                    </div>
                    <div class="text-xs font-semibold text-zinc-700 dark:text-zinc-200" x-text="selectedAsset?.created_at"></div>
                </div>

                {{-- Actions --}}
                <div class="space-y-2">
                    <template x-if="selectedAsset?.id">
                        <button @click="$wire.download(selectedAsset.id)" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                            <flux:icon.arrow-down-tray class="size-4" /> {{ __('Download') }}
                        </button>
                    </template>
                    <a :href="(selectedAsset?.type === 'image' ? '{{ route('user.studio.images') }}' : '{{ route('user.studio.videos') }}') + '?reuse=' + selectedAsset?.id" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-zinc-300 dark:border-neutral-600 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                        <flux:icon.arrow-path class="size-4" /> {{ __('Reuse Prompt') }}
                    </a>
                    @if (\App\Services\HelperService::extensionPromptMarketplace())
                        <template x-if="selectedAsset?.id">
                            <a :href="'{{ route('user.marketplace.list', ['creativeId' => 987654321]) }}'.replace('987654321', selectedAsset?.id)"
                               class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-white transition hover:opacity-95"
                               style="background-color: #4F46E5;">
                                <flux:icon.tag class="size-4" /> {{ __('Sell / Share Prompt') }}
                            </a>
                        </template>
                    @endif
                    <button @click="if(confirm('{{ __('Are you sure?') }}')) { $wire.delete(selectedAsset.id); selectedAsset = null; }" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-red-200 dark:border-red-900/50 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                        <flux:icon.trash class="size-4" /> {{ __('Delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
