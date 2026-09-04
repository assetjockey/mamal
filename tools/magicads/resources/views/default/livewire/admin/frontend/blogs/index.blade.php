<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-10/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Blogs Manager') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9 flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-bold text-2xl">{{ __('Blogs Manager') }}</h1>
                    <flux:subheading size="md">{{ __('Create and manage the posts that appear on your public blog') }}</flux:subheading>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <flux:button :href="route('admin.frontend.blog-comments')" wire:navigate icon="chat-bubble-left-right" variant="filled" class="cursor-pointer">
                        {{ __('Comments') }}
                    </flux:button>
                    <flux:button :href="route('admin.frontend.blogs.create')" wire:navigate variant="primary" icon="plus" class="cursor-pointer">
                        {{ __('New Post') }}
                    </flux:button>
                </div>
            </div>

            <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search posts...') }}" class="flex-1" />
                <flux:select wire:model.live="statusFilter" class="sm:max-w-48">
                    <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                    <flux:select.option value="published">{{ __('Published') }}</flux:select.option>
                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                @if ($posts->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-white"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                            <flux:icon.newspaper class="size-6" />
                        </span>
                        <flux:heading size="lg">{{ __('No blog posts yet') }}</flux:heading>
                        <flux:subheading>{{ __('Write your first post to publish it on the blog.') }}</flux:subheading>
                        <flux:button :href="route('admin.frontend.blogs.create')" wire:navigate variant="primary" icon="plus" class="mt-2 cursor-pointer">
                            {{ __('New Post') }}
                        </flux:button>
                    </div>
                @else
                    <flux:table :paginate="$posts">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Post') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column>{{ __('Comments') }}</flux:table.column>
                            <flux:table.column>{{ __('Published') }}</flux:table.column>
                            <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($posts as $post)
                                <flux:table.row :key="$post->id">
                                    <flux:table.cell class="max-w-sm">
                                        <div class="flex items-center gap-3">
                                            @if ($post->featured_image_url)
                                                <img src="{{ $post->featured_image_url }}" alt="" class="h-10 w-14 shrink-0 rounded-md object-cover border border-(--default-border-color) dark:border-white/8" />
                                            @else
                                                <span class="inline-flex h-10 w-14 shrink-0 items-center justify-center rounded-md text-white"
                                                      style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                                                    <flux:icon.photo class="size-4" />
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    @if ($post->is_featured)
                                                        <flux:icon.star variant="solid" class="size-3.5 text-amber-500" />
                                                    @endif
                                                    <span class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $post->title }}</span>
                                                </div>
                                                <p class="mt-0.5 truncate text-xs text-zinc-400">
                                                    {{ $post->category ?: __('Uncategorized') }} · {{ $post->reading_time_minutes }} {{ __('min') }} · {{ number_format($post->view_count) }} {{ __('views') }}
                                                </p>
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @switch($post->status)
                                            @case('published')
                                                <flux:badge color="emerald" size="sm">{{ __('Published') }}</flux:badge>
                                                @break
                                            @case('archived')
                                                <flux:badge color="amber" size="sm">{{ __('Archived') }}</flux:badge>
                                                @break
                                            @default
                                                <flux:badge color="zinc" size="sm">{{ __('Draft') }}</flux:badge>
                                        @endswitch
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $post->comments_count }}</span>
                                            @if ($post->pending_comments_count > 0)
                                                <flux:badge color="amber" size="sm">{{ $post->pending_comments_count }} {{ __('pending') }}</flux:badge>
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="text-sm text-zinc-500">
                                            {{ $post->published_at ? $post->published_at->format('M j, Y') : '—' }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Feature toggle --}}
                                            <flux:tooltip content="{{ $post->is_featured ? __('Featured — click to unfeature') : __('Mark as featured') }}">
                                                <button type="button" wire:click="toggleFeatured({{ $post->id }})"
                                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg transition-colors
                                                               {{ $post->is_featured
                                                                    ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10'
                                                                    : 'text-zinc-400 hover:bg-amber-50 hover:text-amber-500 dark:text-zinc-500 dark:hover:bg-amber-500/10' }}">
                                                    @if ($post->is_featured)
                                                        <flux:icon.star variant="solid" class="size-[18px]" />
                                                    @else
                                                        <flux:icon.star variant="outline" class="size-[18px]" />
                                                    @endif
                                                </button>
                                            </flux:tooltip>

                                            {{-- View on site --}}
                                            @if ($post->status === 'published')
                                                <flux:tooltip content="{{ __('View on site') }}">
                                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener"
                                                       class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-emerald-50 hover:text-emerald-600 dark:text-zinc-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
                                                        <flux:icon.arrow-top-right-on-square variant="outline" class="size-[18px]" />
                                                    </a>
                                                </flux:tooltip>
                                            @endif

                                            {{-- Edit --}}
                                            <flux:tooltip content="{{ __('Edit post') }}">
                                                <a href="{{ route('admin.frontend.blogs.edit', $post->id) }}" wire:navigate
                                                   class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400">
                                                    <flux:icon.pencil-square variant="outline" class="size-[18px]" />
                                                </a>
                                            </flux:tooltip>

                                            {{-- Delete --}}
                                            <flux:tooltip content="{{ __('Delete post') }}">
                                                <button type="button"
                                                        x-on:click="$wire.deleteId = {{ $post->id }}; $wire.showDeleteModal = true"
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

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model="showDeleteModal" name="confirm-post-deletion" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete Post') }}</flux:heading>
                <flux:subheading>{{ __('This permanently deletes the post and all of its comments. This cannot be undone.') }}</flux:subheading>
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
