<div>
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Workspace') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Projects') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            {{-- Header: heading + create control --}}
            <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                        <flux:icon.folder class="size-6" />
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl md:text-2xl font-extrabold text-zinc-900 dark:text-white leading-tight tracking-tight">{{ __('Projects') }}</h1>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-lg">
                            {{ __('Group related ad copy, images, and videos into a single workspace so everything for a campaign lives in one place.') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    @if($atLimit)
                        {{-- At/over limit: create control disabled with explanatory message + upgrade link (9.5, 9.7) --}}
                        <div class="flex flex-col sm:items-end gap-1">
                            <button type="button" disabled aria-disabled="true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-400 bg-zinc-50 border border-zinc-200 cursor-not-allowed dark:text-zinc-500 dark:bg-zinc-900 dark:border-zinc-800">
                                <flux:icon.plus class="size-4" />
                                <span>{{ __('New Project') }}</span>
                            </button>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                {{ __('You have reached your project limit (:limit).', ['limit' => $projectLimit]) }}
                                @if($upgradeUrl)
                                    <a href="{{ $upgradeUrl }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline">{{ __('Upgrade your plan') }}</a>
                                @endif
                            </p>
                        </div>
                    @else
                        <flux:modal.trigger name="create-project">
                            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 dark:text-zinc-300 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:border-zinc-700 transition">
                                <flux:icon.plus class="size-4 relative" />
                                <span class="relative">{{ __('New Project') }}</span>
                            </button>
                        </flux:modal.trigger>
                    @endif
                </div>
            </div>

            @if($projects->isEmpty())
                {{-- Empty state with a "create first project" control (3.3) --}}
                <div class="relative overflow-hidden rounded-3xl border border-dashed border-zinc-200 dark:border-white/8 p-16 text-center">
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 mb-4">
                            <flux:icon.folder-plus class="size-8" />
                        </div>
                        <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-100 mb-1">{{ __('Create your first project') }}</h2>
                        <p class="text-sm text-zinc-500 max-w-md mx-auto mb-6">{{ __('A project keeps your related ad copy, images, and videos organized together — one place for every campaign or client.') }}</p>

                        @if($atLimit)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('You have reached your project limit (:limit).', ['limit' => $projectLimit]) }}
                                @if($upgradeUrl)
                                    <a href="{{ $upgradeUrl }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline">{{ __('Upgrade your plan') }}</a>
                                @endif
                            </p>
                        @else
                            <flux:modal.trigger name="create-project">
                                <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm shadow-indigo-500/25 hover:shadow-xl transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                    <flux:icon.plus class="size-4" /> {{ __('Create new Project') }}
                                </button>
                            </flux:modal.trigger>
                        @endif
                    </div>
                </div>
            @else
                {{-- Project cards grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <a wire:key="project-{{ $project->id }}"
                           href="{{ \Illuminate\Support\Facades\Route::has('user.projects.show') ? route('user.projects.show', $project->id) : '#' }}"
                           @if(\Illuminate\Support\Facades\Route::has('user.projects.show')) wire:navigate @endif
                           class="group relative block rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden p-5 transition-colors duration-300 hover:border-indigo-300 dark:hover:border-indigo-700/60">
                            <div class="flex items-start gap-3">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shrink-0">
                                    <flux:icon.folder class="size-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $project->name }}</h3>
                                    @if($project->description)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">{{ $project->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-1.5 text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.rectangle-stack class="size-3.5" />
                                <span>{{ trans_choice('{0} No creatives|{1} :count creative|[2,*] :count creatives', $project->creative_count, ['count' => $project->creative_count]) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Create project modal --}}
    <flux:modal name="create-project" class="md:w-[460px]">
        <form wire:submit="create" class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('New Project') }}</flux:heading>
                <flux:subheading>{{ __('Name your project and add an optional description.') }}</flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" type="text" maxlength="120" placeholder="{{ __('e.g. Summer Campaign') }}" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Description') }} <span class="text-zinc-400 font-normal">({{ __('optional') }})</span></flux:label>
                <flux:textarea wire:model="description" rows="3" maxlength="1000" placeholder="{{ __('What is this project about?') }}" />
                <flux:error name="description" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="create">
                    {{ __('Create Project') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
