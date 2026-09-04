<div x-data="{ preview: null }">
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Top toolbar: breadcrumbs + actions --}}
            <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Workspace') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="{{ route('user.projects.index') }}" separator="slash" class="text-xs">{{ __('Projects') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $project->name }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                <div class="flex items-center gap-1.5">
                    {{-- Create a new creative within this project's context: the
                         studios read ?project={id} and associate the result with
                         this project (and default its Brand Kit). --}}
                    <flux:dropdown position="bottom" align="end">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-500 transition">
                            <flux:icon.sparkles class="size-4" />
                            <span>{{ __('Create new') }}</span>
                            <flux:icon.chevron-down class="size-3.5 opacity-80" />
                        </button>

                        <flux:menu>
                            <flux:menu.item icon="document-text" :href="route('user.copy.studio', ['project' => $project->id])" wire:navigate>{{ __('Ad copy') }}</flux:menu.item>
                            <flux:menu.item icon="photo" :href="route('user.studio.images', ['project' => $project->id])" wire:navigate>{{ __('Image') }}</flux:menu.item>
                            <flux:menu.item icon="film" :href="route('user.studio.videos', ['project' => $project->id])" wire:navigate>{{ __('Video') }}</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    <flux:modal.trigger name="associate-creative">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 dark:text-zinc-300 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:border-zinc-700 transition">
                            <flux:icon.plus class="size-4" />
                            <span>{{ __('Add creatives') }}</span>
                        </button>
                    </flux:modal.trigger>

                    <flux:modal.trigger name="rename-project">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 dark:text-zinc-300 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:border-zinc-700 transition">
                            <flux:icon.pencil-square class="size-4" />
                            <span>{{ __('Rename') }}</span>
                        </button>
                    </flux:modal.trigger>

                    <flux:modal.trigger name="delete-project">
                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:text-rose-700 bg-white hover:bg-rose-50 border border-rose-200 dark:text-rose-400 dark:hover:text-rose-300 dark:bg-zinc-800 dark:hover:bg-rose-950/30 dark:border-rose-900/50 transition">
                            <flux:icon.trash class="size-4" />
                            <span>{{ __('Delete') }}</span>
                        </button>
                    </flux:modal.trigger>
                </div>
            </div>

            {{-- Header: name + description (3.6) --}}
            <div class="mb-8 flex items-start gap-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                    <flux:icon.folder class="size-6" />
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl md:text-2xl font-extrabold text-zinc-900 dark:text-white leading-tight tracking-tight">{{ $project->name }}</h1>
                    @if($project->description)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">{{ $project->description }}</p>
                    @else
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1 italic">{{ __('No description.') }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Creative sections (3.7) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Ad Copy section --}}
                    <section class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-zinc-100 dark:border-white/8">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                                <flux:icon.document-text class="size-4" />
                            </div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ __('Ad Copy') }}</h2>
                            <span class="ml-auto text-[11px] font-medium text-zinc-400 dark:text-zinc-500">{{ $adCopies->count() }}</span>
                        </div>

                        @if($adCopies->isEmpty())
                            {{-- Per-group empty state (3.9) --}}
                            <div class="px-5 py-8 text-center">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('No ad copy in this project yet.') }}</p>
                            </div>
                        @else
                            <ul class="divide-y divide-zinc-100 dark:divide-white/8">
                                @foreach($adCopies as $copy)
                                    <li wire:key="copy-{{ $copy->id }}" class="flex items-center gap-3 px-5 py-3">
                                        <a href="{{ route('user.copy.library', ['focus' => $copy->id]) }}" wire:navigate
                                           class="group/copy min-w-0 flex-1 flex items-center gap-2"
                                           title="{{ __('View in Copy Library') }}">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100 truncate group-hover/copy:text-indigo-600 dark:group-hover/copy:text-indigo-400 transition-colors">{{ $copy->title ?: $copy->platformLabel() }}</p>
                                                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-0.5">{{ $copy->platformLabel() }}</p>
                                            </div>
                                            <flux:icon.arrow-up-right class="size-3.5 shrink-0 text-zinc-300 group-hover/copy:text-indigo-500 dark:text-zinc-600 transition-colors" />
                                        </a>
                                        <button type="button"
                                                wire:click="detach('copy', {{ $copy->id }})"
                                                wire:loading.attr="disabled"
                                                class="shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/30 transition">
                                            <flux:icon.x-mark class="size-3.5" />
                                            {{ __('Remove') }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>

                    {{-- Images section --}}
                    <section class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-zinc-100 dark:border-white/8">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                                <flux:icon.photo class="size-4" />
                            </div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ __('Images') }}</h2>
                            <span class="ml-auto text-[11px] font-medium text-zinc-400 dark:text-zinc-500">{{ $images->count() }}</span>
                        </div>

                        @if($images->isEmpty())
                            <div class="px-5 py-8 text-center">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('No images in this project yet.') }}</p>
                            </div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-5">
                                @foreach($images as $image)
                                    <div wire:key="image-{{ $image->id }}" class="group relative rounded-xl overflow-hidden border border-zinc-200 dark:border-white/8 bg-zinc-50 dark:bg-zinc-900 aspect-square">
                                        @if($image->fileUrl())
                                            <button type="button"
                                                    x-on:click="preview = { type: 'image', url: @js($image->fileUrl()), download: @js(route('user.studio.download', $image->id)) }"
                                                    class="block w-full h-full cursor-zoom-in">
                                                <img src="{{ $image->fileUrl() }}" alt="" class="w-full h-full object-cover" />
                                            </button>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600">
                                                <flux:icon.photo class="size-8" />
                                            </div>
                                        @endif
                                        <div class="absolute top-1.5 right-1.5 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                            @if($image->fileUrl())
                                                <a href="{{ route('user.studio.download', $image->id) }}"
                                                   title="{{ __('Download') }}"
                                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/90 text-zinc-600 hover:text-indigo-600 shadow-sm dark:bg-zinc-800/90 dark:text-zinc-300 dark:hover:text-indigo-400">
                                                    <flux:icon.arrow-down-tray class="size-4" />
                                                </a>
                                            @endif
                                            <button type="button"
                                                    wire:click="detach('image', {{ $image->id }})"
                                                    wire:loading.attr="disabled"
                                                    title="{{ __('Remove from project') }}"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/90 text-zinc-600 hover:text-rose-600 shadow-sm dark:bg-zinc-800/90 dark:text-zinc-300 dark:hover:text-rose-400">
                                                <flux:icon.x-mark class="size-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    {{-- Videos section --}}
                    <section class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-zinc-100 dark:border-white/8">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                                <flux:icon.film class="size-4" />
                            </div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ __('Videos') }}</h2>
                            <span class="ml-auto text-[11px] font-medium text-zinc-400 dark:text-zinc-500">{{ $videos->count() }}</span>
                        </div>

                        @if($videos->isEmpty())
                            <div class="px-5 py-8 text-center">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('No videos in this project yet.') }}</p>
                            </div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-5">
                                @foreach($videos as $video)
                                    <div wire:key="video-{{ $video->id }}" class="group relative rounded-xl overflow-hidden border border-zinc-200 dark:border-white/8 bg-zinc-50 dark:bg-zinc-900 aspect-square">
                                        @if($video->fileUrl())
                                            <button type="button"
                                                    x-on:click="preview = { type: 'video', url: @js($video->fileUrl()), download: @js(route('user.studio.download', $video->id)) }"
                                                    class="block w-full h-full cursor-pointer">
                                                <video src="{{ $video->fileUrl() }}#t=0.1" class="w-full h-full object-cover" muted preload="metadata"></video>
                                                <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-black/50 text-white">
                                                        <flux:icon.play class="size-5" />
                                                    </span>
                                                </span>
                                            </button>
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600">
                                                <flux:icon.film class="size-8" />
                                            </div>
                                        @endif
                                        <div class="absolute top-1.5 right-1.5 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                            @if($video->fileUrl())
                                                <a href="{{ route('user.studio.download', $video->id) }}"
                                                   title="{{ __('Download') }}"
                                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/90 text-zinc-600 hover:text-indigo-600 shadow-sm dark:bg-zinc-800/90 dark:text-zinc-300 dark:hover:text-indigo-400">
                                                    <flux:icon.arrow-down-tray class="size-4" />
                                                </a>
                                            @endif
                                            <button type="button"
                                                    wire:click="detach('video', {{ $video->id }})"
                                                    wire:loading.attr="disabled"
                                                    title="{{ __('Remove from project') }}"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/90 text-zinc-600 hover:text-rose-600 shadow-sm dark:bg-zinc-800/90 dark:text-zinc-300 dark:hover:text-rose-400">
                                                <flux:icon.x-mark class="size-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                {{-- Brand kit panel (3.8, 10.3, 10.4) --}}
                <div class="space-y-6">
                    <section class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-zinc-100 dark:border-white/8">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                                <flux:icon.swatch class="size-4" />
                            </div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ __('Brand Kit') }}</h2>
                        </div>

                        <div class="p-5">
                            @if($brand)
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1 shrink-0">
                                        @foreach(array_filter([$brand->primary_color, $brand->secondary_color, $brand->accent_color]) as $color)
                                            <span class="inline-block w-5 h-5 rounded-full border border-black/5 dark:border-white/10" style="background-color: {{ $color }};"></span>
                                        @endforeach
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $brand->name }}</p>
                                        @if($brand->tagline)
                                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate">{{ $brand->tagline }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center gap-2">
                                    <flux:modal.trigger name="project-brand-kit">
                                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 dark:text-zinc-300 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:border-zinc-700 transition">
                                            {{ __('Change') }}
                                        </button>
                                    </flux:modal.trigger>
                                    <button type="button"
                                            wire:click="removeBrandKit"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/30 transition">
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            @else
                                {{-- Empty state (3.8) --}}
                                <div class="text-center py-4">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 mb-3">
                                        <flux:icon.swatch class="size-5" />
                                    </div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">{{ __('No brand kit associated with this project.') }}</p>
                                    @if($brands->isEmpty())
                                        <p class="text-[11px] text-zinc-400 dark:text-zinc-500">{{ __('Create a brand to use it here.') }}</p>
                                    @else
                                        <flux:modal.trigger name="project-brand-kit">
                                            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-500 transition">
                                                <flux:icon.plus class="size-3.5" />
                                                {{ __('Set brand kit') }}
                                            </button>
                                        </flux:modal.trigger>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    {{-- Rename modal (6.6) --}}
    <flux:modal name="rename-project" class="md:w-[460px]">
        <form wire:submit="rename" class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Rename Project') }}</flux:heading>
                <flux:subheading>{{ __('Give this project a clear, recognizable name.') }}</flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="editName" type="text" maxlength="120" />
                <flux:error name="name" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="rename">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation modal — confirm-gated (6.3, 6.4) --}}
    <flux:modal name="delete-project" class="md:w-[460px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete project?') }}</flux:heading>
                <flux:subheading>
                    {{ __('This deletes the project ":name". Its ad copy, images, and videos are kept and become unassigned.', ['name' => $project->name]) }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="danger"
                             x-on:click="$wire.set('confirmingDelete', true).then(() => $wire.delete())"
                             wire:loading.attr="disabled"
                             wire:target="delete">
                    {{ __('Delete project') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Associate creatives modal — lists the user's Unassigned creatives (5.7) --}}
    <flux:modal name="associate-creative" class="md:w-[560px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Add creatives') }}</flux:heading>
                <flux:subheading>{{ __('Add your unassigned ad copy, images, and videos to this project.') }}</flux:subheading>
            </div>

            @if(! $hasUnassigned)
                <div class="rounded-xl border border-dashed border-zinc-200 dark:border-white/8 px-5 py-8 text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('You have no unassigned creatives to add.') }}</p>
                </div>
            @else
                <div class="max-h-[60vh] overflow-y-auto space-y-5 pr-1">
                    @if($unassignedCopies->isNotEmpty())
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">{{ __('Ad Copy') }}</p>
                            <ul class="space-y-1.5">
                                @foreach($unassignedCopies as $copy)
                                    <li wire:key="unassigned-copy-{{ $copy->id }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-white/8 px-3 py-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100 truncate">{{ $copy->title ?: $copy->platformLabel() }}</p>
                                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500">{{ $copy->platformLabel() }}</p>
                                        </div>
                                        <button type="button"
                                                wire:click="associate('copy', {{ $copy->id }})"
                                                wire:loading.attr="disabled"
                                                class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 dark:border-indigo-900/50 dark:text-indigo-400 transition">
                                            <flux:icon.plus class="size-3.5" /> {{ __('Add') }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($unassignedCreatives->isNotEmpty())
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">{{ __('Images & Videos') }}</p>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                                @foreach($unassignedCreatives as $creative)
                                    <div wire:key="unassigned-creative-{{ $creative->id }}" class="group relative rounded-lg overflow-hidden border border-zinc-200 dark:border-white/8 bg-zinc-50 dark:bg-zinc-900 aspect-square">
                                        @if($creative->type === 'video')
                                            @if($creative->fileUrl())
                                                <video src="{{ $creative->fileUrl() }}" class="w-full h-full object-cover" muted preload="metadata"></video>
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600"><flux:icon.film class="size-6" /></div>
                                            @endif
                                        @else
                                            @if($creative->fileUrl())
                                                <img src="{{ $creative->fileUrl() }}" alt="" class="w-full h-full object-cover" />
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-300 dark:text-zinc-600"><flux:icon.photo class="size-6" /></div>
                                            @endif
                                        @endif
                                        <button type="button"
                                                wire:click="associate('{{ $creative->type }}', {{ $creative->id }})"
                                                wire:loading.attr="disabled"
                                                title="{{ __('Add to project') }}"
                                                class="absolute inset-0 flex items-center justify-center bg-indigo-600/0 group-hover:bg-indigo-600/70 text-white opacity-0 group-hover:opacity-100 transition">
                                            <flux:icon.plus class="size-6" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Done') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Brand kit picker modal (10.3, 10.4) --}}
    <flux:modal name="project-brand-kit" class="md:w-[460px]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Project brand kit') }}</flux:heading>
                <flux:subheading>{{ __('Choose a brand to apply its identity to creatives in this project.') }}</flux:subheading>
            </div>

            @if($brands->isEmpty())
                <div class="rounded-xl border border-dashed border-zinc-200 dark:border-white/8 px-5 py-8 text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('You have no brands yet.') }}</p>
                </div>
            @else
                <ul class="space-y-1.5 max-h-[50vh] overflow-y-auto pr-1">
                    @foreach($brands as $b)
                        <li wire:key="brand-{{ $b->id }}">
                            <button type="button"
                                    wire:click="setBrandKit({{ $b->id }})"
                                    wire:loading.attr="disabled"
                                    @class([
                                        'w-full flex items-center gap-3 rounded-lg border px-3 py-2.5 text-left transition',
                                        'border-indigo-300 bg-indigo-50 dark:border-indigo-700/60 dark:bg-indigo-950/30' => $brand && $brand->id === $b->id,
                                        'border-zinc-200 hover:bg-zinc-50 dark:border-white/8 dark:hover:bg-white/5' => ! ($brand && $brand->id === $b->id),
                                    ])>
                                <div class="flex items-center gap-1 shrink-0">
                                    @foreach(array_filter([$b->primary_color, $b->secondary_color, $b->accent_color]) as $color)
                                        <span class="inline-block w-4 h-4 rounded-full border border-black/5 dark:border-white/10" style="background-color: {{ $color }};"></span>
                                    @endforeach
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100 truncate">{{ $b->name }}</p>
                                    @if($b->tagline)
                                        <p class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate">{{ $b->tagline }}</p>
                                    @endif
                                </div>
                                @if($brand && $brand->id === $b->id)
                                    <flux:icon.check class="size-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="flex justify-between gap-2">
                @if($brand)
                    <flux:button type="button" variant="ghost" wire:click="removeBrandKit" wire:loading.attr="disabled">
                        {{ __('Remove brand kit') }}
                    </flux:button>
                @else
                    <span></span>
                @endif
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Inline preview lightbox for images and videos --}}
    <div x-show="preview" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         x-on:keydown.escape.window="preview = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-8">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" x-on:click="preview = null"></div>

        <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col" x-on:click.stop>
            <div class="flex items-center justify-end gap-2 mb-3">
                <a x-show="preview?.download" :href="preview?.download"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-500 transition">
                    <flux:icon.arrow-down-tray class="size-4" />
                    {{ __('Download') }}
                </a>
                <button type="button" x-on:click="preview = null"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/10 text-white hover:bg-white/20 transition">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <div class="flex-1 min-h-0 flex items-center justify-center">
                <template x-if="preview?.type === 'image'">
                    <img :src="preview?.url" alt="" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl" />
                </template>
                <template x-if="preview?.type === 'video'">
                    <video :src="preview?.url" controls autoplay class="max-w-full max-h-[80vh] rounded-lg shadow-2xl"></video>
                </template>
            </div>
        </div>
    </div>
</div>
