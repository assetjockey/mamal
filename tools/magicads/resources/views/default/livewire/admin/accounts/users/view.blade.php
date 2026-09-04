<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-10/12 space-y-6 pb-10">

            @php $sub = $user->activeSubscription; @endphp

            {{-- Breadcrumbs --}}
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('admin.accounts.list') }}" separator="slash" class="text-xs">{{ __('Users') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $user->name }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            {{-- ═══════════════════════════════════════════════════════
                 HERO BANNER
            ═══════════════════════════════════════════════════════ --}}
            <div class="rounded-3xl overflow-hidden mt-8 mb-10 border border-(--default-border-color)">

                {{-- Cover: layered gradient + geometric SVG shapes --}}
                <div data-user-cover class="relative h-36 overflow-hidden">
                    <style>
                        /* Match the Brand Kit "command deck" hero. */
                        /* Light mode: zinc-950 base with an indigo + amber radial wash. */
                        [data-user-cover] {
                            background-color: #09090b; /* zinc-950 */
                            background-image:
                                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(79,70,229,0.22), transparent),
                                radial-gradient(ellipse 80% 50% at 110% 110%, rgba(245,158,11,0.14), transparent);
                        }
                        /* Dark mode: near-black diagonal gradient, same as the dashboard hero. */
                        .dark [data-user-cover] {
                            background-color: #000000;
                            background-image: linear-gradient(to bottom right, #0b0b11, #070709, #000000);
                        }
                    </style>

                    {{-- Brand glow blobs (indigo + amber, matching the brand kit wash) --}}
                    <div class="absolute -top-24 -right-16 w-96 h-96 rounded-full bg-indigo-500/15 blur-[120px] pointer-events-none"></div>
                    <div class="absolute -bottom-24 -left-16 w-96 h-96 rounded-full bg-amber-500/10 blur-[120px] pointer-events-none"></div>

                    {{-- Top shimmer line --}}
                    <div class="absolute top-0 inset-x-0 h-px bg-linear-to-r from-transparent via-indigo-400/60 to-transparent"></div>

                    {{-- Status / group / subscription badges (dark-cover variants) --}}
                    @php
                        $statusMap = [
                            'active'    => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/20',
                            'suspended' => 'bg-rose-500/15 text-rose-300 ring-rose-400/20',
                            'inactive'  => 'bg-rose-500/15 text-rose-300 ring-rose-400/20',
                            'pending'   => 'bg-amber-500/15 text-amber-300 ring-amber-400/20',
                        ];
                        $groupMap = [
                            'admin'      => 'bg-rose-500/15 text-rose-300 ring-rose-400/20',
                            'subscriber' => 'bg-indigo-500/15 text-indigo-300 ring-indigo-400/20',
                            'user'       => 'bg-white/10 text-zinc-300 ring-white/15',
                        ];
                    @endphp
                    <div class="absolute top-4 left-5 flex items-center gap-2 flex-wrap max-w-[55%]">
                        @if(\App\Services\HelperService::extensionSaaS())
                            @if($sub)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/20 backdrop-blur-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    {{ __('Subscribed') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-white/10 text-zinc-300 ring-1 ring-white/15 backdrop-blur-sm">
                                    {{ __('Free Trial') }}
                                </span>
                            @endif
                        @endif
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full ring-1 backdrop-blur-sm {{ $statusMap[$user->status] ?? 'bg-white/10 text-zinc-300 ring-white/15' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full ring-1 backdrop-blur-sm {{ $groupMap[$user->group] ?? 'bg-white/10 text-zinc-300 ring-white/15' }}">
                            {{ ucfirst($user->group) }}
                        </span>
                    </div>

                    {{-- Top-right action buttons --}}
                    <div class="absolute top-4 right-5 flex items-center gap-2">
                        <a href="{{ route('admin.accounts.edit', $user->user_id) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl text-white transition-all shadow-md shadow-indigo-500/25 hover:shadow-lg"
                           style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                            {{ __('Edit Profile') }}
                        </a>
                        <a href="mailto:{{ $user->email }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-white/10 border border-white/15 text-indigo-200 backdrop-blur-sm hover:bg-white/20 transition-all">
                            <x-heroicon-o-envelope class="w-3.5 h-3.5" />
                            {{ __('Email') }}
                        </a>
                        <a href="{{ route('admin.accounts.list') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-white/10 border border-white/15 text-zinc-200 backdrop-blur-sm hover:bg-white/20 transition-all">
                            <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                            {{ __('Back') }}
                        </a>
                    </div>
                </div>

                {{-- Profile info bar --}}
                <div class="bg-(--default-element-bg-color) px-7 pb-5">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-8">

                        {{-- Avatar --}}
                        <div class="flex items-end gap-5">
                            <div class="relative shrink-0">
                                <img src="{{ URL::asset($user->avatar) ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=000000&color=fff&size=200' }}"
                                     class="w-28 h-28 rounded-full border-2 border-(--default-element-bg-color) shadow-lg object-cover" />
                                @if($sub)
                                    <span class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-(--default-element-bg-color) shadow"></span>
                                @endif
                            </div>

                            <div class="mb-2 pt-16 sm:pt-0">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h2 class="text-xl font-bold tracking-tight">{{ $user->name }}</h2>
                                </div>
                                <p class="text-sm text-gray-400 mt-0.5 flex items-center gap-1.5">
                                    <x-heroicon-o-envelope class="w-3.5 h-3.5" />
                                    {{ $user->email }}
                                    @if($user->city || $user->country)
                                        <span class="text-gray-300">·</span>
                                        <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                                        {{ collect([$user->city, $user->country])->filter()->implode(', ') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Quick meta --}}
                        <div class="flex items-center gap-6 text-center sm:mb-1 pb-1">
                            <div>
                                <p class="text-lg font-bold">{{ number_format($user->credits ?? 0) }}</p>
                                <p class="text-xs text-gray-400">{{ __('Credits') }}</p>
                            </div>
                            <div class="w-px h-8 bg-(--default-border-color)"></div>
                            @if(\App\Services\HelperService::extensionSaaS())
                            <div>
                                <p class="text-lg font-bold">${{ number_format($user->balance ?? 0, 2) }}</p>
                                <p class="text-xs text-gray-400">{{ __('Balance') }}</p>
                            </div>
                            <div class="w-px h-8 bg-(--default-border-color)"></div>
                            @endif
                            <div>
                                <p class="text-lg font-bold">{{ $user->created_at->format('M Y') }}</p>
                                <p class="text-xs text-gray-400">{{ __('Member since') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- ═══════════════════════════════════════════════════════
                 MAIN GRID
            ═══════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-7">

                {{-- ── LEFT COLUMN ── --}}
                <div class="space-y-7">

                    {{-- Profile Details --}}
                    <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) p-6">
                        <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">
                            <x-heroicon-o-user-circle class="w-4 h-4 text-(--default-primary-color)" />
                            {{ __('Profile Details') }}
                        </h3>
                        <ul class="space-y-3 text-sm">
                            @if($user->company)
                                <li class="flex items-center gap-3 text-gray-600">
                                    <x-heroicon-o-building-office class="w-4 h-4 shrink-0 text-gray-300" />
                                    {{ $user->company }}
                                </li>
                            @endif
                            <li class="flex items-center gap-3 text-gray-600">
                                <x-heroicon-o-envelope class="w-4 h-4 shrink-0 text-gray-300" />
                                {{ $user->email }}
                            </li>
                            @if($user->phone_number)
                                <li class="flex items-center gap-3 text-gray-600">
                                    <x-heroicon-o-phone class="w-4 h-4 shrink-0 text-gray-300" />
                                    {{ $user->phone_number }}
                                </li>
                            @endif
                            @if($user->website)
                                <li class="flex items-center gap-3 text-gray-600">
                                    <x-heroicon-o-globe-alt class="w-4 h-4 shrink-0 text-gray-300" />
                                    <a href="{{ $user->website }}" target="_blank" class="hover:text-(--default-primary-color) hover:underline truncate">{{ $user->website }}</a>
                                </li>
                            @endif
                            @if($user->address || $user->city || $user->country)
                                <li class="flex items-start gap-3 text-gray-600">
                                    <x-heroicon-o-map-pin class="w-4 h-4 shrink-0 text-gray-300 mt-0.5" />
                                    {{ collect([$user->address, $user->city, $user->postal_code, $user->country])->filter()->implode(', ') }}
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Account Info --}}
                    <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) p-6">
                        <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">
                            <x-heroicon-o-shield-check class="w-4 h-4 text-(--default-primary-color)" />
                            {{ __('Account Info') }}
                        </h3>
                        <ul class="space-y-3 text-sm divide-y divide-(--default-border-color)">
                            <li class="flex justify-between py-2 first:pt-0 last:pb-0">
                                <span class="text-gray-400">{{ __('User ID') }}</span>
                                <span class="font-mono text-xs font-medium text-gray-500">#{{ $user->user_id }}</span>
                            </li>
                            <li class="flex justify-between py-2 first:pt-0 last:pb-0">
                                <span class="text-gray-400">{{ __('Referral ID') }}</span>
                                <span class="font-mono text-xs font-medium text-gray-500">{{ $user->referral_id }}</span>
                            </li>
                            <li class="flex justify-between py-2">
                                <span class="text-gray-400">{{ __('Joined') }}</span>
                                <span class="font-medium">{{ $user->created_at->format('M j, Y') }}</span>
                            </li>
                            <li class="flex justify-between py-2">
                                <span class="text-gray-400">{{ __('Last Seen') }}</span>
                                <span class="font-medium">{{ $user->last_seen ? $user->last_seen->diffForHumans() : __('Never') }}</span>
                            </li>
                            <li class="flex justify-between py-2">
                                <span class="text-gray-400">{{ __('2FA') }}</span>
                                <span class="font-medium {{ $user->google2fa_enabled ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $user->google2fa_enabled ? __('Enabled') : __('Disabled') }}
                                </span>
                            </li>
                            <li class="flex justify-between py-2 last:pb-0">
                                <span class="text-gray-400">{{ __('Email Opt-in') }}</span>
                                <span class="font-medium {{ $user->email_opt_in ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $user->email_opt_in ? __('Yes') : __('No') }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    {{-- Subscription --}}
                    @if(\App\Services\HelperService::extensionSaaS())
                    <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) p-6">
                        <h3 class="font-semibold text-sm mb-4 flex items-center gap-2">
                            <x-heroicon-o-credit-card class="w-4 h-4 text-(--default-primary-color)" />
                            {{ __('Subscription') }}
                        </h3>
                        @if($sub)
                            <div class="rounded-xl bg-gradient-to-br from-violet-50 to-indigo-50 dark:from-indigo-500/10 dark:to-violet-500/10 border border-violet-100 dark:border-indigo-500/20 p-4 space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">{{ __('Plan') }}</span>
                                    <span class="font-bold text-violet-700 dark:text-indigo-300">{{ $sub->plan->name ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">{{ __('Status') }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ ucfirst($sub->status) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">{{ __('Renews') }}</span>
                                    <span class="font-medium">{{ $sub->active_until?->format('M j, Y') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-dashed border-(--default-border-color) p-4 text-center">
                                <x-heroicon-o-credit-card class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                                <p class="text-sm text-gray-400">{{ __('No active subscription') }}</p>
                            </div>
                        @endif
                    </div>
                    @endif

                </div>

                {{-- ── RIGHT COLUMN ── --}}
                <div class="lg:col-span-2 space-y-7">

                    {{-- Recent Orders --}}
                    @if(\App\Services\HelperService::extensionSaaS())
                    <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-(--default-border-color)">
                            <h3 class="font-semibold text-sm flex items-center gap-2">
                                <x-heroicon-o-shopping-bag class="w-4 h-4 text-(--default-primary-color)" />
                                {{ __('Recent Orders') }}
                            </h3>
                            <a href="{{ route('admin.finance.orders') }}" class="text-xs hover:underline font-medium">{{ __('View all →') }}</a>
                        </div>
                        @if($user->orders->isNotEmpty())
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-(--default-element-light-bg-color) text-left text-xs text-gray-400 uppercase tracking-wide">
                                        <th class="px-6 py-3 font-semibold">{{ __('Order') }}</th>
                                        <th class="px-6 py-3 font-semibold">{{ __('Amount') }}</th>
                                        <th class="px-6 py-3 font-semibold">{{ __('Status') }}</th>
                                        <th class="px-6 py-3 font-semibold">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-(--default-border-color)">
                                    @foreach($user->orders as $order)
                                        @php $oc = ['completed' => 'bg-green-50 text-green-700 dark:bg-emerald-500/15 dark:text-emerald-300 ring-green-200', 'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 ring-amber-200', 'failed' => 'bg-red-50 text-red-700 dark:bg-rose-500/15 dark:text-rose-300 ring-red-200']; @endphp
                                        <tr class="hover:bg-(--default-element-light-bg-color) transition-colors">
                                            <td class="px-6 py-3.5 font-mono text-xs text-gray-400">#{{ $order->id }}</td>
                                            <td class="px-6 py-3.5 font-bold">${{ number_format($order->total ?? 0, 2) }}</td>
                                            <td class="px-6 py-3.5">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 {{ $oc[$order->status] ?? 'bg-gray-50 text-gray-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300 ring-gray-200' }}">{{ ucfirst($order->status) }}</span>
                                            </td>
                                            <td class="px-6 py-3.5 text-gray-400 text-xs">{{ $order->created_at->format('M j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-6 py-10 text-center">
                                <x-heroicon-o-shopping-bag class="w-8 h-8 text-gray-200 mx-auto mb-2" />
                                <p class="text-sm text-gray-400">{{ __('No orders yet.') }}</p>
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- Support Tickets --}}
                    <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-(--default-border-color)">
                            <h3 class="font-semibold text-sm flex items-center gap-2">
                                <x-heroicon-o-ticket class="w-4 h-4 text-(--default-primary-color)" />
                                {{ __('Support Tickets') }}
                            </h3>
                            <a href="{{ route('admin.support.tickets') }}" class="text-xs hover:underline font-medium">{{ __('View all →') }}</a>
                        </div>
                        @if($user->supportTickets->isNotEmpty())
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-(--default-element-light-bg-color) text-left text-xs text-gray-400 uppercase tracking-wide">
                                        <th class="px-6 py-3 font-semibold">{{ __('Subject') }}</th>
                                        <th class="px-6 py-3 font-semibold">{{ __('Category') }}</th>
                                        <th class="px-6 py-3 font-semibold">{{ __('Priority') }}</th>
                                        <th class="px-6 py-3 font-semibold">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-(--default-border-color)">
                                    @foreach($user->supportTickets as $ticket)
                                        @php
                                            $pc = ['high' => 'bg-red-50 text-red-700 dark:bg-rose-500/15 dark:text-rose-300 ring-red-200', 'medium' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 ring-amber-200', 'low' => 'bg-gray-50 text-gray-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300 ring-gray-200'];
                                            $tc = ['open' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300 ring-blue-200', 'closed' => 'bg-gray-50 text-gray-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300 ring-gray-200', 'resolved' => 'bg-green-50 text-green-700 dark:bg-emerald-500/15 dark:text-emerald-300 ring-green-200'];
                                        @endphp
                                        <tr class="hover:bg-(--default-element-light-bg-color) transition-colors">
                                            <td class="px-6 py-3.5">
                                                <a href="{{ route('admin.support.tickets.view', $ticket->ticket_id) }}" class="font-medium hover:text-violet-600 hover:underline">
                                                    {{ Str::limit($ticket->subject, 38) }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-3.5 text-gray-400 text-xs">{{ ucfirst($ticket->category) }}</td>
                                            <td class="px-6 py-3.5">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 {{ $pc[$ticket->priority] ?? 'bg-gray-50 text-gray-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300 ring-gray-200' }}">{{ ucfirst($ticket->priority) }}</span>
                                            </td>
                                            <td class="px-6 py-3.5">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 {{ $tc[$ticket->status] ?? 'bg-gray-50 text-gray-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300 ring-gray-200' }}">{{ ucfirst($ticket->status) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="px-6 py-10 text-center">
                                <x-heroicon-o-ticket class="w-8 h-8 text-gray-200 mx-auto mb-2" />
                                <p class="text-sm text-gray-400">{{ __('No support tickets.') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Danger Zone --}}
                    <div class="rounded-2xl border border-red-100 dark:border-rose-500/20 bg-red-50/40 dark:bg-rose-500/5 p-6">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-rose-500/15 flex items-center justify-center shrink-0">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500" />
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-sm text-red-700 dark:text-rose-300 mb-0.5">{{ __('Danger Zone') }}</h3>
                                <p class="text-xs text-red-400 dark:text-rose-400/80 mb-4">{{ __('These actions are permanent and cannot be undone.') }}</p>
                                <flux:modal.trigger name="confirm-delete">
                                    <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-delete')" class="inline-flex items-center gap-1.5">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                        {{ __('Delete User') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <flux:modal name="confirm-delete" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete User') }}</flux:heading>
                <flux:subheading>{{ __('Are you sure you want to delete') }} <strong>{{ $user->name }}</strong>? {{ __('This action cannot be undone.') }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
