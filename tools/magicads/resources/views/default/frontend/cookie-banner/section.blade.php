{{--
    GDPR cookie consent — powered by orestbida/cookieconsent v3.

    Rendered in the page footer (just before @yield('js')) and only included by
    layouts.frontend when $cookieSettings->enable_cookies is true. Every option
    below is driven by the `cookie_settings` table (managed in
    Admin → Backend Settings → GDPR Settings). When a column is empty we fall
    back to the same defaults the library ships with.

    Brand: the primary "Accept" button is themed to the brand indigo (#4F46E5)
    with white text (~6.1:1 AA) per .kiro/steering/brand-palette.md.
--}}
@php
    $cc = $cookieSettings;

    // Booleans — cast defensively so a NULL column never breaks the JSON we emit.
    $enableDarkMode        = (bool) ($cc->enable_dark_mode ?? false);
    $disablePageInteraction = (bool) ($cc->disable_page_interaction ?? false);
    $hideFromBots          = (bool) ($cc->hide_from_bots ?? true);

    // Cookie lifetime, clamped to a sane range.
    $validDays = (int) ($cc->cookie_valid_days ?? 7);
    if ($validDays < 1) {
        $validDays = 7;
    }

    // Modal layout / position — string columns with library defaults.
    $consentLayout    = $cc->consent_modal_layouts ?: 'box';
    $consentPosition  = $cc->consent_modal_position ?: 'bottom right';
    $prefLayout       = $cc->preferences_modal_layout ?: 'box';
    $prefPosition     = $cc->preferences_modal_position ?: 'right';
@endphp

{{-- Library stylesheet (CDN). Brand overrides follow in the inline <style>. --}}
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.1.0/dist/cookieconsent.css"
>

<style>
    /* Brand the primary action with the project indigo — white text is AA on it. */
    #cc-main {
        --cc-btn-primary-bg: #4F46E5;
        --cc-btn-primary-border-color: #4F46E5;
        --cc-btn-primary-hover-bg: #6366F1;
        --cc-btn-primary-hover-border-color: #6366F1;
        --cc-btn-primary-color: #ffffff;
        --cc-btn-primary-hover-color: #ffffff;
        --cc-toggle-on-bg: #4F46E5;
        --cc-cookie-category-block-bg-hover: #EEF2FF;
        --cc-link-color: #4F46E5;
    }
    /* Dark-mode primary text/icons must lift to indigo-400 for legibility. */
    #cc-main.cc--darkmode {
        --cc-btn-primary-bg: #4F46E5;
        --cc-btn-primary-hover-bg: #6366F1;
        --cc-toggle-on-bg: #818CF8;
        --cc-link-color: #818CF8;
    }
</style>

<script src="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.1.0/dist/cookieconsent.umd.js"></script>
<script type="text/javascript">
    @if ($enableDarkMode)
        document.documentElement.classList.add('cc--darkmode');
    @endif

    CookieConsent.run({
        autoShow: true,
        disablePageInteraction: {{ $disablePageInteraction ? 'true' : 'false' }},
        hideFromBots: {{ $hideFromBots ? 'true' : 'false' }},
        mode: 'opt-in',
        cookie: {
            name: 'cc_cookie',
            expiresAfterDays: {{ $validDays }},
        },
        guiOptions: {
            consentModal: {
                layout: @json($consentLayout),
                position: @json($consentPosition),
                equalWeightButtons: true,
                flipButtons: false,
            },
            preferencesModal: {
                layout: @json($prefLayout),
                position: @json($prefPosition),
                equalWeightButtons: true,
                flipButtons: false,
            },
        },
        categories: {
            necessary: {
                enabled: true,
                readOnly: true,
            },
            functionality: {},
            analytics: {
                autoClear: {
                    cookies: [
                        { name: /^_ga/ },
                        { name: '_gid' },
                    ],
                },
                services: {
                    ga: {
                        label: 'Google Analytics',
                        onAccept: () => {},
                        onReject: () => {},
                    },
                },
            },
            ads: {},
        },
        language: {
            default: 'en',
            rtl: 'ar',
            autoDetect: 'document',
            translations: {
                en: {
                    consentModal: {
                        title: @json(__('We use cookies')),
                        description: @json(__("We use cookies to keep the site reliable and to understand what's working. You can accept all, reject non-essential, or manage your preferences.")),
                        acceptAllBtn: @json(__('Accept all')),
                        acceptNecessaryBtn: @json(__('Reject all')),
                        showPreferencesBtn: @json(__('Manage preferences')),
                    },
                    preferencesModal: {
                        title: @json(__('Consent Preferences Center')),
                        acceptAllBtn: @json(__('Accept all')),
                        acceptNecessaryBtn: @json(__('Reject all')),
                        savePreferencesBtn: @json(__('Save preferences')),
                        closeIconLabel: @json(__('Close modal')),
                        sections: [
                            {
                                title: @json(__('Cookie Usage')),
                                description: @json(__('We use cookies to ensure the basic functionality of the website and to enhance your online experience.')),
                            },
                            {
                                title: @json(__('Strictly Necessary Cookies')),
                                description: @json(__('These cookies are essential for the proper functioning of the website and cannot be disabled.')),
                                linkedCategory: 'necessary',
                            },
                            {
                                title: @json(__('Functionality Cookies')),
                                description: @json(__('These cookies allow the website to remember the choices you have made in the past.')),
                                linkedCategory: 'functionality',
                            },
                            {
                                title: @json(__('Analytics Cookies')),
                                description: @json(__('These cookies collect information about how you use the website, which pages you visited and which links you clicked on.')),
                                linkedCategory: 'analytics',
                            },
                            {
                                title: @json(__('Advertisement Cookies')),
                                description: @json(__('These cookies are used to deliver advertising that is relevant to you and your interests.')),
                                linkedCategory: 'ads',
                            },
                        ],
                    },
                },
            },
        },
    });
</script>
