@extends(config('elseyyid-location.layout'))

@php
    $supportedLocales = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales();
    $localeKey        = str_replace('_', '-', $lang);
    $langNative       = ucfirst($supportedLocales[$localeKey]['native'] ?? $lang);
@endphp

@section('page_breadcrumb')
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Backend Settings') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('elseyyid.translations.home2') }}" separator="slash" class="text-xs">{{ __('Language Manager') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $langNative }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
@endsection

@section('page_heading'){{ __('Editing Language') }}: {{ $langNative }}@endsection
@section('page_subheading'){{ $lang }} — {{ __('Update each translation, then click Save.') }}@endsection

@section(config('elseyyid-location.content_section'))

    <div class="mb-4">
        <flux:input
            id="search_string"
            type="search"
            icon="magnifying-glass"
            placeholder="{{ __('Filter strings...') }}"
            oninput="searchStrings()"
        />
    </div>

    <div class="overflow-hidden rounded-xl border border-(--default-border-color) bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full" id="strings">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr class="text-left">
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-(--default-muted-color)">{{ __('Source string (en)') }}</th>
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-(--default-muted-color)">{{ __('Translation') }} ({{ $lang }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($list as $row)
                    <tr class="border-t border-(--default-border-color) align-top dark:border-zinc-700">
                        @foreach ($row->toArray() as $column => $value)
                            @continue($column === 'code')

                            @if ($column === 'en')
                                <td class="w-1/2 px-5 py-3">
                                    <div data-name="{{ $column }}" class="text-sm">{{ $value }}</div>
                                </td>
                            @else
                                <td class="w-1/2 px-5 py-3">
                                    <flux:input
                                        type="text"
                                        data-pk="{{ $row->code }}"
                                        data-name="{{ $column }}"
                                        :value="$value"
                                        placeholder="{{ __('enter translation') }}"
                                    />
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="sticky bottom-4 mt-6 flex justify-center">
        <flux:button
            id="save_strings"
            data-lang="{{ $lang }}"
            variant="primary"
            icon="check"
            class="px-12"
        >
            {{ __('Save') }}
        </flux:button>
    </div>

@endsection

@section('js')
<script>
    /**
     * Save every translation input on the page in one POST.
     *
     * The package controller expects `data` (JSON-encoded array of strings,
     * positionally matching the order of the rendered inputs) and `lang`
     * (the column name). Order is critical because the controller uses the
     * array index + 1 as the row's `code`.
     */
    (function () {
        const saveBtn = document.getElementById('save_strings');
        const csrf    = '{{ csrf_token() }}';
        const url     = '{{ route('elseyyid.translations.lang.update-all2') }}';
        if (!saveBtn) return;

        const originalLabel = saveBtn.innerHTML;

        saveBtn.addEventListener('click', async () => {
            const inputs = Array.from(document.querySelectorAll('#strings input[type="text"]'));
            const values = inputs.map(input => input.value);

            const body = new FormData();
            body.append('data', JSON.stringify(values));
            body.append('lang', saveBtn.dataset.lang);

            saveBtn.disabled  = true;
            saveBtn.innerHTML = '<span class="animate-pulse">{{ __('Saving...') }}</span>';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body,
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error(await response.text());
                document.dispatchEvent(new CustomEvent('toaster:received', {
                    detail: { type: 'success', message: '{{ __('Strings saved successfully') }}' },
                }));
            } catch (e) {
                console.error('save translations failed', e);
                document.dispatchEvent(new CustomEvent('toaster:received', {
                    detail: { type: 'error', message: '{{ __('Could not save the strings') }}' },
                }));
            } finally {
                saveBtn.disabled  = false;
                saveBtn.innerHTML = originalLabel;
            }
        });
    })();

    /**
     * Live-filter the strings table by source or translation contents.
     */
    function searchStrings() {
        const filter = (document.getElementById('search_string')?.value || '').toUpperCase();
        const rows   = document.querySelectorAll('#strings tbody tr');

        rows.forEach(row => {
            const sourceCell      = row.querySelector("[data-name='en']");
            const translationCell = row.querySelector('input[type="text"]');
            const sourceText      = sourceCell ? sourceCell.textContent.toUpperCase() : '';
            const translationText = translationCell ? translationCell.value.toUpperCase() : '';

            row.style.display = (sourceText.includes(filter) || translationText.includes(filter)) ? '' : 'none';
        });
    }
</script>
@endsection
