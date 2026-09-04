<div
    x-data="{
        open: false,
        key: 'linkqr-gdpr-consent-v1',
        init() {
            this.open = localStorage.getItem(this.key) !== 'accepted' && localStorage.getItem(this.key) !== 'declined';
        },
        accept() {
            localStorage.setItem(this.key, 'accepted');
            this.open = false;
            window.dispatchEvent(new CustomEvent('gdpr-consent-updated', { detail: { consent: 'accepted' } }));
        },
        decline() {
            localStorage.setItem(this.key, 'declined');
            this.open = false;
            window.dispatchEvent(new CustomEvent('gdpr-consent-updated', { detail: { consent: 'declined' } }));
        }
    }"
    x-cloak
    x-show="open"
    x-transition.opacity
    class="fixed inset-x-0 bottom-0 z-[70] px-3 pb-3 sm:px-6 sm:pb-6"
>
    <div class="mx-auto max-h-[46vh] max-w-5xl overflow-y-auto rounded-[1.1rem] border p-3 shadow-[0_28px_90px_-58px_rgba(15,23,42,0.7)] backdrop-blur-xl sm:max-h-none sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.78); background: rgba(var(--theme-surface-bg-rgb),0.94);">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex min-w-0 gap-3">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.85rem] bg-blue-50 text-blue-700 sm:h-10 sm:w-10">
                    <i class="fa-light fa-cookie-bite"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-extrabold tracking-[-0.02em] text-slate-950">{{ __('Cookie Policy') }}</h2>
                    <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-600 sm:text-sm sm:leading-6">
                        {{ __('We use cookies to improve your experience, measure traffic, and support analytics features.') }}
                        <a href="{{ route('guest.privacy-policy') }}" class="font-bold text-blue-700 hover:text-blue-800">{{ __('Read our cookie policy') }}</a>
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 gap-2">
                <button type="button" x-on:click="decline()" class="inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] border px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50" style="border-color: rgba(var(--theme-border-color-rgb),0.78);">
                    {{ __('Decline') }}
                </button>
                <button type="button" x-on:click="accept()" class="linkqr-button-primary inline-flex h-11 flex-1 items-center justify-center rounded-[var(--theme-button-radius)] px-4 text-sm font-bold sm:flex-none">
                    {{ __('Allow') }}
                </button>
            </div>
        </div>
    </div>
</div>
