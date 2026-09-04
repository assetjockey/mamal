{{--
    Toolbar for the language manager — used by `langs::home` and a few of the
    other package views. Provides:
      - a "default language" picker (writes to general_settings.default_language)
      - an "add new language" picker (creates a new column on the strings table)

    Both forms post via plain GET to existing routes.
    Uses Flux components so the chrome matches the rest of the dashboard.
--}}

@php
    /** @var \App\Models\GeneralSetting|null $settings — shared by AppServiceProvider's view composer. */
    $supportedLocales = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales();
    $enabledLocales   = collect(explode(',', (string) ($settings->languages ?? '')))->filter()->all();
    $installed        = languagesList(); // memoized — see app/Services/helpers.php

    // Locales that aren't installed yet — these are what the "Add new" select offers.
    $addableLocales = collect($supportedLocales)
        ->reject(fn ($properties, $code) => in_array($code, $installed, true))
        ->all();
@endphp

<div class="grid gap-4 md:grid-cols-2">

    {{-- ── Default language ── --}}
    <div class="rounded-xl border border-(--default-border-color) bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="sm" class="mb-1 font-semibold">{{ __('Default Language') }}</flux:heading>
        <flux:subheading class="mb-4 text-[12px]">{{ __('Choose which locale loads by default for new visitors.') }}</flux:subheading>

        <form action="{{ route('elseyyid.translations.lang.setLocale2') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:select name="setLocale" placeholder="{{ __('Select Default Language') }}" class="flex-1">
                @foreach ($supportedLocales as $localeCode => $properties)
                    @if (in_array($localeCode, $enabledLocales, true))
                        <flux:select.option
                            value="{{ $localeCode }}"
                            :selected="optional($settings)->default_language === $localeCode"
                        >
                            {{ ucfirst($properties['native']) }}
                            @if (optional($settings)->default_language === $localeCode)
                                — {{ __('current default') }}
                            @endif
                        </flux:select.option>
                    @endif
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="primary" icon="check">{{ __('Set') }}</flux:button>
        </form>
    </div>

    {{-- ── Add new language ── --}}
    <div class="rounded-xl border border-(--default-border-color) bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading size="sm" class="mb-1 font-semibold">{{ __('Add New Language') }}</flux:heading>
        <flux:subheading class="mb-4 text-[12px]">{{ __('Adds a new locale column to the translations table.') }}</flux:subheading>

        @if (empty($addableLocales))
            <flux:callout variant="secondary" icon="check-circle">
                <flux:callout.text>{{ __('All supported locales are already installed.') }}</flux:callout.text>
            </flux:callout>
        @else
            <form action="{{ route('elseyyid.translations.lang.newLang2') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <flux:select name="newLang" placeholder="{{ __('Select language to add') }}" class="flex-1">
                    @foreach ($addableLocales as $localeCode => $properties)
                        <flux:select.option value="{{ $localeCode }}">
                            {{ ucfirst($properties['native']) }} <span class="text-(--default-muted-color)">({{ $localeCode }})</span>
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:button type="submit" variant="primary" icon="plus">{{ __('Add') }}</flux:button>
            </form>
        @endif
    </div>

</div>
