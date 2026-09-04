<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Notifications')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('System Notifications')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="font-bold text-2xl">{{ __('System Notifications') }}</h1>
                    <flux:subheading size="md" class="mb-6">{{ __('Manage system notifications') }}</flux:subheading>
                </div>
                @if($notifications->where('read_at', null)->count())
                    <flux:button wire:click="markAllAsRead" size="sm" variant="primary" class="hover:bg-blue-500 py-5 px-5 rounded-xl cursor-pointer">
                        {{ __('Mark all as read') }}
                    </flux:button>
                @endif
            </div>

            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <div @class([
                        'border rounded-2xl p-4 flex items-start gap-4 transition-colors',
                        'border-(--default-border-color) dark:border-white/8 dark:bg-(--default-element-light-bg-color)',
                        'bg-(--default-element-light-bg-color) dark:bg-blue-950/30 border-blue-200 dark:border-blue-800' => is_null($notification->read_at),
                    ])>
                        <div class="flex-shrink-0 mt-0.5">
                            @if(str_contains($notification->type, 'UserRegistered'))
                                <flux:icon.user-plus class="w-5 h-5 text-blue-500" />
                            @else
                                <flux:icon.credit-card class="w-5 h-5 text-green-500" />
                            @endif
                        </div>

                        <div class="flex-grow min-w-0">
                            <p class="text-sm font-semibold text-neutral-900 dark:text-white">
                                {{ $notification->data['title'] }}
                            </p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                                {{ $notification->data['message'] }}
                            </p>
                            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if(isset($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}" class="text-xs text-blue-500 hover:underline">
                                    {{ __('View') }}
                                </a>
                            @endif
                            @if(is_null($notification->read_at))
                                <flux:button wire:click="markAsRead('{{ $notification->id }}')" size="xs" variant="ghost">
                                    {{ __('Mark read') }}
                                </flux:button>
                            @endif
                            <flux:button wire:click="delete('{{ $notification->id }}')" wire:confirm="{{ __('Delete this notification?') }}" size="xs" variant="ghost" class="text-red-500">
                                <flux:icon.trash class="w-4 h-4" />
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="border border-(--default-border-color) dark:border-white/8 dark:bg-(--default-element-light-bg-color) rounded-2xl p-12 text-center">
                        <flux:icon.bell-off class="w-10 h-10 text-neutral-400 mx-auto mb-3" />
                        <p class="text-neutral-500 dark:text-neutral-400">{{ __('No notifications yet.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>
