<?php

/*
|--------------------------------------------------------------------------
| Fashion Studio — creative presets
|--------------------------------------------------------------------------
|
| Curated direction options surfaced across the Fashion Studio tools. These
| are pure presentation data (labels + prompt fragments) so they can be tuned
| without touching component logic.
|
*/

return [

    // Aspect ratios offered to the user.
    'ratios' => ['9:16', '1:1', '3:4', '16:9', '4:3'],

    /*
    |----------------------------------------------------------------------
    | Billing — feature × model credit matrix
    |----------------------------------------------------------------------
    |
    | Each tool ("feature") is billed independently, and every feature can
    | cost a different number of credits per engine. These are the defaults;
    | the admin can override any cell from the Fashion Studio config screen,
    | and overrides are persisted to extension_settings.fashion_studio_pricing.
    |
    | `engines` lists the billable engines; `features` defines each tool's
    | label + default per-engine cost. Keys under each feature must match an
    | engine key. A missing cell falls back to the feature's `default`.
    |
    */
    'engines' => [
        'gemini' => ['label' => 'Nano Banana 2', 'sub' => 'Google · Gemini 3.1 Flash Image'],
        'openai' => ['label' => 'GPT Image 2',   'sub' => 'OpenAI · gpt-image-2'],
    ],

    'pricing' => [
        // feature key => ['label' => ..., 'default' => int, 'gemini' => int, 'openai' => int]
        'photoshoot'      => ['label' => 'Photoshoot',      'default' => 3, 'gemini' => 3, 'openai' => 4],
        'virtual_try_on'  => ['label' => 'Virtual Try-On',  'default' => 3, 'gemini' => 3, 'openai' => 4],
        'change_model'    => ['label' => 'Change Model',    'default' => 3, 'gemini' => 3, 'openai' => 4],
        'change_style'    => ['label' => 'Change Style',    'default' => 2, 'gemini' => 2, 'openai' => 3],
        'edit_image'      => ['label' => 'Edit Image',      'default' => 2, 'gemini' => 2, 'openai' => 3],
        'create_video'    => ['label' => 'Create Video',   'default' => 12, 'gemini' => 12, 'openai' => 12],
        'wardrobe_create' => ['label' => 'Wardrobe AI Item', 'default' => 1, 'gemini' => 1, 'openai' => 2],
    ],

    // Garment slots for Virtual Try-On.
    'slots' => [
        ['key' => 'top',       'label' => 'Top',        'icon' => 'shirt'],
        ['key' => 'bottom',    'label' => 'Bottom',     'icon' => 'package'],
        ['key' => 'outer',     'label' => 'Outerwear',  'icon' => 'layers'],
        ['key' => 'footwear',  'label' => 'Footwear',   'icon' => 'footprints'],
        ['key' => 'accessory', 'label' => 'Accessory',  'icon' => 'gem'],
    ],

    // Wardrobe categories.
    'categories' => [
        ['key' => 'tops',         'label' => 'Tops'],
        ['key' => 'bottoms',      'label' => 'Bottoms'],
        ['key' => 'dresses',      'label' => 'Dresses'],
        ['key' => 'outerwear',    'label' => 'Outerwear'],
        ['key' => 'footwear',     'label' => 'Footwear'],
        ['key' => 'accessories',  'label' => 'Accessories'],
        ['key' => 'bags',         'label' => 'Bags'],
        ['key' => 'jewelry',      'label' => 'Jewelry'],
    ],

    // Pose direction chips.
    'poses' => [
        ['label' => 'Editorial stance', 'prompt' => 'a confident editorial standing pose, weight on one hip, chin slightly raised'],
        ['label' => 'Walking',          'prompt' => 'a natural mid-stride walking pose as if on a runway'],
        ['label' => 'Three-quarter',    'prompt' => 'a relaxed three-quarter turn toward the camera'],
        ['label' => 'Seated',           'prompt' => 'an elegant seated pose on a simple stool'],
        ['label' => 'Back detail',      'prompt' => 'turned to showcase the back of the outfit, head glancing over the shoulder'],
        ['label' => 'Candid',           'prompt' => 'a candid, in-motion pose with relaxed body language'],
    ],

    // Background presets (gradient is only for the picker swatch).
    'backgrounds' => [
        ['label' => 'Studio seamless', 'gradient' => '#EEF2FF,#C7D2FE', 'prompt' => 'a clean seamless studio backdrop in soft neutral grey'],
        ['label' => 'Warm beige',      'gradient' => '#FEF3C7,#FCD34D', 'prompt' => 'a warm beige studio wall with soft directional light'],
        ['label' => 'Concrete loft',   'gradient' => '#E2E8F0,#94A3B8', 'prompt' => 'an industrial concrete loft with large soft window light'],
        ['label' => 'Golden hour',     'gradient' => '#FDE68A,#F59E0B', 'prompt' => 'an outdoor golden-hour scene with warm backlight and gentle lens flare'],
        ['label' => 'Urban street',    'gradient' => '#CBD5E1,#475569', 'prompt' => 'a blurred urban street at dusk with bokeh city lights'],
        ['label' => 'Botanical',       'gradient' => '#D1FAE5,#34D399', 'prompt' => 'a lush botanical garden with soft diffused daylight'],
        ['label' => 'Marble luxe',     'gradient' => '#F1F5F9,#E2E8F0', 'prompt' => 'a luxurious white marble interior with elegant ambient light'],
        ['label' => 'Gradient pop',    'gradient' => '#E0E7FF,#818CF8', 'prompt' => 'a smooth indigo gradient backdrop, modern and minimal'],
        ['label' => 'Sandy desert',    'gradient' => '#FEF3C7,#FBBF24', 'prompt' => 'a minimalist sandy desert dune under clear bright sky'],
    ],

    // Lighting chips.
    'lighting' => [
        ['label' => 'Soft cinematic', 'prompt' => 'soft cinematic key light with gentle fill and natural shadows'],
        ['label' => 'High-key',       'prompt' => 'bright high-key studio lighting, minimal shadows, clean and airy'],
        ['label' => 'Dramatic',       'prompt' => 'dramatic low-key lighting with strong contrast and rim light'],
        ['label' => 'Golden glow',    'prompt' => 'warm golden-hour glow with soft backlight'],
        ['label' => 'Daylight',       'prompt' => 'natural diffused daylight, true-to-life color'],
    ],

    // Preset model archetypes for Change Model.
    'models' => [
        ['label' => 'Female · editorial', 'emoji' => '👩', 'prompt' => 'a female fashion model with an editorial, high-fashion look'],
        ['label' => 'Male · editorial',   'emoji' => '👨', 'prompt' => 'a male fashion model with a sharp, editorial look'],
        ['label' => 'Androgynous',        'emoji' => '🧑', 'prompt' => 'an androgynous model with a striking contemporary look'],
        ['label' => 'Mature · elegant',   'emoji' => '🧓', 'prompt' => 'a mature, elegant model with refined styling'],
        ['label' => 'Youthful · street',  'emoji' => '🧒', 'prompt' => 'a youthful model with a fresh streetwear aesthetic'],
        ['label' => 'Plus size',          'emoji' => '🧍', 'prompt' => 'a plus-size model with confident, body-positive styling'],
    ],

    // Style packs for Change Style. Text-only selectable cells.
    'style_packs' => [
        ['key' => 'streetwear',  'label' => 'Streetwear',   'prompt' => 'restyle into bold contemporary streetwear with layered urban pieces'],
        ['key' => 'minimal',     'label' => 'Minimalist',   'prompt' => 'restyle into clean minimalist fashion with neutral tones and refined tailoring'],
        ['key' => 'glam',        'label' => 'Evening Glam', 'prompt' => 'restyle into glamorous evening wear with luxurious fabrics and statement details'],
        ['key' => 'vintage',     'label' => 'Vintage',      'prompt' => 'restyle into a curated vintage aesthetic with retro silhouettes and warm tones'],
        ['key' => 'athleisure',  'label' => 'Athleisure',   'prompt' => 'restyle into sleek modern athleisure with performance fabrics'],
        ['key' => 'bohemian',    'label' => 'Bohemian',     'prompt' => 'restyle into free-spirited bohemian fashion with flowing layers and prints'],
        ['key' => 'business',    'label' => 'Business',     'prompt' => 'restyle into sharp business attire with impeccable tailoring'],
        ['key' => 'avantgarde',  'label' => 'Avant-garde',  'prompt' => 'restyle into experimental avant-garde fashion with sculptural shapes'],
        ['key' => 'casual',      'label' => 'Casual',       'prompt' => 'restyle into relaxed everyday casual wear — comfortable, effortless and easy-going'],
        ['key' => 'formal',      'label' => 'Formal',       'prompt' => 'restyle into elegant formal wear suitable for black-tie events, with refined tailoring'],
        ['key' => 'oldmoney',    'label' => 'Old Money',    'prompt' => 'restyle into a quiet-luxury old-money aesthetic with timeless, understated premium pieces'],
        ['key' => 'y2k',         'label' => 'Y2K',          'prompt' => 'restyle into a nostalgic early-2000s Y2K look with playful, bold retro-futuristic pieces'],
        ['key' => 'preppy',      'label' => 'Preppy',       'prompt' => 'restyle into a polished preppy look with collegiate, smart-casual staples'],
        ['key' => 'grunge',      'label' => 'Grunge',       'prompt' => 'restyle into a 90s grunge aesthetic with distressed layers, plaid and a moody palette'],
        ['key' => 'gothic',      'label' => 'Gothic',       'prompt' => 'restyle into a dark gothic aesthetic with dramatic black silhouettes and edgy details'],
        ['key' => 'cottagecore', 'label' => 'Cottagecore',  'prompt' => 'restyle into a soft cottagecore look with floral prints, natural fabrics and romantic details'],
        ['key' => 'punk',        'label' => 'Punk',         'prompt' => 'restyle into a rebellious punk aesthetic with leather, studs, and bold anti-establishment edge'],
        ['key' => 'korean',      'label' => 'K-Fashion',    'prompt' => 'restyle into trendy modern Korean street fashion with chic, youthful layering'],
        ['key' => 'western',     'label' => 'Western',      'prompt' => 'restyle into a western look with denim, suede, fringe and cowboy-inspired details'],
        ['key' => 'resort',      'label' => 'Coastal',      'prompt' => 'restyle into a breezy coastal resort look with light fabrics and vacation-ready ease'],
    ],

    // Motion presets for Create Video keyframes.
    'motion' => [
        ['key' => 'turn',     'label' => 'Runway turn', 'icon' => 'arrow-path',                  'prompt' => 'the model performing a graceful runway turn'],
        ['key' => 'walk',     'label' => 'Walk toward', 'icon' => 'arrow-right',                 'prompt' => 'the model walking confidently toward the camera'],
        ['key' => 'wind',     'label' => 'Wind flow',   'icon' => 'sparkles',                    'prompt' => 'fabric and hair flowing in a gentle breeze'],
        ['key' => 'dolly',    'label' => 'Dolly in',    'icon' => 'video-camera',                'prompt' => 'a slow cinematic dolly-in toward the model'],
        ['key' => 'spin',     'label' => 'Outfit spin', 'icon' => 'arrow-path-rounded-square',   'prompt' => 'the model spinning to show the outfit from all angles'],
        ['key' => 'pose',     'label' => 'Pose change', 'icon' => 'bolt',                        'prompt' => 'the model transitioning smoothly between two editorial poses'],
    ],

    // Quick-edit chips for Edit Image.
    'edit_chips' => [
        'Change the background to a clean studio',
        'Make the lighting warmer and softer',
        'Change the outfit color to deep emerald',
        'Add subtle golden-hour sunlight',
        'Remove background distractions',
        'Enhance fabric texture and detail',
        'Convert to high-fashion black and white',
        'Add a soft cinematic color grade',
    ],
];
