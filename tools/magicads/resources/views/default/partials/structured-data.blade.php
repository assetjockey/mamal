{{--
    JSON-LD structured data partial.

    Emits schema.org markup for AI search engines (ChatGPT, Perplexity,
    Claude, Gemini, Google AI Overviews) to understand, verify, and cite
    this page. Uses data from the same view composers as seo-meta.blade.php
    plus inline content that mirrors the visible FAQ / testimonials / steps.

    Schemas emitted:
      - Organization
      - WebSite (with SearchAction)
      - SoftwareApplication (with AggregateRating)
      - FAQPage
      - HowTo
      - ItemList of Offer (one per plan)
      - BreadcrumbList
--}}
@php
    $siteName = $generalSettings?->site_name
        ?? $generalSettings?->name
        ?? config('app.name', 'AI Ad Studio');

    $description = trim((string) ($seoSettings?->home_description ?? ''));
    if ($description === '') {
        $description = __('AI-powered image, video, and ad copy for every channel.');
    }

    $homeUrl = trim((string) ($seoSettings?->home_url ?? ''));
    if ($homeUrl === '') {
        $homeUrl = url('/');
    }

    $logoSrc = $generalSettings?->logo_frontend
        ?? $generalSettings?->logo
        ?? null;

    $logoUrl = filled($logoSrc)
        ? (str_starts_with((string) $logoSrc, 'http') ? $logoSrc : asset($logoSrc))
        : asset('favicon.svg');

    $contactEmail = trim((string) ($generalSettings?->contact_email ?? ''));

    $sameAs = collect([
        $socialMedia?->linkedin_url ?? null,
        $socialMedia?->twitter_url ?? null,
        $socialMedia?->facebook_url ?? null,
        $socialMedia?->instagram_url ?? null,
        $socialMedia?->youtube_url ?? null,
        $socialMedia?->tiktok_url ?? null,
    ])->filter()->values()->all();

    // --- FAQ data (mirrors frontend/faq/section.blade.php) ---
    $faqs = [
        ['q' => __('Do I need design experience to use AI Ad Studio?'),  'a' => __('No. The studio handles layout, typography, and platform sizing for you. Give it a short brief and your brand assets and it produces finished ads you can publish immediately.')],
        ['q' => __('Which platforms are supported?'),                    'a' => __('We ship with more than twenty canvas presets across Instagram, Facebook, TikTok, YouTube, LinkedIn, and Google Display — covering every major aspect ratio for modern paid campaigns.')],
        ['q' => __('Can I use my own brand assets?'),                    'a' => __('Yes. Upload your logo, pick your fonts and palette once in a brand kit, and every generation respects those rules automatically so your output stays on brand without manual cleanup.')],
        ['q' => __('Do you support video ads?'),                         'a' => __('The Video Studio produces short-form video ads with captions, looped B-roll, and platform-ready aspect ratios for Reels, Stories, TikTok, and YouTube Shorts.')],
        ['q' => __("What happens to generations I don't use?"),          'a' => __('Every render is saved to your asset gallery with tags and campaign folders. You can search, favorite, and remix any previous generation without regenerating from scratch.')],
        ['q' => __('Can I try it before paying?'),                       'a' => __("Start for free and generate a handful of ads with every studio to see the output quality. Upgrade to a paid plan when you're ready to scale across channels.")],
    ];

    // --- HowTo steps (mirrors frontend/how-it-works/section.blade.php) ---
    $howToSteps = [
        ['name' => __('Brief it'),              'text' => __('One sentence or one paragraph. Tell the studio the offer, audience, and tone — or paste a URL and let it infer.')],
        ['name' => __('Pick a canvas'),         'text' => __('Twenty-plus presets for Meta, TikTok, YouTube, LinkedIn, and Google Display — or render every size at once.')],
        ['name' => __('Generate and ship'),     'text' => __('Preview all sizes side-by-side, remix the winners, and export production-ready files with captions.')],
    ];

    // --- Aggregate rating (mirrors hero / testimonials section) ---
    $ratingValue = '4.9';
    $reviewCount = 1247;

    // --- Plans → Offers ---
    $registrationEnabled = class_exists(\Laravel\Fortify\Features::class)
        && \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration());

    $offers = [];
    if (isset($plans) && $plans instanceof \Illuminate\Support\Collection && $plans->isNotEmpty()) {
        foreach ($plans as $plan) {
            $price = (float) ($plan->price ?? 0);
            $offers[] = [
                '@type' => 'Offer',
                'name' => (string) ($plan->name ?? 'Plan'),
                'description' => (string) ($plan->tagline ?? $plan->description ?? ''),
                'price' => number_format($price, 2, '.', ''),
                'priceCurrency' => strtoupper((string) ($plan->currency ?? 'USD')),
                'availability' => 'https://schema.org/InStock',
                'url' => $registrationEnabled
                    ? route('register', ['plan' => $plan->plan_id ?? null])
                    : $homeUrl . '#pricing',
            ];
        }
    } else {
        // Fallback offers so the schema is always non-empty
        $offers = [
            ['@type' => 'Offer', 'name' => 'Starter', 'price' => '0.00',  'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock', 'url' => $homeUrl . '#pricing'],
            ['@type' => 'Offer', 'name' => 'Studio',  'price' => '49.00', 'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock', 'url' => $homeUrl . '#pricing'],
            ['@type' => 'Offer', 'name' => 'Scale',   'price' => '199.00','priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock', 'url' => $homeUrl . '#pricing'],
        ];
    }

    // --- Compose all schemas ---
    $organization = [
        '@type' => 'Organization',
        '@id'   => $homeUrl . '#organization',
        'name'  => $siteName,
        'url'   => $homeUrl,
        'logo'  => [
            '@type' => 'ImageObject',
            'url'   => $logoUrl,
        ],
        'description' => $description,
    ];
    if (filled($contactEmail)) {
        $organization['contactPoint'] = [
            '@type' => 'ContactPoint',
            'email' => $contactEmail,
            'contactType' => 'customer support',
        ];
    }
    if (! empty($sameAs)) {
        $organization['sameAs'] = $sameAs;
    }

    $website = [
        '@type' => 'WebSite',
        '@id'   => $homeUrl . '#website',
        'url'   => $homeUrl,
        'name'  => $siteName,
        'description' => $description,
        'publisher' => ['@id' => $homeUrl . '#organization'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $homeUrl . '/?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $softwareApplication = [
        '@type' => 'SoftwareApplication',
        '@id'   => $homeUrl . '#software',
        'name'  => $siteName,
        'description' => $description,
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'AI Advertising / Marketing Automation',
        'operatingSystem' => 'Web Browser',
        'url' => $homeUrl,
        'image' => $logoUrl,
        'publisher' => ['@id' => $homeUrl . '#organization'],
        'featureList' => [
            __('AI image ad generation'),
            __('AI video ad generation'),
            __('AI ad copy generation with A/B variants'),
            __('Brand kit lock — logo, fonts, palette stay on-brand'),
            __('20+ canvas presets for Meta, TikTok, YouTube, LinkedIn, Google Display'),
            __('Searchable asset gallery with tags and campaign folders'),
        ],
        'offers' => [
            '@type' => 'AggregateOffer',
            'offerCount' => count($offers),
            'lowPrice' => '0',
            'highPrice' => (string) max(array_map(fn ($o) => (float) $o['price'], $offers)),
            'priceCurrency' => $offers[0]['priceCurrency'] ?? 'USD',
            'offers' => $offers,
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'bestRating' => '5',
            'worstRating' => '1',
            'ratingCount' => $reviewCount,
            'reviewCount' => $reviewCount,
        ],
    ];

    $faqPage = [
        '@type' => 'FAQPage',
        '@id'   => $homeUrl . '#faq',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question',
            'name'  => $f['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $f['a'],
            ],
        ], $faqs),
    ];

    $howTo = [
        '@type' => 'HowTo',
        '@id'   => $homeUrl . '#how-it-works',
        'name'  => __('How to generate ads with :app', ['app' => $siteName]),
        'description' => __('Three steps from a one-sentence brief to platform-ready ad creative across every major channel.'),
        'totalTime' => 'PT30S',
        'step' => array_map(fn ($s, $i) => [
            '@type' => 'HowToStep',
            'position' => $i + 1,
            'name' => $s['name'],
            'text' => $s['text'],
        ], $howToSteps, array_keys($howToSteps)),
    ];

    $breadcrumb = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => __('Home'),
                'item' => $homeUrl,
            ],
        ],
    ];

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organization,
            $website,
            $softwareApplication,
            $faqPage,
            $howTo,
            $breadcrumb,
        ],
    ];

    // JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE keep URLs and translated
    // strings clean; JSON_PRETTY_PRINT only in non-production for easier debugging.
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if (! app()->environment('production')) {
        $jsonFlags |= JSON_PRETTY_PRINT;
    }
@endphp

<script type="application/ld+json">
{!! json_encode($graph, $jsonFlags) !!}
</script>
