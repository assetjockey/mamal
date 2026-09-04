<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-9/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Testimonials') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9 flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-bold text-2xl">{{ __('Testimonials') }}</h1>
                    <flux:subheading size="md">{{ __('Manage the customer testimonials shown on the landing page') }}</flux:subheading>
                </div>
                <flux:button wire:click="create" variant="primary" icon="plus" class="cursor-pointer shrink-0">
                    {{ __('Add Testimonial') }}
                </flux:button>
            </div>

            <div class="mb-6">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by name, company or content...') }}" />
            </div>

            <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                @if ($testimonials->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-white"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                            <flux:icon.chat-bubble-bottom-center-text class="size-6" />
                        </span>
                        <flux:heading size="lg">{{ __('No testimonials yet') }}</flux:heading>
                        <flux:subheading>{{ __('Add your first testimonial to populate the landing page.') }}</flux:subheading>
                        <flux:button wire:click="create" variant="primary" icon="plus" class="mt-2 cursor-pointer">
                            {{ __('Add Testimonial') }}
                        </flux:button>
                    </div>
                @else
                    <flux:table :paginate="$testimonials">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Customer') }}</flux:table.column>
                            <flux:table.column>{{ __('Rating') }}</flux:table.column>
                            <flux:table.column>{{ __('Featured') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($testimonials as $item)
                                <flux:table.row :key="$item->id">
                                    <flux:table.cell class="max-w-sm">
                                        <div class="flex items-center gap-3">
                                            @if ($item->avatar_url)
                                                <img src="{{ $item->avatar_url }}" alt="{{ $item->name }}" class="h-10 w-10 shrink-0 rounded-full object-cover border border-(--default-border-color) dark:border-white/8" />
                                            @else
                                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                                                      style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">{{ $item->initials }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <span class="block truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $item->name }}</span>
                                                <p class="truncate text-xs text-zinc-400">
                                                    {{ collect([$item->role, $item->company])->filter()->implode(', ') ?: __('No role set') }}
                                                </p>
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-0.5" aria-label="{{ $item->stars }} {{ __('out of 5') }}">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <flux:icon.star variant="solid" class="size-4 {{ $i <= $item->stars ? 'text-amber-500' : 'text-zinc-300 dark:text-zinc-600' }}" />
                                            @endfor
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($item->featured)
                                            <flux:badge color="zinc" size="sm" icon="star">{{ __('Featured') }}</flux:badge>
                                        @else
                                            <span class="text-sm text-zinc-400">—</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($item->status === 'active')
                                            <flux:badge color="emerald" size="sm">{{ __('Active') }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <flux:tooltip content="{{ __('Edit') }}">
                                                <button type="button" wire:click="edit({{ $item->id }})"
                                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400">
                                                    <flux:icon.pencil-square variant="outline" class="size-[18px]" />
                                                </button>
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Delete') }}">
                                                <button type="button"
                                                        x-on:click="$wire.deleteId = {{ $item->id }}; $wire.showDeleteModal = true"
                                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-zinc-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                                    <flux:icon.trash variant="outline" class="size-[18px]" />
                                                </button>
                                            </flux:tooltip>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================
         Create / edit testimonial modal
         ============================================================ --}}
    <flux:modal wire:model="showModal" name="testimonial-form" class="max-w-xl w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $testimonialId ? __('Edit Testimonial') : __('Add Testimonial') }}</flux:heading>
                <flux:subheading>{{ __('This content appears in the testimonials section of the landing page.') }}</flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('Testimonial') }}</flux:label>
                <flux:textarea wire:model="testimonial" rows="4" placeholder="{{ __('What did the customer say?') }}" />
                <flux:error name="testimonial" />
            </flux:field>

            {{-- Star rating selector --}}
            <flux:field>
                <flux:label>{{ __('Rating') }}</flux:label>
                <div class="flex items-center gap-1" x-data>
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" wire:click.prevent="$set('stars', {{ $i }})"
                                class="cursor-pointer p-0.5"
                                aria-label="{{ $i }} {{ __('stars') }}">
                            <flux:icon.star variant="solid" class="size-7 transition-colors {{ $i <= $stars ? 'text-amber-500' : 'text-zinc-300 dark:text-zinc-600' }}" />
                        </button>
                    @endfor
                    <span class="ml-2 text-sm text-zinc-500">{{ $stars }}/5</span>
                </div>
                <flux:error name="stars" />
            </flux:field>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="name" placeholder="{{ __('e.g. Jordan Alvarez') }}" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Role') }}</flux:label>
                    <flux:input wire:model="role" placeholder="{{ __('e.g. Growth Lead') }}" />
                    <flux:error name="role" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Company') }}</flux:label>
                <flux:input wire:model="company" placeholder="{{ __('e.g. Lumen Labs') }}" />
                <flux:error name="company" />
            </flux:field>

            {{-- Avatar upload --}}
            <flux:field>
                <flux:label>{{ __('Avatar') }} <span class="font-normal text-zinc-400">({{ __('optional') }})</span></flux:label>
                <div class="flex items-center gap-4">
                    <div class="shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="preview" class="h-14 w-14 rounded-full object-cover border border-(--default-border-color) dark:border-white/8" />
                        @elseif ($avatar_path)
                            <img src="{{ \Illuminate\Support\Str::startsWith($avatar_path, 'http') ? $avatar_path : URL::asset($avatar_path) }}" alt="current avatar" class="h-14 w-14 rounded-full object-cover border border-(--default-border-color) dark:border-white/8" />
                        @else
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-full text-base font-bold text-white"
                                  style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">{{ \Illuminate\Support\Str::of($name)->trim()->explode(' ')->take(2)->map(fn ($p) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)))->implode('') ?: 'A' }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <flux:input type="file" wire:model="avatar" accept="image/*" />
                        <flux:description>{{ __('Leave empty to use the initials of the name. JPG/PNG, max 2MB.') }}</flux:description>
                        @if ($avatar_path && ! $avatar)
                            <button type="button" wire:click="removeAvatar" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:underline dark:text-rose-400">
                                <flux:icon.trash class="size-3" /> {{ __('Remove avatar') }}
                            </button>
                        @endif
                    </div>
                </div>
                <flux:error name="avatar" />
            </flux:field>

            {{-- Featured toggle --}}
            <div class="md:border border-(--default-border-color) md:p-5 rounded-xl dark:border-white/8 dark:bg-(--default-element-bg-color)">
                <flux:field variant="inline">
                    <flux:label>{{ __('Featured') }}</flux:label>
                    <flux:description><small>{{ __('Featured testimonials render as a full-black (ink) card to stand out.') }}</small></flux:description>
                    <flux:switch wire:model.live="featured" />
                    <flux:error name="featured" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status">
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>
                <flux:description>{{ __('Only active testimonials are shown on the landing page.') }}</flux:description>
                <flux:error name="status" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-white/6">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $testimonialId ? __('Update Testimonial') : __('Create Testimonial') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ============================================================
         Delete confirmation modal
         ============================================================ --}}
    <flux:modal wire:model="showDeleteModal" name="confirm-testimonial-deletion" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete Testimonial') }}</flux:heading>
                <flux:subheading>{{ __('This action is permanent and cannot be undone.') }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
