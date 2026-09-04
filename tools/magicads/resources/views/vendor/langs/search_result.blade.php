@extends(config('elseyyid-location.layout'))

@section('page_heading'){{ __('Search Result for') }} "{{ $search_value }}"@endsection
@section('page_subheading'){{ __('Strings whose source or translations match your query.') }}@endsection
@section('page_breadcrumb')
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Backend Settings') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('elseyyid.translations.home2') }}" separator="slash" class="text-xs">{{ __('Language Manager') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Search') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
@endsection

@section(config('elseyyid-location.content_section'))

    @include('langs::includes.tools')

    <div class="mt-10">
        @if (count($result) > 0)
            <div class="mt-4 space-y-2">
                @foreach ($result as $element)
                    <div class="flex items-center justify-between rounded-xl border border-(--default-border-color) bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="min-w-0 flex-1 pr-4">
                            <flux:heading size="sm" class="truncate">{{ $element->en }}</flux:heading>
                            <flux:subheading>{{ __('Code') }}: {{ $element->code }}</flux:subheading>
                        </div>
                        <flux:button
                            icon="eye"
                            variant="ghost"
                            size="sm"
                            href="{{ route('elseyyid.translations.lang.string', $element->code) }}"
                        >
                            {{ __('View') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @else
            <flux:callout variant="secondary" icon="information-circle" class="mt-4">
                <flux:callout.heading>{{ __('No matches') }}</flux:callout.heading>
                <flux:callout.text>{{ __('No translation strings matched ":term".', ['term' => $search_value]) }}</flux:callout.text>
            </flux:callout>
        @endif
    </div>

@endsection
