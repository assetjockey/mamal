<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.25rem] border shadow-[0_18px_52px_-44px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.7); background: linear-gradient(135deg, var(--theme-surface-base), color-mix(in srgb, var(--theme-surface-soft) 72%, transparent));">
        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <a href="{{ route('portal.qr-codes.edit', ['qrCode' => $qrCode->id]) }}" wire:navigate class="mt-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] border transition hover:-translate-x-0.5" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-muted-text-color);" title="{{ __('Back to QR design') }}" aria-label="{{ __('Back to QR design') }}">
                    <i class="fa-light fa-arrow-left"></i>
                </a>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                            <i class="fa-light {{ $typeMeta['icon'] }}"></i>
                        </span>
                        <x-ui.badge variant="neutral">{{ $typeMeta['label'] }}</x-ui.badge>
                        <x-ui.badge :variant="$qrCode->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($qrCode->status) }}</x-ui.badge>
                        <span class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $typeMeta['kind'] }}</span>
                    </div>
                    <h1 class="mt-2 truncate text-[1.65rem] font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $typeMeta['label'] }}</h1>
                    <p class="mt-1 max-w-2xl truncate text-sm" style="color: var(--theme-muted-text-color);">{{ $typeMeta['description'] }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 lg:justify-end">
                @if ($qrCode->shortUrl())
                    <x-ui.button href="{{ $qrCode->shortUrl() }}" target="_blank" variant="outline">
                        <i class="fa-light fa-arrow-up-right-from-square"></i>
                        {{ __('View') }}
                    </x-ui.button>
                @endif
                <x-ui.button href="{{ route('portal.qr-codes.edit', ['qrCode' => $qrCode->id]) }}" wire:navigate>
                    <i class="fa-light fa-qrcode"></i>
                    {{ __('Design QR') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <x-ui.card class="space-y-5">
            <div>
                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Type data') }}</h2>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($schema, 'help') }}</p>
            </div>

            <div class="grid gap-3 lg:grid-cols-2">
                @foreach ((array) data_get($schema, 'fields', []) as $field)
                    @php
                        $span = data_get($field, 'span') === 2 ? 'lg:col-span-2' : '';
                        $fieldType = (string) data_get($field, 'type', 'text');
                        $wireModel = 'content.'.data_get($field, 'key');
                    @endphp

                    <div class="{{ $span }}">
                        @if ($fieldType === 'textarea')
                            <x-ui.textarea wire:model.defer="{{ $wireModel }}" :label="$field['label']" rows="4"></x-ui.textarea>
                        @elseif ($fieldType === 'select')
                            <x-ui.select wire:model.live="{{ $wireModel }}" :label="$field['label']">
                                @foreach ((array) data_get($field, 'options', []) as $option)
                                    <option value="{{ $option }}">{{ __(ucwords(str_replace(['_', '-'], ' ', $option))) }}</option>
                                @endforeach
                            </x-ui.select>
                        @elseif ($fieldType === 'image')
                            <x-ui.image-picker
                                wire:model.defer="{{ $wireModel }}"
                                :name="'qr_type_image_'.data_get($field, 'key')"
                                context="portal"
                                value-field="url"
                                :value="(string) data_get($content, data_get($field, 'key'), '')"
                                :preview="(string) data_get($content, data_get($field, 'key'), '')"
                                :label="$field['label']"
                                :button-label="__('Choose image')"
                                :dialog-title="$field['label']"
                                :dialog-description="__('Select an image from Files.')"
                                layout="compact"
                            />
                        @elseif ($fieldType === 'file')
                            <x-ui.input-file-picker
                                wire:model.defer="{{ $wireModel }}"
                                :name="'qr_type_file_'.data_get($field, 'key')"
                                context="portal"
                                value-field="url"
                                picker-type="all"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.txt,.csv,image/*,video/*"
                                :value="(string) data_get($content, data_get($field, 'key'), '')"
                                :label="$field['label']"
                                :button-label="__('Choose file')"
                                :placeholder="__('No file selected')"
                                :dialog-title="__('Choose file')"
                                :dialog-description="__('Upload a file or select one from your Files library.')"
                            />
                        @else
                            <x-ui.input wire:model.defer="{{ $wireModel }}" type="{{ $fieldType }}" :label="$field['label']" />
                        @endif
                    </div>
                @endforeach
            </div>

            @foreach ((array) data_get($schema, 'lists', []) as $listKey)
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold capitalize" style="color: var(--theme-header-text-color);">{{ __(str_replace('_', ' ', $listKey)) }}</h3>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Add structured items for this QR landing page.') }}</p>
                        </div>
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="addListItem('{{ $listKey }}')">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Add') }}
                        </x-ui.button>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ((array) data_get($content, $listKey, []) as $index => $item)
                            <div wire:key="{{ $listKey }}-{{ $index }}" class="rounded-[0.85rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.64); background-color: var(--theme-surface-soft);">
                                @if ($listKey === 'products')
                                    <div class="grid gap-4 lg:grid-cols-[12rem_minmax(0,1fr)]">
                                        <div class="min-w-0">
                                            <x-ui.image-picker
                                                wire:model.defer="content.products.{{ $index }}.image_url"
                                                :name="'qr_product_image_'.$index"
                                                context="portal"
                                                value-field="url"
                                                :value="(string) data_get($item, 'image_url', '')"
                                                :preview="(string) data_get($item, 'image_url', '')"
                                                :label="__('Product image')"
                                                :button-label="__('Choose image')"
                                                :dialog-title="__('Choose product image')"
                                                :dialog-description="__('Select an image from Files.')"
                                            />
                                        </div>
                                        <div class="grid gap-3 lg:grid-cols-2">
                                            <x-ui.input wire:model.defer="content.products.{{ $index }}.name" :label="__('Product name')" />
                                            <x-ui.input wire:model.defer="content.products.{{ $index }}.price" :label="__('Price')" />
                                            <x-ui.input wire:model.defer="content.products.{{ $index }}.url" :label="__('Product URL')" />
                                            <x-ui.input wire:model.defer="content.products.{{ $index }}.description" :label="__('Short description')" />
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <x-ui.button type="button" variant="outline" size="sm" wire:click="removeListItem('{{ $listKey }}', {{ $index }})">{{ __('Remove') }}</x-ui.button>
                                    </div>
                                @elseif ($listKey === 'fields')
                                    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_11rem_8rem_auto] lg:items-end">
                                        <x-ui.input wire:model.defer="content.fields.{{ $index }}.label" :label="__('Field label')" />
                                        <x-ui.select wire:model.live="content.fields.{{ $index }}.type" :label="__('Type')">
                                            <option value="text">{{ __('Text') }}</option>
                                            <option value="email">{{ __('Email') }}</option>
                                            <option value="phone">{{ __('Phone') }}</option>
                                            <option value="textarea">{{ __('Textarea') }}</option>
                                        </x-ui.select>
                                        <x-ui.select wire:model.live="content.fields.{{ $index }}.required" :label="__('Required')">
                                            <option value="1">{{ __('Yes') }}</option>
                                            <option value="0">{{ __('No') }}</option>
                                        </x-ui.select>
                                        <x-ui.button type="button" variant="outline" wire:click="removeListItem('{{ $listKey }}', {{ $index }})">{{ __('Remove') }}</x-ui.button>
                                    </div>
                                @else
                                    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                                        <x-ui.input wire:model.defer="content.{{ $listKey }}.{{ $index }}.label" :label="__('Label')" />
                                        <x-ui.input wire:model.defer="content.{{ $listKey }}.{{ $index }}.url" :label="__('URL')" />
                                        <x-ui.button type="button" variant="outline" wire:click="removeListItem('{{ $listKey }}', {{ $index }})">{{ __('Remove') }}</x-ui.button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-[0.85rem] border border-dashed p-4 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.9); color: var(--theme-muted-text-color);">
                                {{ __('No items yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach

            <div class="flex flex-wrap gap-2">
                <x-ui.button type="button" wire:click="save">
                    <i class="fa-light fa-floppy-disk"></i>
                    {{ __('Save content') }}
                </x-ui.button>
            </div>

            @include('appqrtypebuilder::partials.public-template-picker')

        </x-ui.card>

        <x-ui.card class="space-y-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Live content') }}</p>
                <h2 class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Preview') }}</h2>
            </div>

            <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-soft);">
                <div class="overflow-hidden rounded-[1rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base);">
                    @if ($qrCode->type === 'restaurant_menu')
                        @php
                            $heroImage = (string) data_get($content, 'background_image_url', '');
                            $logoImage = (string) data_get($content, 'logo_url', '');
                            $menuItems = array_slice((array) data_get($content, 'products', []), 0, 3);
                        @endphp
                        <div class="relative min-h-32 p-4" style="background: {{ $heroImage ? 'linear-gradient(180deg, rgba(15,23,42,0.18), rgba(15,23,42,0.62)), url('.$heroImage.') center/cover' : 'linear-gradient(135deg, #fff7ed, #fef3c7)' }};">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-[0.9rem] shadow-sm" style="background-color: var(--theme-surface-base);">
                                    @if ($logoImage)
                                        <img src="{{ $logoImage }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <i class="fa-light fa-utensils" style="color: #f97316;"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em]" style="color: {{ $heroImage ? '#ffffff' : '#c2410c' }};">{{ __('Menu') }}</p>
                                    <h3 class="truncate text-lg font-semibold" style="color: {{ $heroImage ? '#ffffff' : '#111827' }};">{{ $preview['title'] }}</h3>
                                </div>
                            </div>
                            @if (data_get($content, 'subtitle'))
                                <p class="mt-4 text-sm" style="color: {{ $heroImage ? 'rgba(255,255,255,0.82)' : '#9a3412' }};">{{ data_get($content, 'subtitle') }}</p>
                            @endif
                        </div>
                            <div class="space-y-2 p-4">
                            @forelse ($menuItems as $item)
                                <div class="flex items-center gap-3 rounded-[0.85rem] p-2.5" style="background-color: var(--theme-surface-soft);">
                                    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-[0.7rem]" style="background-color: var(--theme-surface-base);">
                                        @if (data_get($item, 'image_url'))
                                            <img src="{{ data_get($item, 'image_url') }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <i class="fa-light fa-bowl-food" style="color: var(--theme-muted-text-color);"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($item, 'name') ?: __('Menu item') }}</p>
                                        <p class="truncate text-xs" style="color: var(--theme-muted-text-color);">{{ data_get($item, 'description') ?: __('Short description') }}</p>
                                    </div>
                                    @if (data_get($item, 'price'))
                                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($item, 'price') }}</span>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-[0.85rem] p-3 text-sm" style="background-color: var(--theme-surface-soft); color: var(--theme-muted-text-color);">{{ __('Add menu items to preview the public page.') }}</div>
                            @endforelse
                        </div>
                    @elseif ($qrCode->type === 'product_catalogue')
                        @php $products = array_slice((array) data_get($content, 'products', []), 0, 2); @endphp
                        <div class="p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-accent);">{{ __('Catalogue') }}</p>
                            <h3 class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $preview['title'] }}</h3>
                            @if ($preview['body'])
                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $preview['body'] }}</p>
                            @endif
                            <div class="mt-4 grid gap-3">
                                @forelse ($products as $item)
                                    <div class="flex gap-3 rounded-[0.9rem] border p-2.5" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-[0.8rem]" style="background-color: var(--theme-surface-base);">
                                            @if (data_get($item, 'image_url'))
                                                <img src="{{ data_get($item, 'image_url') }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <i class="fa-light fa-box-open" style="color: var(--theme-muted-text-color);"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($item, 'name') ?: __('Product') }}</p>
                                            <p class="mt-0.5 line-clamp-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ data_get($item, 'description') ?: __('Product description') }}</p>
                                            @if (data_get($item, 'price'))
                                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-accent);">{{ data_get($item, 'price') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-[0.85rem] p-3 text-sm" style="background-color: var(--theme-surface-soft); color: var(--theme-muted-text-color);">{{ __('Add products to preview the public catalogue.') }}</div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="overflow-hidden">
                            <div class="p-4" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.14), rgba(20,184,166,0.14));">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.9rem] shadow-sm" style="background-color: var(--theme-surface-base); color: var(--theme-accent);">
                                        <i class="fa-light {{ $typeMeta['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">{{ $typeMeta['kind'] }}</p>
                                        <h3 class="truncate text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $preview['title'] }}</h3>
                                    </div>
                                </div>
                                @if ($preview['body'])
                                    <p class="mt-3 line-clamp-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $preview['body'] }}</p>
                                @endif
                            </div>
                            @php
                                $hasScanDestination = filled((string) ($preview['destination'] ?? ''));
                            @endphp

                            <div class="space-y-2 p-4">
                                <div class="rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ $hasScanDestination ? __('Scan destination') : __('Public page') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $hasScanDestination ? __('Encoded after save') : __('Ready after save') }}</p>
                                </div>
                                @if ($preview['destination'])
                                    <p class="break-all rounded-[0.85rem] px-3 py-2 text-xs" style="background-color: var(--theme-surface-soft); color: var(--theme-muted-text-color);">{{ $preview['destination'] }}</p>
                                @else
                                    <p class="rounded-[0.85rem] px-3 py-2 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">{{ __('Editable QR content page') }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-[0.95rem] border px-4 py-3 text-sm leading-6" style="border-color: rgba(var(--theme-border-color-rgb),0.64); background-color: var(--theme-surface-soft); color: var(--theme-muted-text-color);">
                {{ __('Save content after editing the fields. Use Design QR when you are ready to adjust the visual QR code.') }}
            </div>
        </x-ui.card>
    </div>
</div>
