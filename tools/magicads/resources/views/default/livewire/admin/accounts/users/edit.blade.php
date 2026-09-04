<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-8/12 space-y-6 pb-10">

            {{-- Breadcrumbs --}}
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('admin.accounts.list') }}" separator="slash" class="text-xs">{{ __('Users') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('admin.accounts.view', $user->user_id) }}" separator="slash" class="text-xs">{{ $user->name }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Edit') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            {{-- Page header --}}
            <div class="flex items-center justify-between mt-8 mb-10">
                <div class="flex items-center gap-4">
                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=000000&color=fff&size=80' }}"
                         class="w-12 h-12 rounded-xl object-cover ring-2 ring-violet-100 shadow" />
                    <div>
                        <h1 class="text-lg font-bold">{{ __('Edit Profile') }}</h1>
                        <p class="text-sm text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.accounts.view', $user->user_id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl text-white border border-(--default-border-color) bg-zinc-950 hover:bg-(--default-primary-color) transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    {{ __('Back to Profile') }}
                </a>
            </div>

            <form wire:submit="save" class="space-y-10">

                {{-- ── Personal Information ── --}}
                <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) overflow-hidden">
                    <div class="px-6 py-4 border-b border-(--default-border-color) flex items-center gap-2">
                        <x-heroicon-o-user-circle class="w-4 h-4 text-(--default-primary-color)" />
                        <h2 class="font-semibold text-sm">{{ __('Personal Information') }}</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Full Name') }} <span class="text-red-400">*</span></label>
                            <input wire:model="name" type="text"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="{{ __('Full name') }}" />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Company') }}</label>
                            <input wire:model="company" type="text"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="{{ __('Company name') }}" />
                            @error('company') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Phone Number') }}</label>
                            <input wire:model="phone_number" type="text"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="+1 234 567 890" />
                            @error('phone_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Website') }}</label>
                            <input wire:model="website" type="url"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="https://example.com" />
                            @error('website') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Address ── --}}
                <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color) overflow-hidden">
                    <div class="px-6 py-4 border-b border-(--default-border-color) flex items-center gap-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-(--default-primary-color)" />
                        <h2 class="font-semibold text-sm">{{ __('Address') }}</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Street Address') }}</label>
                            <input wire:model="address" type="text"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="{{ __('123 Main St') }}" />
                            @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('City') }}</label>
                            <input wire:model="city" type="text"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="{{ __('City') }}" />
                            @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Postal Code') }}</label>
                            <input wire:model="postal_code" type="text"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition"
                                   placeholder="10001" />
                            @error('postal_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Country') }}</label>
                            <select wire:model="country"
                                    class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition">
                                <option value="">{{ __('Select a country') }}</option>
                                @foreach(config('countries') as $code => $country)
                                    <option value="{{ $code }}">{{ $country['flagEmoji'] }} {{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Account Settings ── --}}
                <div class="rounded-2xl border border-(--default-border-color) bg-(--default-element-bg-color)">
                    <div class="px-6 py-4 border-b border-(--default-border-color) flex items-center gap-2">
                        <x-heroicon-o-shield-check class="w-4 h-4 text-(--default-primary-color)" />
                        <h2 class="font-semibold text-sm">{{ __('Account Settings') }}</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div x-data="{
                                open: false,
                                value: @entangle('group'),
                                options: [
                                    { value: 'user',       label: '{{ __('User') }}' },
                                    { value: 'admin',      label: '{{ __('Admin') }}' },
                                    { value: 'subscriber', label: '{{ __('Subscriber') }}' },
                                ],
                                get selected() { return this.options.find(o => o.value === this.value) ?? this.options[0]; }
                            }" @click.outside="open = false" class="relative">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Group') }}</label>
                            <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) transition">
                                <span x-text="selected.label">{{ ucfirst($group) }}</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition
                                 class="absolute z-20 mt-1.5 w-full rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) shadow-lg overflow-hidden p-2 space-y-0.5">
                                <template x-for="opt in options" :key="opt.value">
                                    <button type="button"
                                            @click="value = opt.value; open = false"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors hover:bg-gray-100 dark:hover:bg-white/5"
                                            :class="value === opt.value ? 'font-semibold' : 'font-normal'">
                                        <span x-text="opt.label"></span>
                                    </button>
                                </template>
                            </div>
                            @error('group') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div x-data="{
                                open: false,
                                value: @entangle('status'),
                                options: [
                                    { value: 'active',    label: '{{ __('Active') }}' },
                                    { value: 'inactive',  label: '{{ __('Inactive') }}' },
                                    { value: 'suspended', label: '{{ __('Suspended') }}' },
                                    { value: 'pending',   label: '{{ __('Pending') }}' },
                                ],
                                get selected() { return this.options.find(o => o.value === this.value) ?? this.options[0]; }
                            }" @click.outside="open = false" class="relative">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Status') }}</label>
                            <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) transition">
                                <span x-text="selected.label">{{ ucfirst($status) }}</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition
                                 class="absolute z-20 mt-1.5 w-full rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) shadow-lg overflow-hidden p-2 space-y-0.5">
                                <template x-for="opt in options" :key="opt.value">
                                    <button type="button"
                                            @click="value = opt.value; open = false"
                                            class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors hover:bg-gray-100 dark:hover:bg-white/5"
                                            :class="value === opt.value ? 'font-semibold' : 'font-normal'">
                                        <span x-text="opt.label"></span>
                                    </button>
                                </template>
                            </div>
                            @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ __('Credits') }}</label>
                            <input wire:model="credits" type="number" min="0" value="{{ $user->credits }}"
                                   class="w-full rounded-xl border border-(--default-border-color) bg-(--default-element-bg-color) px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-(--default-primary-color) focus:border-transparent transition" />
                            @error('credits') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Actions ── --}}
                <div class="flex items-center justify-center gap-3 pt-1">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">
                        <span wire:loading.remove wire:target="save">{{ __('Update') }}</span>
                        <span wire:loading wire:target="save">{{ __('Updating...') }}</span>
                    </flux:button>
                </div>

            </form>

        </div>
    </div>
</div>
