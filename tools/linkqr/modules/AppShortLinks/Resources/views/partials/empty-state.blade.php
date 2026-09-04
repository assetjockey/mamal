<div class="flex min-h-[17rem] items-center justify-center p-8">
    <div class="w-full rounded-[1rem] border p-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
            <i class="fa-light fa-link-simple text-lg"></i>
        </span>
        <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No short links found') }}</h3>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create a new link or adjust the current filters to find an existing campaign.') }}</p>
        <button type="button" x-data x-on:click.prevent="document.getElementById('short-link-create-trigger')?.click()" class="mt-5 inline-flex h-10 items-center gap-2 rounded-[0.75rem] bg-black px-4 text-sm font-semibold text-white">
            <i class="fa-light fa-plus"></i>
            {{ __('New link') }}
        </button>
    </div>
</div>
