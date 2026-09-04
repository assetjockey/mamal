@extends(config('elseyyid-location.layout'))

@section('page_heading'){{ $string->en }}@endsection
@section('page_subheading'){{ __('Code') }}: {{ $string->code }}@endsection
@section('page_breadcrumb')
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Backend Settings') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('elseyyid.translations.home2') }}" separator="slash" class="text-xs">{{ __('Language Manager') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('String :code', ['code' => $string->code]) }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
@endsection

@section(config('elseyyid-location.content_section'))

    @include('langs::includes.tools')

    <div class="mt-10 rounded-xl border border-(--default-border-color) bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-4">
            @foreach (collect($string)->except(['code', 'created_at', 'updated_at'])->toArray() as $column => $value)
                <div class="flex flex-col gap-1">
                    <flux:label>{{ $column }}</flux:label>
                    <flux:input
                        type="text"
                        data-pk="{{ $string->code }}"
                        data-name="{{ $column }}"
                        :value="$value"
                    />
                </div>
            @endforeach
        </div>

        <flux:subheading class="mt-6 italic">
            {{ __('This view is read-only in the current build. Use the "Edit Strings" view from the Languages page to bulk-edit translations.') }}
        </flux:subheading>
    </div>

@endsection
