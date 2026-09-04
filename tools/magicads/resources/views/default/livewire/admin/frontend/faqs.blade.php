<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-8/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('FAQs Manager') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9 flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-bold text-2xl">{{ __('FAQs Manager') }}</h1>
                    <flux:subheading size="md">{{ __('Manage the questions shown in the landing page FAQ section') }}</flux:subheading>
                </div>
                <flux:button wire:click="create" variant="primary" icon="plus" class="cursor-pointer shrink-0">
                    {{ __('Add FAQ') }}
                </flux:button>
            </div>

            <div class="mb-6">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search questions...') }}" />
            </div>

            <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                @if ($faqs->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-white"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                            <flux:icon.question-mark-circle class="size-6" />
                        </span>
                        <flux:heading size="lg">{{ __('No FAQs yet') }}</flux:heading>
                        <flux:subheading>{{ __('Create your first FAQ to populate the landing page.') }}</flux:subheading>
                        <flux:button wire:click="create" variant="primary" icon="plus" class="mt-2 cursor-pointer">
                            {{ __('Add FAQ') }}
                        </flux:button>
                    </div>
                @else
                    <flux:table :paginate="$faqs">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Question') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($faqs as $faq)
                                <flux:table.row :key="$faq->id">
                                    <flux:table.cell class="max-w-md">
                                        <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $faq->question }}</span>
                                        <p class="mt-0.5 truncate text-xs text-zinc-400">{{ \Illuminate\Support\Str::limit(strip_tags($faq->answer), 80) }}</p>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($faq->status === 'active')
                                            <flux:badge color="emerald" size="sm">{{ __('Active') }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell class="text-right">
                                        <div class="flex justify-end gap-1">
                                            <flux:button wire:click="edit({{ $faq->id }})" variant="ghost" size="sm" icon="pencil-square" class="cursor-pointer" />
                                            <flux:button
                                                x-data
                                                x-on:click="$dispatch('open-modal', 'confirm-faq-deletion')"
                                                wire:click="confirmDelete({{ $faq->id }})"
                                                variant="ghost" size="sm" icon="trash"
                                                class="cursor-pointer text-rose-600 dark:text-rose-400" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================
         Create / edit FAQ modal
         ============================================================ --}}
    <flux:modal wire:model="showModal" name="faq-form" class="max-w-xl w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $faqId ? __('Edit FAQ') : __('Add FAQ') }}</flux:heading>
                <flux:subheading>{{ __('This content appears in the FAQ section of the landing page.') }}</flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('Question') }}</flux:label>
                <flux:input wire:model="question" placeholder="{{ __('e.g. Do I need design experience?') }}" />
                <flux:error name="question" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Answer') }}</flux:label>
                <flux:textarea wire:model="answer" rows="5" placeholder="{{ __('Write a clear, helpful answer...') }}" />
                <flux:error name="answer" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status">
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>
                <flux:description>{{ __('Only active FAQs are shown on the landing page.') }}</flux:description>
                <flux:error name="status" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-white/6">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $faqId ? __('Update FAQ') : __('Create FAQ') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ============================================================
         Delete confirmation modal
         ============================================================ --}}
    <flux:modal name="confirm-faq-deletion" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete FAQ') }}</flux:heading>
                <flux:subheading>{{ __('This action is permanent and cannot be undone.') }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:modal.close>
                    <flux:button variant="danger" wire:click="delete">{{ __('Delete') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
