<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-10/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('admin.frontend.blogs')" separator="slash" class="text-xs">{{ __('Blogs Manager') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Comments') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9 flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-bold text-2xl">{{ __('Blog Comments') }}</h1>
                    <flux:subheading size="md">{{ __('Moderate, approve and remove comments left by your readers') }}</flux:subheading>
                </div>
                <flux:button icon="chevron-left" :href="route('admin.frontend.blogs')" wire:navigate variant="filled" class="cursor-pointer shrink-0">
                    {{ __('Back to Posts') }}
                </flux:button>
            </div>

            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by author or content...') }}" class="flex-1" />
                <flux:select wire:model.live="statusFilter" class="sm:max-w-52">
                    <flux:select.option value="">{{ __('All') }}</flux:select.option>
                    <flux:select.option value="pending">{{ __('Pending') }} ({{ $counts['pending'] }})</flux:select.option>
                    <flux:select.option value="approved">{{ __('Approved') }} ({{ $counts['approved'] }})</flux:select.option>
                    <flux:select.option value="spam">{{ __('Spam') }} ({{ $counts['spam'] }})</flux:select.option>
                    <flux:select.option value="rejected">{{ __('Rejected') }} ({{ $counts['rejected'] }})</flux:select.option>
                </flux:select>
            </div>

            <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                @if ($comments->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-white"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                            <flux:icon.chat-bubble-left-right class="size-6" />
                        </span>
                        <flux:heading size="lg">{{ __('No comments here') }}</flux:heading>
                        <flux:subheading>{{ __('Comments matching this filter will appear here.') }}</flux:subheading>
                    </div>
                @else
                    <div class="flex flex-col gap-3">
                        @foreach ($comments as $comment)
                            <div class="rounded-xl border border-(--default-border-color) p-4 dark:border-white/8" wire:key="comment-{{ $comment->id }}">
                                <div class="flex items-start gap-3">
                                    <img src="{{ $comment->avatar_url }}" alt="" class="h-9 w-9 shrink-0 rounded-full bg-black/[0.05]" loading="lazy" />
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $comment->name }}</span>
                                            <span class="text-xs text-zinc-400">{{ $comment->email }}</span>

                                            @switch($comment->status)
                                                @case('approved')
                                                    <flux:badge color="emerald" size="sm">{{ __('Approved') }}</flux:badge>
                                                    @break
                                                @case('spam')
                                                    <flux:badge color="rose" size="sm">{{ __('Spam') }}</flux:badge>
                                                    @break
                                                @case('rejected')
                                                    <flux:badge color="rose" size="sm">{{ __('Rejected') }}</flux:badge>
                                                    @break
                                                @default
                                                    <flux:badge color="amber" size="sm">{{ __('Pending') }}</flux:badge>
                                            @endswitch

                                            @if ($comment->parent_id)
                                                <flux:badge color="zinc" size="sm">{{ __('Reply') }}</flux:badge>
                                            @endif
                                        </div>

                                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ $comment->content }}</p>

                                        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-zinc-400">
                                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                                            @if ($comment->post)
                                                <span aria-hidden="true">·</span>
                                                <span>{{ __('on') }}</span>
                                                <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                    {{ \Illuminate\Support\Str::limit($comment->post->title, 50) }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 flex-col items-end gap-1.5">
                                        <div class="flex items-center gap-1">
                                            @if ($comment->status !== 'approved')
                                                <flux:tooltip content="{{ __('Approve') }}">
                                                    <button type="button" wire:click="setStatus({{ $comment->id }}, 'approved')"
                                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-emerald-50 hover:text-emerald-600 dark:text-zinc-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
                                                        <flux:icon.check-circle variant="outline" class="size-[18px]" />
                                                    </button>
                                                </flux:tooltip>
                                            @endif
                                            @if ($comment->status !== 'pending')
                                                <flux:tooltip content="{{ __('Mark pending') }}">
                                                    <button type="button" wire:click="setStatus({{ $comment->id }}, 'pending')"
                                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-amber-50 hover:text-amber-600 dark:text-zinc-400 dark:hover:bg-amber-500/10 dark:hover:text-amber-400">
                                                        <flux:icon.clock variant="outline" class="size-[18px]" />
                                                    </button>
                                                </flux:tooltip>
                                            @endif
                                            @if ($comment->status !== 'spam')
                                                <flux:tooltip content="{{ __('Mark as spam') }}">
                                                    <button type="button" wire:click="setStatus({{ $comment->id }}, 'spam')"
                                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-orange-50 hover:text-orange-600 dark:text-zinc-400 dark:hover:bg-orange-500/10 dark:hover:text-orange-400">
                                                        <flux:icon.no-symbol variant="outline" class="size-[18px]" />
                                                    </button>
                                                </flux:tooltip>
                                            @endif
                                            <flux:tooltip content="{{ __('Delete') }}">
                                                <button type="button"
                                                        x-on:click="$wire.deleteId = {{ $comment->id }}; $wire.showDeleteModal = true"
                                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-zinc-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                                    <flux:icon.trash variant="outline" class="size-[18px]" />
                                                </button>
                                            </flux:tooltip>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        {{ $comments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model="showDeleteModal" name="confirm-comment-deletion" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete Comment') }}</flux:heading>
                <flux:subheading>{{ __('This permanently deletes the comment and any replies to it. This cannot be undone.') }}</flux:subheading>
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
