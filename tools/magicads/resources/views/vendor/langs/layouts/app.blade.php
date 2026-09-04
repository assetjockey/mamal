{{--
    Language Manager — vendor override layout for elseyyid/laravel-json-mysql-locations-manager.

    Each child blade in this folder (`langs::home`, `langs::list`, `langs::lang`,
    `langs::search_result`) extends this file via
    `@extends(config('elseyyid-location.layout'))` and pushes its markup into
    a `content` section (configured by `elseyyid-location.content_section` —
    note that the published config sets this to `content_languages`, not the
    Blade-default `content`).

    Implementation note: Blade's `@yield` doesn't bridge into a Blade
    component slot (the body of a `<x-…>` invocation). Sections defined by
    the child blade live in the parent layout's render context, but a
    component evaluates its slot in a fresh compiled context and never sees
    them. To work around this we manually pull each section's accumulated
    content out of the section bag via `$__env->yieldContent()` BEFORE
    invoking the sidebar component, then emit the captured strings inside
    the slot as raw HTML.

    Width + chrome match the rest of the admin Settings pages
    (e.g. resources/views/default/livewire/admin/backend/global-settings.blade.php):
    centered column constrained to `lg:w-7/12`, breadcrumbs in `mb-6`, then
    a bold `text-2xl` title with subheading.
--}}

@php
    /** @var \Illuminate\View\Factory $__env */
    $contentSection = config('elseyyid-location.content_section', 'content');
    $scriptsSection = config('elseyyid-location.scripts_section', 'scripts');

    $sectionBody    = $__env->yieldContent($contentSection);
    $sectionCss     = $__env->yieldContent('css');
    $sectionScripts = $__env->yieldContent($scriptsSection);
    $sectionJs      = $__env->yieldContent('js');

    // Optional per-page metadata so child views can customize the heading + breadcrumbs.
    /** @var string|null $pageHeading  — set via `@section('page_heading')` */
    $pageHeading     = trim($__env->yieldContent('page_heading'));
    /** @var string|null $pageSubheading */
    $pageSubheading  = trim($__env->yieldContent('page_subheading'));
    /** @var string|null $pageBreadcrumb — set via `@section('page_breadcrumb')` */
    $pageBreadcrumb  = trim($__env->yieldContent('page_breadcrumb'));
@endphp

<x-layouts::app.sidebar :title="__('Language Manager')">
    <flux:main class="bg-(--default-main-bg-color) m-4 rounded-xl border border-transparent shadow-sm lg:p-12">
        <div>
            <div class="flex justify-center">
                <div class="w-full lg:w-7/12">

                    {{-- Breadcrumbs (matches admin Settings pages) --}}
                    <div class="mb-6">
                        @if ($pageBreadcrumb !== '')
                            {!! $pageBreadcrumb !!}
                        @else
                            <flux:breadcrumbs>
                                <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                                <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Backend Settings') }}</flux:breadcrumbs.item>
                                <flux:breadcrumbs.item href="{{ route('elseyyid.translations.home2') }}" separator="slash" class="text-xs text-gray-500 dark:text-white/80">{{ __('Language Manager') }}</flux:breadcrumbs.item>
                            </flux:breadcrumbs>
                        @endif
                    </div>

                    {{-- Title block --}}
                    <div class="mb-6">
                        <h1 class="font-bold text-2xl">
                            {{ $pageHeading !== '' ? $pageHeading : __('Language Manager') }}
                        </h1>
                        <flux:subheading size="md" class="mb-0 text-[12px]">
                            {{ $pageSubheading !== ''
                                ? $pageSubheading
                                : __('Translate the strings used across the application and manage which locales are exposed to your users.') }}
                        </flux:subheading>
                    </div>

                    {{-- Page-level actions, dark button styling matching the rest of the admin --}}
                    <div class="mb-9 flex flex-wrap items-center gap-2">
                        <flux:button
                            icon="arrow-path"
                            variant="primary"
                            color="zinc"
                            size="sm"
                            href="{{ route('elseyyid.translations.lang.reinstall') }}"
                            onclick="return confirm('{{ __('Reinstall will rebuild the strings table from your lang/*.json files. Existing translations are backed up first. Continue?') }}');"
                        >
                            {{ __('Reinstall Language Files') }}
                        </flux:button>
                        <flux:button
                            icon="document-arrow-down"
                            variant="primary"
                            color="zinc"
                            size="sm"
                            href="{{ route('elseyyid.translations.lang.publishAll2') }}"
                        >
                            {{ __('Publish All JSON Files') }}
                        </flux:button>
                    </div>

                    {!! $sectionCss !!}

                    @includeIf('langs::includes.messages')

                    {!! $sectionBody !!}

                    {!! $sectionScripts !!}
                    {!! $sectionJs !!}

                </div>
            </div>
        </div>
    </flux:main>
</x-layouts::app.sidebar>
