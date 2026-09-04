<?php

/*
|--------------------------------------------------------------------------
| Ad Performance Analytics — providers, metrics, AI insights & sync tuning
|--------------------------------------------------------------------------
|
| Single source of truth for the Ad Performance Analytics plugin. The provider
| adapters, the connections UI, the sync job and the dashboards all read from
| `providers` so there is never a scattered "if ($provider === 'meta')" check.
|
| The plugin closes the create -> publish -> MEASURE loop: it pulls spend,
| delivery and conversion metrics from Meta, Google Ads and TikTok, normalizes
| them into one canonical daily row shape (ad_metrics) and attributes them back
| to the creatives generated inside MagicAds (creative_ad_links).
|
| Pricing here covers ONLY the optional AI insight generation (syncing and
| dashboards are always free). Admin overrides persist to
| ad_analytics_settings.ai_pricing.
|
| Plugin slug: magicads-ad-performance-analytics
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Connectable ad providers
    |----------------------------------------------------------------------
    |
    | Each entry declares:
    |   label / short   — display strings
    |   flux_icon       — Heroicon name for Flux UI chrome
    |   color           — brand hex (chips/badges backgrounds only, never text)
    |   oauth           — auth style: 'oauth2' for all three
    |   currency_scale  — divide raw spend by this to reach major units
    |                     (Google returns micros = 1_000_000; Meta/TikTok = 1)
    |   docs            — the provider console/setup docs the admin needs
    |   setup_hint      — short "bring your own API app" guidance
    |
    */
    'providers' => [

        'meta' => [
            'label'          => 'Meta Ads',
            'short'          => 'Meta',
            'flux_icon'      => 'globe-alt',
            'color'          => '#1877F2',
            'oauth'          => 'oauth2',
            'currency_scale' => 1,
            'scopes'         => ['ads_read', 'business_management'],
            'docs'           => 'https://developers.facebook.com/docs/marketing-apis/',
            'setup_hint'     => 'Create a Meta app, add the Marketing API product, complete Business Verification and request Advanced Access to ads_read.',
        ],

        'google' => [
            'label'          => 'Google Ads',
            'short'          => 'Google',
            'flux_icon'      => 'presentation-chart-line',
            'color'          => '#4285F4',
            'oauth'          => 'oauth2',
            'currency_scale' => 1000000, // cost_micros -> currency
            'scopes'         => ['https://www.googleapis.com/auth/adwords'],
            'docs'           => 'https://developers.google.com/google-ads/api/docs/start',
            'setup_hint'     => 'Create a Google Cloud OAuth client, apply for a Google Ads API developer token on a Manager (MCC) account and get it approved for Standard access.',
        ],

        'tiktok' => [
            'label'          => 'TikTok Ads',
            'short'          => 'TikTok',
            'flux_icon'      => 'musical-note',
            'color'          => '#010101',
            'oauth'          => 'oauth2',
            'currency_scale' => 1,
            'scopes'         => ['reporting'],
            'docs'           => 'https://business-api.tiktok.com/portal/docs',
            'setup_hint'     => 'Register a TikTok for Business developer app, request the Reporting scope and get the app approved before advertisers can authorize it.',
            // TikTok exposes revenue under different metric names depending on the
            // account's attribution setup. Leave null to skip revenue (spend/CTR
            // still work and ROAS shows as n/a); set to the exact metric your
            // advertiser reports support (e.g. 'total_complete_payment_rate') to
            // enable ROAS. Requesting an unsupported metric errors the report, so
            // this stays opt-in.
            'value_metric'   => null,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Canonical metrics
    |----------------------------------------------------------------------
    |
    | Every provider adapter maps its native fields onto this fixed set of
    | columns on ad_metrics. Derived metrics (ctr, cpc, cpm, roas, cpa) are
    | computed from these base values so they are always internally consistent.
    |
    */
    'base_metrics' => [
        'impressions',
        'clicks',
        'spend',
        'conversions',
        'conversion_value',
    ],

    /*
    |----------------------------------------------------------------------
    | AI insight generation — billing
    |----------------------------------------------------------------------
    |
    | Dashboards and syncing are free. The only billable action is generating
    | an AI performance analysis for a date range. Flat credits per generation;
    | admin overrides persist to ad_analytics_settings.ai_pricing.rate.
    |
    */
    'ai' => [
        'default_rate' => 3,             // credits per generated insight report
        'model_words'  => 500,           // soft target length of the analysis
        'model'        => 'gpt-4o-mini', // OpenAI chat model (uses the admin's openai_key)
    ],

    /*
    |----------------------------------------------------------------------
    | Sync tuning
    |----------------------------------------------------------------------
    */
    'sync' => [
        // Default look-back window (days) pulled on a full sync.
        'default_lookback_days' => 30,
        // Maximum look-back a user may request from the UI.
        'max_lookback_days'     => 365,
        // How long normalized daily rows are retained before pruning.
        'retention_days'        => 730,
        // Minutes before an in-flight sync is considered stuck and retryable.
        'stuck_after_minutes'   => 20,
        // Provider requests may be chunked into windows of this many days.
        'chunk_days'            => 30,
        // Minimum minutes between automatic syncs of the same account.
        'min_interval_minutes'  => 180,
    ],

    /*
    |----------------------------------------------------------------------
    | Attribution
    |----------------------------------------------------------------------
    |
    | How a MagicAds creative is matched to an external ad. The tag is embedded
    | in the ad name / utm_content when a creative is exported or published, and
    | matched back on sync. Manual linking is always available as a fallback.
    |
    */
    'attribution' => [
        // utm_content / ad-name token, e.g. "magicads_1234".
        'tag_prefix' => 'magicads_',
        // Regex used to recover a creative id from an ad name or tracking spec.
        'tag_regex'  => '/magicads[_-](\d+)/i',
    ],

    /*
    |----------------------------------------------------------------------
    | Fatigue detection
    |----------------------------------------------------------------------
    |
    | An ad is flagged "fatigued" when, comparing the recent window to the
    | prior window of equal length, frequency rises while CTR falls by at least
    | these thresholds (and it has meaningful spend).
    |
    */
    'fatigue' => [
        'window_days'       => 7,
        'ctr_drop_pct'      => 20,  // CTR fell by >= this percent
        'min_impressions'   => 1000,
    ],
];
