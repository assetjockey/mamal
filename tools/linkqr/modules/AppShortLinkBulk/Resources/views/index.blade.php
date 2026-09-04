<style>
    .short-link-bulk-file::file-selector-button {
        background-color: color-mix(in srgb, var(--theme-surface-soft) 78%, transparent);
        color: var(--theme-header-text-color);
    }

    .short-link-bulk-file:hover::file-selector-button {
        background-color: color-mix(in srgb, var(--theme-accent) 12%, var(--theme-surface-soft));
    }
</style>

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem]">
        <section class="overflow-hidden rounded-[1.25rem] border shadow-[0_24px_70px_-58px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-3 border-b px-4 py-4 lg:flex-row lg:items-center lg:justify-between lg:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-file-csv"></i>
                    </span>
                    <div class="min-w-0">
                        <h1 class="truncate text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Bulk import short links') }}</h1>
                        <p class="mt-1 truncate text-sm" style="color: var(--theme-muted-text-color);">{{ __('Upload or paste CSV, preview rows, then import with shared Brand settings.') }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex h-9 items-center rounded-[0.7rem] px-3 text-xs font-semibold" style="background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);">{{ $rowCount }} {{ __('rows') }}</span>
                    <button
                        type="button"
                        x-data
                        x-on:click="
                            const csv = 'name,destination_url,custom_code,password\nSpring launch,https://example.com/spring,spring-launch,\nSupport,https://example.com/support,,';
                            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                            const url = URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = 'short-links-import-template.csv';
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            URL.revokeObjectURL(url);
                        "
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-4 text-sm font-semibold"
                        style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);"
                    >
                        <i class="fa-light fa-download"></i>
                        {{ __('Template') }}
                    </button>
                    <a href="{{ route('portal.short-links.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                        <i class="fa-light fa-list"></i>
                        {{ __('Links') }}
                    </a>
                    <x-ui.button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import">
                        <i class="fa-light fa-file-import" wire:loading.remove wire:target="import"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="import"></i>
                        <span wire:loading.remove wire:target="import">{{ __('Import') }}</span>
                        <span wire:loading wire:target="import">{{ __('Importing...') }}</span>
                    </x-ui.button>
                </div>
            </div>

            <div class="grid gap-3 border-b p-4 md:grid-cols-3 lg:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="rounded-[0.9rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background-color: var(--theme-surface-soft);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Rows') }}</p>
                            <p class="mt-4 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($rowCount) }}</p>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);"><i class="fa-light fa-table-rows"></i></span>
                    </div>
                </div>
                <div class="rounded-[0.9rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background-color: var(--theme-surface-soft);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Created') }}</p>
                            <p class="mt-4 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($createdCount) }}</p>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem] bg-emerald-50 text-emerald-700"><i class="fa-light fa-circle-check"></i></span>
                    </div>
                </div>
                <div class="rounded-[0.9rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background-color: var(--theme-surface-soft);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Errors') }}</p>
                            <p class="mt-4 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format(count($errorsPreview)) }}</p>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem] bg-red-50 text-red-600"><i class="fa-light fa-triangle-exclamation"></i></span>
                    </div>
                </div>
            </div>

            <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_24rem]">
                <main class="space-y-0">
                    <section class="border-b p-4 lg:p-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('CSV source') }}</h2>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Required columns: name and destination_url. Optional: custom_code, password.') }}</p>
                            </div>
                            <x-ui.badge variant="primary">{{ __('Step 1') }}</x-ui.badge>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-[20rem_minmax(0,1fr)] lg:items-stretch">
                            <div class="flex h-full flex-col rounded-[0.95rem] border p-4" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                <label class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upload CSV file') }}</label>
                                <div class="mt-3 rounded-[0.85rem] border p-3" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                                    <input
                                        type="file"
                                        wire:model="uploadedCsv"
                                        accept=".csv,.txt"
                                        class="short-link-bulk-file block w-full cursor-pointer text-sm file:mr-3 file:h-9 file:rounded-[0.65rem] file:border-0 file:px-3 file:text-sm file:font-semibold"
                                        style="color: var(--theme-header-text-color);"
                                    >
                                </div>
                                @error('uploadedCsv') <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p> @enderror
                                <div class="mt-auto pt-4">
                                    <button type="button" wire:click="loadUploadedCsv" wire:loading.attr="disabled" wire:target="uploadedCsv,loadUploadedCsv" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-[0.75rem] px-4 text-sm font-semibold text-white shadow-sm" style="background-color: var(--theme-accent);">
                                        <i class="fa-light fa-upload" wire:loading.remove wire:target="loadUploadedCsv"></i>
                                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="loadUploadedCsv"></i>
                                        {{ __('Load file') }}
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-[0.95rem] border" style="border-color: var(--theme-border-color);">
                                <div class="flex items-center justify-between gap-3 border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                    <label class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Paste CSV') }}</label>
                                    <span class="hidden text-xs md:inline" style="color: var(--theme-muted-text-color);">name,destination_url,custom_code,password</span>
                                </div>
                                <textarea wire:model.live.debounce.350ms="csv" rows="9" spellcheck="false" class="block w-full border-0 p-4 font-mono text-sm leading-6 outline-none focus:ring-0" style="background-color: var(--theme-input-surface); color: var(--theme-input-text);"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="p-4 lg:p-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Preview mapped links') }}</h2>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Showing the first 8 rows before import.') }}</p>
                            </div>
                            <x-ui.badge variant="primary">{{ __('Step 2') }}</x-ui.badge>
                        </div>

                        <div class="mt-5 overflow-hidden rounded-[0.95rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                            <div class="hidden grid-cols-[minmax(0,0.9fr)_minmax(0,1.25fr)_9rem_7rem] border-b px-4 py-3 text-xs font-semibold uppercase tracking-[0.12em] lg:grid" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">
                                <div>{{ __('Name') }}</div>
                                <div>{{ __('Destination') }}</div>
                                <div>{{ __('Alias') }}</div>
                                <div>{{ __('Status') }}</div>
                            </div>
                            <div class="divide-y" style="--tw-divide-color: rgba(var(--theme-border-color-rgb),0.52);">
                                @forelse ($previewRows as $row)
                                    @php
                                        $isValid = filled($row['name'] ?? null) && filter_var((string) ($row['destination_url'] ?? ''), FILTER_VALIDATE_URL);
                                    @endphp
                                    <div class="grid gap-3 px-4 py-4 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.25fr)_9rem_7rem] lg:items-center">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $row['name'] ?: __('Untitled') }}</p>
                                            @if (filled($row['password'] ?? null))
                                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-lock"></i> {{ __('Password protected') }}</p>
                                            @endif
                                        </div>
                                        <p class="truncate text-sm" style="color: var(--theme-muted-text-color);">{{ $row['destination_url'] ?: '-' }}</p>
                                        <code class="rounded-[0.6rem] px-2 py-1 text-xs" style="background-color: rgba(var(--theme-border-color-rgb),0.18); color: var(--theme-header-text-color);">{{ $row['custom_code'] ?: __('auto') }}</code>
                                        <x-ui.badge :variant="$isValid ? 'success' : 'danger'">{{ $isValid ? __('Ready') : __('Fix') }}</x-ui.badge>
                                    </div>
                                @empty
                                    <div class="flex min-h-40 flex-col items-center justify-center px-4 py-8 text-center">
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                            <i class="fa-light fa-file-csv"></i>
                                        </span>
                                        <h3 class="mt-4 font-semibold" style="color: var(--theme-header-text-color);">{{ __('No rows to preview') }}</h3>
                                        <p class="mt-2 max-w-md text-sm" style="color: var(--theme-muted-text-color);">{{ __('Upload or paste CSV content to validate rows before importing.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="border-t p-4 xl:border-l xl:border-t-0 lg:p-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                    <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Shared settings') }}</h2>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Apply to every imported link.') }}</p>
                            </div>
                            <x-ui.badge variant="primary">{{ __('Step 3') }}</x-ui.badge>
                        </div>

                        <div class="mt-5 space-y-4">
                            <x-ui.select wire:model.defer="custom_domain_id" :label="__('Brand domain')">
                                <option value="">{{ __('Default app domain') }}</option>
                                @foreach ($domains as $domain)
                                    <option value="{{ $domain->id }}">{{ $domain->domain }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.select wire:model.defer="utm_preset_id" :label="__('UTM preset')">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($utmPresets as $preset)
                                    <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.field :label="__('Tracking pixels')">
                                @if ($pixels->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach ($pixels as $pixel)
                                            <div class="rounded-[0.85rem] border px-3 py-3" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                                                @php($pixelChecked = in_array((int) $pixel->id, array_map('intval', (array) $tracking_pixel_ids), true))
                                                <x-ui.checkbox
                                                    wire:model.defer="tracking_pixel_ids"
                                                    value="{{ $pixel->id }}"
                                                    :checked="$pixelChecked"
                                                    :label="$pixel->name"
                                                    :description="str($pixel->provider)->title()"
                                                />
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-[0.9rem] border border-dashed p-4 text-sm" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ __('No tracking pixels configured.') }}</div>
                                @endif
                            </x-ui.field>

                            <x-ui.button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import" class="w-full justify-center">
                                <i class="fa-light fa-file-import" wire:loading.remove wire:target="import"></i>
                                <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="import"></i>
                                <span wire:loading.remove wire:target="import">{{ __('Import short links') }}</span>
                                <span wire:loading wire:target="import">{{ __('Importing...') }}</span>
                            </x-ui.button>
                            @if ($lastCreatedIds !== [])
                                <x-ui.button type="button" variant="outline" wire:click="rollbackLastImport" class="w-full justify-center">
                                    <i class="fa-light fa-rotate-left"></i>
                                    {{ __('Rollback last import batch') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </section>

                    @if ($createdCount > 0 || $errorsPreview !== [])
                        <section class="mt-5 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Last import') }}</h3>
                            <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __(':count created', ['count' => $createdCount]) }}</p>
                            @foreach ($errorsPreview as $error)
                                <p class="mt-2 rounded-[0.75rem] px-3 py-2 text-xs" style="background-color: rgba(239,68,68,0.12); color: var(--theme-danger-color);">{{ $error }}</p>
                            @endforeach
                        </section>
                    @endif
                </aside>
            </div>
        </section>
    </div>
</div>
