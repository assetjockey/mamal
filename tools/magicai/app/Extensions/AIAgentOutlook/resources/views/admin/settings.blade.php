@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Outlook Settings'))
@section('titlebar_actions', '')
@section('titlebar_subtitle', __('Configure Microsoft OAuth credentials for Outlook integration'))

@section('settings')
    <form
        method="post"
        action="{{ route('dashboard.admin.ai-agent.outlook.settings.update') }}"
        id="settings_form"
        enctype="multipart/form-data"
    >
        @csrf
        <h3 class="mb-[25px] text-[20px]">{{ __('Microsoft OAuth Credentials') }}</h3>
        <div class="row">
            <x-card
                class="mb-3 max-md:text-center"
                size="lg"
            >
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client ID') }}</label>
                        <input
                            class="form-control"
                            type="text"
                            name="microsoft_client_id"
                            value="{{ $clientId }}"
                            placeholder="{{ __('Azure App (client) ID') }}"
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Client Secret') }}</label>
                        <input
                            class="form-control"
                            type="password"
                            name="microsoft_client_secret"
                            value=""
                            placeholder="{{ __('Leave blank to keep existing secret') }}"
                            autocomplete="new-password"
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Tenant ID') }}</label>
                        <input
                            class="form-control"
                            type="text"
                            name="microsoft_tenant_id"
                            value="{{ $tenantId }}"
                            placeholder="common"
                        >
                        <x-alert class="mt-2">
                            <p>{{ __('Use "common" to allow any Microsoft account, or enter your Azure tenant ID to restrict to your organization.') }}</p>
                        </x-alert>
                    </div>
                    <div
                        class="mb-3"
                        x-data="{ copied: false, url: '{{ route('dashboard.user.ai-agent.connectors.outlook.callback') }}' }"
                    >
                        <label class="form-label">{{ __('Authorized Redirect URI') }}</label>
                        <p class="mb-1 text-[13px] text-heading-foreground/60">{{ __('Add this URL to your Azure app Redirect URIs.') }}</p>
                        <div class="flex gap-2">
                            <input
                                class="form-control font-mono text-[13px]"
                                type="text"
                                :value="url"
                                readonly
                                @click="$el.select()"
                            >
                            <button
                                class="btn btn-outline-secondary shrink-0"
                                type="button"
                                @click="navigator.clipboard.writeText(url); copied = true; setTimeout(() => copied = false, 2000)"
                            >
                                <span x-show="! copied">{{ __('Copy') }}</span>
                                <span x-show="copied">{{ __('Copied!') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <button
            class="btn btn-primary w-full"
            type="submit"
        >
            {{ __('Save') }}
        </button>
    </form>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
@endpush
