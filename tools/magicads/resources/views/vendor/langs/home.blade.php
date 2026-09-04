@extends(config('elseyyid-location.layout'))

@section(config('elseyyid-location.content_section'))

    @include('langs::includes.tools')

    @php
        /** @var \App\Models\GeneralSetting|null $settings — provided by view composer. */
        $enabledLocales = collect(explode(',', (string) ($settings->languages ?? '')))->filter()->all();
        $supportedLocales = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales();

        // Resolve a friendly native name for a column code like `pt_BR`.
        $localeName = function (string $code) use ($supportedLocales): string {
            $key = str_replace('_', '-', $code);
            return ucfirst($supportedLocales[$key]['native'] ?? $code);
        };
    @endphp

    <div class="mt-10">
        <flux:heading size="lg" level="2"><span class="font-bold">{{ __('Installed Languages') }}</span></flux:heading>
        <flux:subheading class="mb-5 text-[12px]">{{ __('Toggle which locales are exposed on the site, edit translations, or regenerate the JSON files.') }}</flux:subheading>

        <div class="space-y-2" id="installed-languages">

            {{-- English is the canonical source column on the `strings` table; it's always present. --}}
            @php
                $enChecked  = in_array('en-US', $enabledLocales, true) || in_array('en', $enabledLocales, true);
            @endphp
            <div class="flex items-center justify-between rounded-xl border border-(--default-border-color) bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="inline-flex size-9 items-center justify-center rounded-md text-xs font-bold text-white"
                          style="background: linear-gradient(120deg, #4F46E5, #0F172A);">EN</span>
                    <div>
                        <flux:heading size="sm" class="!mb-0">{{ __('English') }}</flux:heading>
                        <flux:subheading>en — {{ __('source language') }}</flux:subheading>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        class="js-language-toggle"
                        data-lang="en"
                        @checked($enChecked)
                    />
                </div>
            </div>

            {{-- Each translated locale column on the `strings` table. --}}
            @foreach ($langs as $lang)
                @php
                    $checked  = in_array($lang, $enabledLocales, true);
                    $disabled = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale() === $lang;
                @endphp
                <div class="flex items-center justify-between rounded-xl border border-(--default-border-color) bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex size-9 items-center justify-center rounded-md bg-zinc-100 text-xs font-semibold uppercase text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ \Illuminate\Support\Str::limit($lang, 2, '') }}
                        </span>
                        <div>
                            <flux:heading size="sm" class="!mb-0">{{ $localeName($lang) }}</flux:heading>
                            <flux:subheading>{{ $lang }}</flux:subheading>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            class="js-language-toggle"
                            data-lang="{{ $lang }}"
                            @checked($checked)
                            @disabled($disabled)
                        />

                        <flux:dropdown align="end">
                            <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                            <flux:menu>
                                <flux:menu.item icon="pencil-square" href="{{ route('elseyyid.translations.lang2', $lang) }}" class="text-[12px]">
                                    {{ __('Edit Strings') }}
                                </flux:menu.item>
                                <flux:menu.item icon="document-arrow-down" href="{{ route('elseyyid.translations.lang.generateJson2', $lang) }}" class="text-[12px]">
                                    {{ __('Generate JSON File') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

@endsection

@section('js')
<script>
    /**
     * Persist the on/off state of a locale to general_settings.languages.
     *
     * Uses fetch() instead of jQuery because the dashboard layout does
     * not load jQuery. The toaster() helper is set up by livewire-toaster
     * via the `<x-toaster-hub />` shipped from the sidebar layout, but
     * we surface success/error through a small inline notice as a fallback.
     */
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || '{{ csrf_token() }}';
        const url  = '{{ route('elseyyid.translations.lang.lang-save2') }}';

        document.querySelectorAll('.js-language-toggle').forEach(input => {
            input.addEventListener('change', async (event) => {
                const checkbox = event.currentTarget;
                const body = new FormData();
                body.append('lang',  checkbox.dataset.lang);
                body.append('state', checkbox.checked ? 1 : 0);

                checkbox.disabled = true;
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body,
                        credentials: 'same-origin',
                    });
                    if (!response.ok) throw new Error(await response.text());
                    document.dispatchEvent(new CustomEvent('toaster:received', {
                        detail: { type: 'success', message: '{{ __('Saved successfully') }}' },
                    }));
                } catch (e) {
                    console.error('language toggle save failed', e);
                    document.dispatchEvent(new CustomEvent('toaster:received', {
                        detail: { type: 'error', message: '{{ __('Could not save the change') }}' },
                    }));
                    checkbox.checked = !checkbox.checked; // revert UI
                } finally {
                    checkbox.disabled = false;
                }
            });
        });
    })();
</script>
@endsection
