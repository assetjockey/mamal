@component(theme_view('layouts.app', 'app'), ['title' => __('Bulk Post')])
    <div class="mx-auto max-w-[1440px] space-y-6 px-4 pb-10 pt-4 sm:px-5 xl:px-6">
        <section class="overflow-hidden rounded-[1.5rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background:
            radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.12), transparent 32%),
            rgba(var(--theme-surface-base-rgb,255,255,255),0.98);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.035);
        ">
            <div class="grid gap-6 px-5 py-6 sm:px-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center">
                <div class="min-w-0">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1rem] border" style="border-color: rgba(var(--theme-accent-rgb),0.16); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                            <i class="fa-light fa-file-csv text-xl"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Bulk Publishing') }}</p>
                            <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Bulk Post') }}</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Upload one CSV, select channels, then apply campaign rules and schedule spacing in one pass.') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.64); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <div class="grid divide-y sm:grid-cols-3 sm:divide-x sm:divide-y-0" style="border-color: rgba(var(--theme-border-color-rgb),0.64); --tw-divide-opacity: 1; divide-color: rgba(var(--theme-border-color-rgb),0.64);">
                            <div class="flex items-center gap-3 px-4 py-3.5">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">1</span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Accounts') }}</p>
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose channels') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 px-4 py-3.5">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">2</span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Spreadsheet') }}</p>
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upload CSV') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 px-4 py-3.5">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">3</span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Rules') }}</p>
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Labels and interval') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem]" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                            <i class="fa-light fa-download"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('CSV template') }}</p>
                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Use the required columns before importing.') }}</p>
                        </div>
                    </div>
                    <x-ui.button :href="route('portal.bulk-posts.template')" class="mt-4 w-full justify-center" size="sm">
                        <i class="fa-light fa-file-arrow-down"></i>
                        {{ __('Download template') }}
                    </x-ui.button>
                </div>
            </div>
        </section>

        

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem] xl:items-start">
            <form method="POST" action="{{ route('portal.bulk-posts.store') }}" enctype="multipart/form-data" class="space-y-4" x-data="{ selectedAccounts: [] }">
                @csrf

                @php
                    $channelProviderRegistry = collect(channel_provider_cards())->keyBy('key');
                    $bulkChannelOptions = collect($accounts)
                        ->map(function ($account) use ($channelProviderRegistry) {
                            $provider = $channelProviderRegistry->get((string) $account->provider_key, []);
                            $providerLabel = (string) data_get($provider, 'label', str($account->provider_key)->headline());
                            $capabilityLabel = (string) ($account->category ?: __('Channel'));
                            $providerTone = publishing_provider_tone((string) $account->provider_key);

                            return [
                                'key' => (string) $account->id,
                                'label' => (string) ($account->display_name ?: ($account->username ? '@'.$account->username : strtoupper($account->provider_key))),
                                'subtitle' => trim($providerLabel.' '.str($capabilityLabel)->lower()),
                                'avatarUrl' => (string) ($account->avatar_url ?? ''),
                                'providerKey' => (string) $account->provider_key,
                                'providerLabel' => $providerLabel,
                                'providerIcon' => (string) data_get($provider, 'icon', ''),
                                'providerColor' => (string) data_get($provider, 'color', ''),
                                'providerToneSurface' => (string) ($providerTone['surface'] ?? ''),
                                'providerToneText' => (string) ($providerTone['text'] ?? ''),
                            ];
                        })
                        ->values()
                        ->all();

                    $bulkChannelNetworks = $channelProviderRegistry
                        ->only(collect($accounts)->pluck('provider_key')->filter()->unique()->values()->all())
                        ->map(function ($provider) {
                            $providerKey = (string) ($provider['key'] ?? '');
                            $providerTone = publishing_provider_tone($providerKey);

                            return [
                                'key' => $providerKey,
                                'label' => (string) ($provider['label'] ?? ''),
                                'icon' => (string) ($provider['icon'] ?? ''),
                                'color' => (string) ($provider['color'] ?? ''),
                                'toneSurface' => (string) ($providerTone['surface'] ?? ''),
                                'toneText' => (string) ($providerTone['text'] ?? ''),
                            ];
                        })
                        ->filter(fn ($provider) => $provider['key'] !== '' && $provider['label'] !== '')
                        ->values()
                        ->all();
                @endphp

                <x-ui.card class="p-0 overflow-visible">
                    <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">1</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Select channels') }}</p>
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose one or more connected social accounts for this bulk upload.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5 px-6 py-6 sm:px-8">
                        <x-ui.channel-selector
                            name="account_ids"
                            :options="$bulkChannelOptions"
                            :network-options="$bulkChannelNetworks"
                            :selected="collect((array) old('account_ids', []))->map(fn ($id) => (string) $id)->all()"
                            :label="__('Accounts')"
                            :description="__('Choose one or more connected social accounts for this bulk upload.')"
                            :error="$errors->first('account_ids') ?: $errors->first('account_ids.*')"
                            :placeholder="__('Choose one or more accounts')"
                            :empty-label="__('No matching channels found.')"
                            :multiple="true"
                            :show-network-filters="true"
                            :connect-href="route('portal.channels')"
                            :connect-label="__('Connect a channel')"
                            :inline-panel="true"
                        />
                    </div>
                </x-ui.card>

                <x-ui.card class="p-0 overflow-hidden">
                    <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">2</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upload CSV') }}</p>
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use the bulk template so your caption and schedule columns match the importer.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 px-6 py-6 sm:px-8">
                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="max-w-lg">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('CSV source file') }}</p>
                                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Upload the spreadsheet that contains the post rows. Each row becomes one scheduled publishing item for each selected account.') }}</p>
                                </div>
                                <x-ui.button :href="route('portal.bulk-posts.template')" variant="outline">{{ __('Download Template') }}</x-ui.button>
                            </div>

                            <div class="mt-5">
                                <div class="space-y-2.5">
                                    <x-ui.label>{{ __('Media CSV file') }}</x-ui.label>
                                    <label
                                        for="bulk-posts-csv-file"
                                        class="flex min-h-[3.5rem] w-full cursor-pointer items-center justify-between gap-4 rounded-[0.9rem] border px-4 py-3 transition hover:border-[var(--theme-accent)]"
                                        style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                        x-data="{ fileName: @js(old('csv_file')) || '{{ __('Choose CSV file') }}' }"
                                    >
                                        <input
                                            id="bulk-posts-csv-file"
                                            type="file"
                                            name="csv_file"
                                            accept=".csv,text/csv"
                                            class="sr-only"
                                            x-on:change="fileName = $event.target.files?.[0]?.name || '{{ __('Choose CSV file') }}'"
                                        >
                                        <span class="min-w-0 flex-1 truncate text-sm font-medium" x-text="fileName"></span>
                                        <span class="inline-flex h-9 items-center rounded-[0.75rem] border px-3 text-sm font-medium"
                                            style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.9); color: var(--theme-header-text-color);">
                                            {{ __('Browse') }}
                                        </span>
                                    </label>
                                    @if ($errors->first('csv_file'))
                                        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('csv_file') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-0 overflow-hidden">
                    <div class="border-b px-6 py-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">3</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Publishing settings') }}</p>
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Apply campaign, labels, and spacing before posts enter Publishing.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 px-6 py-6 sm:px-8">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                            <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                <x-ui.select
                                    name="campaign_id"
                                    :label="__('Campaign')"
                                    :error="$errors->first('campaign_id')"
                                >
                                    <option value="">{{ __('No campaign') }}</option>
                                    @foreach ($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}" @selected((string) old('campaign_id', '') === (string) $campaign->id)>{{ $campaign->name }}{{ $campaign->status !== 'active' ? ' ('.str($campaign->status)->headline().')' : '' }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                <x-ui.input
                                    name="interval_minutes"
                                    type="number"
                                    min="1"
                                    step="1"
                                    :label="__('Interval per post (minute)')"
                                    :value="old('interval_minutes', 60)"
                                    :error="$errors->first('interval_minutes')"
                                />
                            </div>
                        </div>

                        <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                            <x-ui.tag-selector
                                name="label_ids"
                                :label="__('Labels')"
                                :options="$labels->map(fn ($label) => ['key' => (string) $label->id, 'label' => $label->name])->all()"
                                :selected="collect((array) old('label_ids', []))->map(fn ($id) => (string) $id)->all()"
                                :description="__('Use Labels to organize, filter and report on imported posts.')"
                                :placeholder="__('Search or pick labels...')"
                                :empty-label="__('No matching labels found.')"
                                :error="$errors->first('label_ids') ?: $errors->first('label_ids.*')"
                            />
                        </div>

                        <div class="rounded-[1rem] border px-4 py-4 text-sm leading-7" style="border-color: rgba(245,158,11,0.28); background-color: rgba(254,243,199,0.75); color: #9a6700;">
                            {{ __('If the CSV has no valid schedule time, the first post will use the current time and the next posts will follow this interval.') }}
                        </div>

                        @if ($canShortenUrls)
                            <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                <x-ui.checkbox
                                    name="shorten_urls"
                                    value="1"
                                    :checked="(string) old('shorten_urls', '0') === '1'"
                                    :label="__('Shorten URLs before scheduling')"
                                />
                                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Short links will be applied to the main link field and any URLs found inside the caption.') }}</p>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card class="p-0 overflow-hidden">
                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to import') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Upload the CSV now. The importer will validate rows before queueing them into publishing.') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.button :href="route('portal.bulk-posts.template')" variant="outline" size="lg">{{ __('Download Template') }}</x-ui.button>
                            <x-ui.button type="submit" size="lg">{{ __('Save changes') }}</x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            </form>

            <aside class="hidden xl:block">
                <div class="sticky top-[calc(var(--app-shell-header-height,79px)+1.5rem)] overflow-hidden rounded-[1.35rem] border p-5 shadow-[0_24px_60px_-44px_rgba(15,23,42,0.30)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                    radial-gradient(circle at 100% 0%, rgba(var(--theme-accent-rgb), 0.12), transparent 34%),
                    rgba(var(--theme-surface-base-rgb,255,255,255),0.96);
                ">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Import flow') }}</p>
                    <div class="mt-5 space-y-4">
                        <div class="flex gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">1</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Select channels') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Choose where every CSV row will be published.') }}</p>
                            </div>
                        </div>
                        <div class="h-5 w-px translate-x-4" style="background-color: rgba(var(--theme-border-color-rgb),0.9);"></div>
                        <div class="flex gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">2</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upload CSV') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Import captions, media links, and schedule fields.') }}</p>
                            </div>
                        </div>
                        <div class="h-5 w-px translate-x-4" style="background-color: rgba(var(--theme-border-color-rgb),0.9);"></div>
                        <div class="flex gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">3</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Queue posts') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Apply campaign, labels, interval, then import.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endcomponent
