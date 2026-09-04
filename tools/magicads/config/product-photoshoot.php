<?php

/*
|--------------------------------------------------------------------------
| Product Photoshoot — creative presets & billing defaults
|--------------------------------------------------------------------------
|
| Pure presentation data (labels + prompt fragments) plus the default credit
| pricing matrix. Tuned without touching component logic. The admin can
| override any pricing cell from the Product Photoshoot config screen; those
| overrides persist to extension_settings.product_photoshoot_pricing.
|
*/

return [

    // Aspect ratios offered to the user (product imagery skews square / portrait).
    'ratios' => ['1:1', '4:5', '3:4', '16:9', '9:16', '4:3'],

    /*
    |----------------------------------------------------------------------
    | Billing — feature × engine credit matrix
    |----------------------------------------------------------------------
    |
    | Each tool ("feature") is billed independently and can cost a different
    | number of credits per engine. A missing cell falls back to the
    | feature's `default`, then to a hard floor of 1.
    |
    */
    'engines' => [
        'gemini' => ['label' => 'Nano Banana 2', 'sub' => 'Google · Gemini 3.1 Flash Image'],
        'openai' => ['label' => 'GPT Image 2',   'sub' => 'OpenAI · gpt-image-2'],
    ],

    'pricing' => [
        // feature key => ['label' => ..., 'default' => int, 'gemini' => int, 'openai' => int]
        'photoshoot'         => ['label' => 'Photoshoot',         'default' => 3, 'gemini' => 3, 'openai' => 4],
        'template'           => ['label' => 'Scene Template',     'default' => 3, 'gemini' => 3, 'openai' => 4],
        'background_replace' => ['label' => 'Background Replace', 'default' => 2, 'gemini' => 2, 'openai' => 3],
        'edit_image'         => ['label' => 'Edit Image',         'default' => 2, 'gemini' => 2, 'openai' => 3],
        'product_create'     => ['label' => 'AI Product Shot',    'default' => 1, 'gemini' => 1, 'openai' => 2],
    ],

    // Product categories.
    'product_categories' => [
        ['key' => 'beauty',       'label' => 'Beauty & Cosmetics'],
        ['key' => 'fragrance',    'label' => 'Fragrance'],
        ['key' => 'skincare',     'label' => 'Skincare'],
        ['key' => 'food',         'label' => 'Food & Beverage'],
        ['key' => 'beverage',     'label' => 'Drinks & Bottles'],
        ['key' => 'fashion',      'label' => 'Fashion & Apparel'],
        ['key' => 'jewelry',      'label' => 'Jewelry & Watches'],
        ['key' => 'footwear',     'label' => 'Footwear'],
        ['key' => 'tech',         'label' => 'Tech & Gadgets'],
        ['key' => 'home',         'label' => 'Home & Decor'],
        ['key' => 'supplements',  'label' => 'Supplements & Health'],
        ['key' => 'toys',         'label' => 'Toys & Kids'],
        ['key' => 'pet',          'label' => 'Pet Products'],
        ['key' => 'other',        'label' => 'Other'],
    ],

    // Lighting direction chips.
    'lighting' => [
        ['label' => 'Soft studio',    'prompt' => 'soft, even studio lighting with gentle gradients and clean highlights'],
        ['label' => 'High-key',       'prompt' => 'bright high-key lighting, minimal shadows, airy and clean'],
        ['label' => 'Dramatic',       'prompt' => 'dramatic low-key lighting with deep shadows and a bright rim light'],
        ['label' => 'Golden hour',    'prompt' => 'warm golden-hour light with long soft shadows'],
        ['label' => 'Natural daylight','prompt' => 'natural diffused daylight, true-to-life color rendering'],
        ['label' => 'Neon glow',      'prompt' => 'moody neon accent lighting with colored reflections'],
    ],

    // Camera angle chips.
    'angles' => [
        ['label' => 'Eye level',     'prompt' => 'a straight-on eye-level product shot'],
        ['label' => 'Top-down',      'prompt' => 'a top-down flat-lay angle looking directly down'],
        ['label' => '45° hero',      'prompt' => 'a 45-degree hero angle that flatters the product silhouette'],
        ['label' => 'Low angle',     'prompt' => 'a low heroic angle looking slightly up at the product'],
        ['label' => 'Macro detail',  'prompt' => 'an extreme macro close-up emphasizing texture and material detail'],
    ],

    // Composition / framing chips (advanced).
    'compositions' => [
        ['label' => 'Centered hero',    'prompt' => 'a balanced centered hero composition with the product as the clear focal point'],
        ['label' => 'Rule of thirds',   'prompt' => 'an off-center rule-of-thirds composition with deliberate negative space'],
        ['label' => 'Negative space',   'prompt' => 'generous clean negative space around the product, leaving room for ad copy'],
        ['label' => 'Close crop',       'prompt' => 'a tight close crop that fills the frame with the product'],
        ['label' => 'Group / set',      'prompt' => 'a styled group arrangement showing the product with complementary set pieces'],
        ['label' => 'In-hand',          'prompt' => 'the product held naturally in a hand to convey scale and real-world use'],
    ],

    // Color palette / mood chips (advanced).
    'color_moods' => [
        ['label' => 'Warm & cozy',      'prompt' => 'a warm cozy color palette with amber and terracotta tones'],
        ['label' => 'Cool & clean',     'prompt' => 'a cool clean palette of crisp whites, soft blues and neutral greys'],
        ['label' => 'Monochrome',       'prompt' => 'a refined monochrome palette tonally matched to the product'],
        ['label' => 'Pastel',           'prompt' => 'a soft pastel palette, airy and gentle'],
        ['label' => 'Bold & vivid',     'prompt' => 'a bold high-saturation palette with punchy complementary colors'],
        ['label' => 'Earthy natural',   'prompt' => 'an earthy natural palette of stone, wood, linen and greenery'],
        ['label' => 'Luxury dark',      'prompt' => 'a moody luxury palette of deep charcoal, black and metallic accents'],
    ],

    // Camera & lens / depth-of-field chips (advanced).
    'lenses' => [
        ['label' => '85mm portrait',    'prompt' => 'shot on an 85mm lens with a shallow depth of field and creamy background blur'],
        ['label' => '50mm natural',     'prompt' => 'shot on a 50mm lens with natural, true-to-eye perspective'],
        ['label' => '100mm macro',      'prompt' => 'shot on a 100mm macro lens resolving fine texture and micro detail'],
        ['label' => '35mm wide',        'prompt' => 'shot on a 35mm lens capturing the product within its surrounding scene'],
        ['label' => 'Tilt-shift',       'prompt' => 'a tilt-shift look with a thin plane of focus and selective blur'],
        ['label' => 'Deep focus',       'prompt' => 'a large depth of field keeping the entire product crisply in focus'],
    ],

    // Background presets for Background Replace (gradient is only the picker swatch).
    'backgrounds' => [
        ['key' => 'studio_white',  'label' => 'Studio white',   'gradient' => '#F8FAFC,#E2E8F0', 'prompt' => 'a seamless pure white studio sweep with soft shadow beneath the product'],
        ['key' => 'studio_grey',   'label' => 'Studio grey',    'gradient' => '#E2E8F0,#94A3B8', 'prompt' => 'a smooth neutral grey studio backdrop with a subtle gradient'],
        ['key' => 'marble',        'label' => 'Marble luxe',    'gradient' => '#F1F5F9,#E2E8F0', 'prompt' => 'a luxurious white marble surface with elegant soft reflections'],
        ['key' => 'wood',          'label' => 'Warm wood',      'gradient' => '#FEF3C7,#D97706', 'prompt' => 'a warm natural wood tabletop with soft directional light'],
        ['key' => 'concrete',      'label' => 'Concrete',       'gradient' => '#E2E8F0,#64748B', 'prompt' => 'a modern minimalist concrete surface with cool ambient light'],
        ['key' => 'gradient_pop',  'label' => 'Gradient pop',   'gradient' => '#EEF2FF,#818CF8', 'prompt' => 'a smooth indigo studio gradient backdrop, modern and bold'],
        ['key' => 'nature',        'label' => 'Botanical',      'gradient' => '#D1FAE5,#34D399', 'prompt' => 'a fresh botanical scene with green leaves softly blurred behind the product'],
        ['key' => 'water',         'label' => 'Water splash',   'gradient' => '#CFFAFE,#06B6D4', 'prompt' => 'crisp water droplets and a dynamic splash around the product, fresh and clean'],
        ['key' => 'kitchen',       'label' => 'Kitchen scene',  'gradient' => '#FEF3C7,#FBBF24', 'prompt' => 'a bright lifestyle kitchen counter scene with soft natural window light'],
        ['key' => 'desk',          'label' => 'Modern desk',    'gradient' => '#E0E7FF,#6366F1', 'prompt' => 'a clean modern desk setup with minimal props and soft daylight'],
        ['key' => 'sand',          'label' => 'Sandy beige',    'gradient' => '#FEF3C7,#F59E0B', 'prompt' => 'a minimalist sandy beige podium scene under bright soft light'],
        ['key' => 'dark_luxe',     'label' => 'Dark luxe',      'gradient' => '#0F172A,#334155', 'prompt' => 'a premium dark moody scene with a single dramatic spotlight and rich reflections'],
    ],

    /*
    |----------------------------------------------------------------------
    | Scene templates — curated, prompt-engineered looks
    |----------------------------------------------------------------------
    | Each category supplies a master prompt; each template adds its own
    | direction. The product image is composited into the scene.
    */
    'template_categories' => [
        'ecommerce' => [
            'label'         => 'E-commerce',
            'icon'          => 'shopping-bag',
            'master_prompt' => 'Produce a crisp, conversion-ready e-commerce product photo with the product perfectly centered, accurate colors and a clean uncluttered composition.',
            'templates'     => [
                ['key' => 'pure_white',   'label' => 'Pure white',     'prompt' => 'Place the product on a seamless pure white background with a soft natural contact shadow.'],
                ['key' => 'soft_grey',    'label' => 'Soft grey',      'prompt' => 'Place the product on a soft neutral grey gradient backdrop, studio-lit.'],
                ['key' => 'podium',       'label' => 'Podium',         'prompt' => 'Stage the product on a minimalist circular podium with gentle ambient occlusion.'],
                ['key' => 'floating',     'label' => 'Floating',       'prompt' => 'Make the product float weightlessly with a soft drop shadow below, clean studio background.'],
                ['key' => 'color_pop',    'label' => 'Color pop',      'prompt' => 'Set the product against a clean solid pastel color backdrop with a crisp contact shadow.'],
                ['key' => 'reflective',   'label' => 'Reflective base','prompt' => 'Place the product on a glossy reflective white surface with a clean mirror reflection.'],
                ['key' => 'flat_top',     'label' => 'Top-down',       'prompt' => 'Shoot the product top-down on a clean seamless surface, perfectly squared to the frame.'],
            ],
        ],
        'lifestyle' => [
            'label'         => 'Lifestyle',
            'icon'          => 'sun',
            'master_prompt' => 'Create an aspirational lifestyle product scene where the product feels naturally at home in a real, beautifully styled environment.',
            'templates'     => [
                ['key' => 'kitchen',      'label' => 'Kitchen',        'prompt' => 'Style the product on a bright modern kitchen counter with tasteful props and morning light.'],
                ['key' => 'bathroom',     'label' => 'Bathroom vanity','prompt' => 'Style the product on a clean spa-like bathroom vanity with soft towels and greenery.'],
                ['key' => 'desk',         'label' => 'Work desk',      'prompt' => 'Style the product on a minimalist work desk with subtle tech and stationery props.'],
                ['key' => 'outdoor',      'label' => 'Outdoor',        'prompt' => 'Style the product in a sunlit outdoor setting with natural bokeh and warm tones.'],
                ['key' => 'cafe',         'label' => 'Café table',     'prompt' => 'Style the product on a cozy café tabletop with a warm blurred interior and soft window light.'],
                ['key' => 'bedside',      'label' => 'Bedside',        'prompt' => 'Style the product on a soft linen bedside setting with calm, diffused daylight.'],
            ],
        ],
        'luxury' => [
            'label'         => 'Luxury',
            'icon'          => 'sparkles',
            'master_prompt' => 'Create a high-end luxury advertising still with premium materials, dramatic lighting and an editorial, magazine-grade mood.',
            'templates'     => [
                ['key' => 'spotlight',    'label' => 'Spotlight',      'prompt' => 'Light the product with a single dramatic spotlight against a deep dark backdrop with rich reflections.'],
                ['key' => 'marble_gold',  'label' => 'Marble & gold',  'prompt' => 'Stage the product on white marble with subtle gold accents and elegant soft light.'],
                ['key' => 'silk',         'label' => 'Silk drape',     'prompt' => 'Surround the product with flowing satin/silk fabric in tasteful tones for a couture feel.'],
                ['key' => 'reflection',   'label' => 'Glass reflection','prompt' => 'Place the product on a polished reflective glass surface with a clean mirror reflection.'],
                ['key' => 'velvet',       'label' => 'Velvet',         'prompt' => 'Rest the product on rich draped velvet with a moody spotlight and deep jewel tones.'],
                ['key' => 'smoke',        'label' => 'Smoke & light',  'prompt' => 'Surround the product with subtle drifting smoke and a single dramatic beam of light.'],
            ],
        ],
        'seasonal' => [
            'label'         => 'Seasonal',
            'icon'          => 'gift',
            'master_prompt' => 'Create a festive seasonal product scene with tasteful themed props that keep the product as the clear hero.',
            'templates'     => [
                ['key' => 'holiday',      'label' => 'Holiday',        'prompt' => 'Add warm holiday props — soft bokeh string lights, pine, and subtle gift elements.'],
                ['key' => 'summer',       'label' => 'Summer',         'prompt' => 'Add a fresh summer mood with bright light, tropical leaves and a vacation feel.'],
                ['key' => 'valentines',   'label' => 'Valentine',      'prompt' => 'Add a romantic Valentine theme with soft roses and a warm blush palette.'],
                ['key' => 'autumn',       'label' => 'Autumn',         'prompt' => 'Add a cozy autumn mood with warm tones, dried leaves and golden light.'],
                ['key' => 'spring',       'label' => 'Spring',         'prompt' => 'Add a fresh spring mood with soft blossoms, pastel tones and airy daylight.'],
                ['key' => 'blackfriday',  'label' => 'Sale / BF',      'prompt' => 'Add a bold high-contrast black-and-gold sale mood with dramatic spotlighting and ample copy space.'],
            ],
        ],
        'social' => [
            'label'         => 'Social Ad',
            'icon'          => 'megaphone',
            'master_prompt' => 'Create a bold, scroll-stopping social-media advertising visual with high contrast, vivid color and ample negative space for copy.',
            'templates'     => [
                ['key' => 'bold_color',   'label' => 'Bold color',     'prompt' => 'Set the product against a vivid solid color background with a punchy modern look.'],
                ['key' => 'splash',       'label' => 'Splash',         'prompt' => 'Surround the product with a dynamic liquid/powder splash for energy and motion.'],
                ['key' => 'duotone',      'label' => 'Duotone',        'prompt' => 'Apply a striking duotone treatment with strong graphic shapes behind the product.'],
                ['key' => 'flatlay',      'label' => 'Flat lay',       'prompt' => 'Compose a top-down flat lay with complementary props arranged around the product.'],
                ['key' => 'gradient_mesh','label' => 'Gradient mesh',  'prompt' => 'Set the product on a smooth modern gradient-mesh backdrop with soft glow and clean negative space.'],
                ['key' => 'geometric',    'label' => 'Geometric',      'prompt' => 'Surround the product with bold floating geometric shapes and crisp hard shadows.'],
            ],
        ],
        'minimalist' => [
            'label'         => 'Minimalist',
            'icon'          => 'square-2-stack',
            'master_prompt' => 'Create a refined minimalist product still with abundant negative space, restrained styling and a calm, gallery-grade aesthetic that lets the product breathe.',
            'templates'     => [
                ['key' => 'mono_tone',    'label' => 'Tonal',          'prompt' => 'Place the product on a tonal backdrop matched to its own color for a quiet, sophisticated monochrome look.'],
                ['key' => 'shadow_play',  'label' => 'Shadow play',    'prompt' => 'Light the product so a crisp graphic shadow falls across a clean neutral surface as the main design element.'],
                ['key' => 'paper_fold',   'label' => 'Paper fold',     'prompt' => 'Stage the product on softly folded matte paper with gentle directional light and subtle creases.'],
                ['key' => 'single_prop',  'label' => 'Single prop',    'prompt' => 'Pair the product with one carefully chosen complementary prop on a bare minimalist surface.'],
                ['key' => 'negative',     'label' => 'Negative space', 'prompt' => 'Position the product off-center with generous empty negative space and a single soft light source.'],
                ['key' => 'plinth',       'label' => 'Stone plinth',   'prompt' => 'Rest the product on a raw stone or plaster plinth against a seamless muted backdrop.'],
            ],
        ],
        'nature' => [
            'label'         => 'Nature',
            'icon'          => 'globe-alt',
            'master_prompt' => 'Create an organic nature-inspired product scene with natural materials, fresh botanicals and soft outdoor light that signals freshness and authenticity.',
            'templates'     => [
                ['key' => 'botanical',    'label' => 'Botanical',      'prompt' => 'Surround the product with fresh green leaves and foliage, softly blurred, with dappled natural light.'],
                ['key' => 'stone_water',  'label' => 'Stone & water',  'prompt' => 'Place the product on a wet river stone with gentle water, dewdrops and cool morning light.'],
                ['key' => 'wood_grain',   'label' => 'Wood grain',     'prompt' => 'Stage the product on a warm natural wood surface with soft directional daylight and organic texture.'],
                ['key' => 'sand_dune',    'label' => 'Sand',           'prompt' => 'Rest the product on rippled sand under warm soft light with a minimal desert-toned palette.'],
                ['key' => 'florals',      'label' => 'Florals',        'prompt' => 'Nestle the product among soft fresh flowers and petals with a gentle romantic palette.'],
                ['key' => 'tropical',     'label' => 'Tropical',       'prompt' => 'Set the product among lush tropical leaves with bright sunlight and vivid greens.'],
            ],
        ],
    ],

    // Quick-edit chips for Edit Image.
    'edit_chips' => [
        'Replace the background with a clean white studio',
        'Make the lighting brighter and softer',
        'Add a realistic reflection beneath the product',
        'Remove background clutter and distractions',
        'Enhance the texture and material detail',
        'Add subtle water droplets for freshness',
        'Give it a premium dark luxury mood',
        'Add soft golden-hour sunlight',
    ],

    /*
    |----------------------------------------------------------------------
    | Advanced — negative prompt & inspiration
    |----------------------------------------------------------------------
    */

    // Common "avoid" tokens offered as one-tap chips for the negative prompt.
    'negative_chips' => [
        'blurry', 'low quality', 'distorted product', 'extra logos', 'text artifacts',
        'watermark', 'harsh shadows', 'washed-out colors', 'cluttered background',
        'plastic-looking', 'unrealistic reflections', 'warped label',
    ],

    // Sensible baseline appended to every generation unless the user clears it.
    'default_negative' => 'blurry, low quality, distorted product, warped or unreadable label, extra limbs, watermark, text artifacts, oversaturated, cluttered background',

    // "Surprise me" idea seeds for the Photoshoot prompt box.
    'idea_prompts' => [
        'On a wet river stone surrounded by fresh eucalyptus leaves with soft morning mist and dewdrops.',
        'Floating above a calm reflective water surface with gentle ripples and a clean gradient sky.',
        'On a warm marble pedestal lit by a single dramatic spotlight with rich golden reflections.',
        'Nestled in crushed ice with frosty condensation and crisp cold blue lighting.',
        'On a minimalist concrete block in bright daylight with a long soft architectural shadow.',
        'Surrounded by a dynamic splash of its key ingredient frozen in mid-air, high-speed look.',
        'On a cozy linen-draped table with dried flowers and warm candlelight bokeh.',
        'Against a bold solid color backdrop with a hard geometric shadow, modern ad-campaign style.',
    ],
];
