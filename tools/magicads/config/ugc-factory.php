<?php

/*
|--------------------------------------------------------------------------
| UGC Factory — fal.ai-powered user-generated-content video studio
|--------------------------------------------------------------------------
|
| UGC Factory ships as the "magicads-ugc-factory" plugin. It wraps the fal.ai
| queue API to turn a still "actor" photo into a realistic talking UGC video
| using the VEED Fabric 1.0 image-to-video model:
|
|   https://fal.ai/models/veed/fabric-1.0/api
|
| The flow is: an actor image + an audio track → a lip-synced talking video.
| The audio track is produced one of three ways from the builder:
|
|   - Text   → a script is turned into speech with ElevenLabs TTS (on fal.ai).
|   - Audio  → the user uploads a ready-made voiceover.
|   - Record → the user records a voiceover in the browser.
|
| Everything runs on the ADMIN's single fal.ai API key (entered on the plugin
| settings page and stored encrypted on extension_settings.fal_api_key, with a
| graceful fallback to the shared admin_keys.fal_key) — users never bring a key.
|
| This file is pure configuration so models, pricing and limits can be tuned
| without touching component logic.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | fal.ai endpoints
    |----------------------------------------------------------------------
    | Centralised so a model bump is a one-file change. All requests hit the
    | fal queue host and authenticate with `Authorization: Key {key}`.
    */
    'fal' => [
        'queue_base' => env('FAL_QUEUE_BASE', 'https://queue.fal.run'),

        // VEED Fabric 1.0 — image-to-video (talking video). Use the "/fast"
        // variant for quicker, cheaper turnaround when admins prefer speed.
        'fabric_model'      => env('UGC_FABRIC_MODEL', 'veed/fabric-1.0'),
        'fabric_fast_model' => env('UGC_FABRIC_FAST_MODEL', 'veed/fabric-1.0/fast'),

        // ElevenLabs multilingual TTS (text → speech) for the "Text" mode.
        'tts_model' => env('UGC_TTS_MODEL', 'fal-ai/elevenlabs/tts/multilingual-v2'),

        // Fast text-to-image model used by "Create New Actor".
        'actor_model' => env('UGC_ACTOR_MODEL', 'fal-ai/flux/schnell'),

        // Per-request HTTP timeouts (seconds).
        'timeout'          => 30,
        'connect_timeout'  => 10,
        'download_timeout' => 300,

        // Blocking bounded-poll budgets (seconds) for the in-pipeline TTS step
        // and the in-request actor-image generation.
        'tts_max_wait'   => 60,
        'actor_max_wait' => 60,
        'poll_interval'  => 3,
    ],

    /*
    |----------------------------------------------------------------------
    | Render fidelity (which Fabric model)
    |----------------------------------------------------------------------
    | 'quality' → veed/fabric-1.0 · 'fast' → veed/fabric-1.0/fast.
    */
    'render_mode' => env('UGC_RENDER_MODE', 'quality'),

    /*
    |----------------------------------------------------------------------
    | Output quality tiers (the Fabric `resolution` enum)
    |----------------------------------------------------------------------
    | Each tier is independently priced (see `pricing.ugc_video`). The default
    | tier is used in the Audio / Record modes where no quality picker is shown,
    | and is overridable by the admin.
    */
    'qualities' => [
        '480p' => ['label' => '480p', 'sub' => 'Standard · faster render', 'icon' => 'bolt'],
        '720p' => ['label' => '720p HD', 'sub' => 'Crisp · social-ready', 'icon' => 'sparkles'],
    ],

    'default_quality' => env('UGC_DEFAULT_QUALITY', '720p'),

    /*
    |----------------------------------------------------------------------
    | Billing — feature → priced variants
    |----------------------------------------------------------------------
    | UGC Factory bills per VARIANT so the cost can scale with output quality.
    |   - ugc_video    is priced per quality tier (480p / 720p).
    |   - create_actor is a single flat cost.
    | The admin can override every cell on the plugin settings page; values are
    | persisted to extension_settings.ugc_factory_pricing as
    | [feature => [variant => credits]]. A user is charged only after a
    | successful render.
    */
    'engines' => [
        'fal' => ['label' => 'fal.ai', 'sub' => 'VEED Fabric 1.0'],
    ],

    'pricing' => [
        'ugc_video' => [
            'label' => 'UGC Talking Video',
            'per'   => 'quality',
            'variants' => [
                '480p' => ['label' => '480p', 'default' => 20],
                '720p' => ['label' => '720p HD', 'default' => 35],
            ],
        ],
        'create_actor' => [
            'label' => 'Create New Actor',
            'per'   => 'flat',
            'variants' => [
                'flat' => ['label' => 'Per actor', 'default' => 5],
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Voices (ElevenLabs Default library, via fal.ai) for the "Text" mode
    |----------------------------------------------------------------------
    | The fal ElevenLabs `multilingual-v2` endpoint accepts a `voice` NAME from
    | ElevenLabs' Default voice library — `value` is the exact name passed to
    | the API. Every voice ships with a PUBLIC preview MP3 (no key required) at
    | storage.googleapis.com/eleven-public-prod/premade/voices/{id}/...mp3, used
    | for the in-picker play/pause sample. `tone` is a short UI hint.
    */
    'voices' => [
        // ---- Female ----
        ['value' => 'Rachel',    'gender' => 'Female', 'accent' => 'American', 'tone' => 'Calm narration',  'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/21m00Tcm4TlvDq8ikWAM/df6788f9-5c96-470d-8312-aab3b3d8f50a.mp3'],
        ['value' => 'Matilda',   'gender' => 'Female', 'accent' => 'American', 'tone' => 'Warm',            'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/XrExE9yKIg1WjnnlVkGX/b930e18d-6b4d-466e-bab2-0ae97c6d8535.mp3'],
        ['value' => 'Bella',     'gender' => 'Female', 'accent' => 'American', 'tone' => 'Soft',            'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/EXAVITQu4vr4xnSDxMaL/941b779e-c2ad-48d4-bddb-28d1a68fa27e.mp3'],
        ['value' => 'Elli',      'gender' => 'Female', 'accent' => 'American', 'tone' => 'Emotional',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/MF3mGyEYCl7XYWbV9V6O/d8ecadea-9e48-4e5d-868a-2ec3d7397861.mp3'],
        ['value' => 'Serena',    'gender' => 'Female', 'accent' => 'American', 'tone' => 'Pleasant',        'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/pMsXgVXv3BLzUgSXRplE/d61f18ed-e5b0-4d0b-a33c-5c6e7e33b053.mp3'],
        ['value' => 'Nicole',    'gender' => 'Female', 'accent' => 'American', 'tone' => 'Whisper · ASMR',  'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/piTKgcLEGmPE4e6mEKli/c269a54a-e2bc-44d0-bb46-4ed2666d6340.mp3'],
        ['value' => 'Grace',     'gender' => 'Female', 'accent' => 'American', 'tone' => 'Gentle · Southern','preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/oWAxZDx7w5VEj9dCyTzz/84a36d1c-e182-41a8-8c55-dbdd15cd6e72.mp3'],
        ['value' => 'Emily',     'gender' => 'Female', 'accent' => 'American', 'tone' => 'Calm',            'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/LcfcDJNUP1GQjkzn1xUU/e4b994b7-9713-4238-84f3-add8fccaaccd.mp3'],
        ['value' => 'Domi',      'gender' => 'Female', 'accent' => 'American', 'tone' => 'Strong',          'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/AZnzlk1XvdvUeBnXmlld/508e12d0-a7f7-4d86-a0d3-f3884ff353ed.mp3'],
        ['value' => 'Freya',     'gender' => 'Female', 'accent' => 'American', 'tone' => 'Expressive',      'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/jsCqWAovK2LkecY7zXl4/8e1f5240-556e-4fd5-892c-25df9ea3b593.mp3'],
        ['value' => 'Gigi',      'gender' => 'Female', 'accent' => 'American', 'tone' => 'Animated',        'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/jBpfuIE2acCO8z3wKNLl/3a7e4339-78fa-404e-8d10-c3ef5587935b.mp3'],
        ['value' => 'Dorothy',   'gender' => 'Female', 'accent' => 'British',  'tone' => 'Pleasant',        'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/ThT5KcBeYPX3keUQqHPh/981f0855-6598-48d2-9f8f-b6d92fbbe3fc.mp3'],
        ['value' => 'Charlotte', 'gender' => 'Female', 'accent' => 'Swedish',  'tone' => 'Seductive',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/XB0fDUnXU5powFXDhCwa/942356dc-f10d-4d89-bda5-4f8505ee038b.mp3'],
        ['value' => 'Mimi',      'gender' => 'Female', 'accent' => 'Swedish',  'tone' => 'Childish',        'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/zrHiDhphv9ZnVXBqCLjz/decbf20b-0f57-4fac-985b-a4f0290ebfc4.mp3'],
        ['value' => 'Glinda',    'gender' => 'Female', 'accent' => 'American', 'tone' => 'Character',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/z9fAnlkpzviPz146aGWa/cbc60443-7b61-4ebb-b8e1-5c03237ea01d.mp3'],
        // ---- Male ----
        ['value' => 'Adam',      'gender' => 'Male',   'accent' => 'American', 'tone' => 'Deep narration',  'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/pNInz6obpgDQGcFmaJgB/38a69695-2ca9-4b9e-b9ec-f07ced494a58.mp3'],
        ['value' => 'Antoni',    'gender' => 'Male',   'accent' => 'American', 'tone' => 'Well-rounded',    'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/ErXwobaYiN019PkySvjV/ee9ac367-91ee-4a56-818a-2bd1a9dbe83a.mp3'],
        ['value' => 'Josh',      'gender' => 'Male',   'accent' => 'American', 'tone' => 'Deep',            'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/TxGEqnHWrfWFTfGW9XjX/3ae2fc71-d5f9-4769-bb71-2a43633cd186.mp3'],
        ['value' => 'Arnold',    'gender' => 'Male',   'accent' => 'American', 'tone' => 'Crisp',           'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/VR6AewLTigWG4xSOukaG/316050b7-c4e0-48de-acf9-a882bb7fc43b.mp3'],
        ['value' => 'Sam',       'gender' => 'Male',   'accent' => 'American', 'tone' => 'Raspy',           'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/yoZ06aMxZJJ28mfd3POQ/ac9d1c91-92ce-4b20-8cc2-3187a7da49ec.mp3'],
        ['value' => 'Jessie',    'gender' => 'Male',   'accent' => 'American', 'tone' => 'Raspy',           'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/t0jbNlBVZ17f02VDIeMI/e26939e3-61a4-4872-a41d-33922cfbdcdc.mp3'],
        ['value' => 'Liam',      'gender' => 'Male',   'accent' => 'American', 'tone' => 'Articulate',      'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/TX3LPaxmHKxFdv7VOQHJ/63148076-6363-42db-aea8-31424308b92c.mp3'],
        ['value' => 'Thomas',    'gender' => 'Male',   'accent' => 'American', 'tone' => 'Calm · meditative','preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/GBv7mTt0atIp3Br8iCZE/98542988-5267-4148-9a9e-baa8c4f14644.mp3'],
        ['value' => 'Michael',   'gender' => 'Male',   'accent' => 'American', 'tone' => 'Orotund',         'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/flq6f7yk4E4fJM5XTYuZ/c6431a82-f7d2-4905-b8a4-a631960633d6.mp3'],
        ['value' => 'Ethan',     'gender' => 'Male',   'accent' => 'American', 'tone' => 'Whisper · ASMR',  'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/g5CIjZEefAph4nQFvHAz/26acfa99-fdec-43b8-b2ee-e49e75a3ac16.mp3'],
        ['value' => 'Jeremy',    'gender' => 'Male',   'accent' => 'Irish',    'tone' => 'Excited',         'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/bVMeCyTHy58xNoL34h3p/66c47d58-26fd-4b30-8a06-07952116a72c.mp3'],
        ['value' => 'Daniel',    'gender' => 'Male',   'accent' => 'British',  'tone' => 'Authoritative',   'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/onwK4e9ZLuTAKqWW03F9/7eee0236-1a72-4b86-b303-5dcadc007ba9.mp3'],
        ['value' => 'Joseph',    'gender' => 'Male',   'accent' => 'British',  'tone' => 'Newsy',           'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/Zlb1dXrM653N07WRdFW3/daa22039-8b09-4c65-b59f-c79c48646a72.mp3'],
        ['value' => 'Matthew',   'gender' => 'Male',   'accent' => 'British',  'tone' => 'Calm',            'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/Yko7PKHZNXotIFUBG7I9/02c66c93-a237-436f-8a7d-43e8c49bc6a3.mp3'],
        ['value' => 'Dave',      'gender' => 'Male',   'accent' => 'British',  'tone' => 'Conversational',  'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/CYw3kZ02Hs0563khs1Fj/872cb056-45d3-419e-b5c6-de2b387a93a0.mp3'],
        ['value' => 'Charlie',   'gender' => 'Male',   'accent' => 'Australian','tone' => 'Casual',         'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/IKne3meq5aSn9XLyUdCD/102de6f2-22ed-43e0-a1f1-111fa75c5481.mp3'],
        ['value' => 'James',     'gender' => 'Male',   'accent' => 'Australian','tone' => 'Calm',           'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/ZQe5CZNOzWyzPSCn5a3c/35734112-7b72-48df-bc2f-64d5ab2f791b.mp3'],
        ['value' => 'Callum',    'gender' => 'Male',   'accent' => 'American', 'tone' => 'Character',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/N2lVS1w4EtoT3dr4eOWO/ac833bd8-ffda-4938-9ebc-b0f99ca25481.mp3'],
        ['value' => 'Clyde',     'gender' => 'Male',   'accent' => 'American', 'tone' => 'Character',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/2EiwWnXFnvU5JabPnv8n/65d80f52-703f-4cae-a91d-75d4e200ed02.mp3'],
        ['value' => 'Patrick',   'gender' => 'Male',   'accent' => 'American', 'tone' => 'Character',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/ODq5zmih8GrVes37Dizd/0ebec87a-2569-4976-9ea5-0170854411a9.mp3'],
        ['value' => 'Harry',     'gender' => 'Male',   'accent' => 'American', 'tone' => 'Character',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/SOYHLrjzK2X1ezoPC6cr/86d178f6-f4b6-4e0e-85be-3de19f490794.mp3'],
        ['value' => 'Ryan',      'gender' => 'Male',   'accent' => 'American', 'tone' => 'Confident',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/wViXBPUzp2ZZixB1xQuM/4a82f749-889c-4097-85f0-a3826a28b1d8.mp3'],
        ['value' => 'Fin',       'gender' => 'Male',   'accent' => 'Irish',    'tone' => 'Character',       'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/D38z5RcWu1voky8WS1ja/a470ba64-1e72-46d9-ba9d-030c4155e2d2.mp3'],
        ['value' => 'Giovanni',  'gender' => 'Male',   'accent' => 'Italian',  'tone' => 'Foreign',         'preview' => 'https://storage.googleapis.com/eleven-public-prod/premade/voices/zcAOhNBS3c14rBihAFp1/e7410f8f-4913-4cb8-8907-784abee5aff8.mp3'],
    ],

    /*
    |----------------------------------------------------------------------
    | Preset actors
    |----------------------------------------------------------------------
    | A starter cast shown under "Pick an Actor". `image` is resolved with
    | asset() in the view — drop matching files under
    | public/assets/extensions/ugc-factory/actors/. Users can also upload their
    | own or generate new ones, which are stored per-user.
    */
    'actors' => [
        ['key' => 'at-the-cafe',                 'name' => 'Sofia',   'image' => 'assets/extensions/ugc-factory/actors/at-the-cafe.webp',                 'tag' => 'At The Cafe'],
        ['key' => 'chatting-in-the-car',         'name' => 'Mia',     'image' => 'assets/extensions/ugc-factory/actors/chatting-in-the-car.webp',         'tag' => 'Chatting In The Car'],
        ['key' => 'cozy-in-the-kitchen',         'name' => 'Emma',    'image' => 'assets/extensions/ugc-factory/actors/cozy-in-the-kitchen.webp',         'tag' => 'Cozy In The Kitchen'],
        ['key' => 'dev-in-the-office',           'name' => 'Liam',    'image' => 'assets/extensions/ugc-factory/actors/dev-in-the-office.webp',           'tag' => 'Dev In The Office'],
        ['key' => 'gaming-session',              'name' => 'Noah',    'image' => 'assets/extensions/ugc-factory/actors/gaming-session.webp',              'tag' => 'Gaming Session'],
        ['key' => 'in-a-cozy-home',              'name' => 'Ava',     'image' => 'assets/extensions/ugc-factory/actors/in-a-cozy-home.webp',              'tag' => 'In A Cozy Home'],
        ['key' => 'in-the-kitchen',              'name' => 'Olivia',  'image' => 'assets/extensions/ugc-factory/actors/in-the-kitchen.webp',              'tag' => 'In The Kitchen'],
        ['key' => 'in-the-living-room',          'name' => 'Lena',    'image' => 'assets/extensions/ugc-factory/actors/in-the-living-room.webp',          'tag' => 'In The Living Room'],
        ['key' => 'in-the-office-building',      'name' => 'Ethan',   'image' => 'assets/extensions/ugc-factory/actors/in-the-office-building.webp',      'tag' => 'In The Office Building'],
        ['key' => 'on-the-way-to-work',          'name' => 'Maya',    'image' => 'assets/extensions/ugc-factory/actors/on-the-way-to-work.webp',          'tag' => 'On The Way To Work'],
        ['key' => 'online-meeting',              'name' => 'James',   'image' => 'assets/extensions/ugc-factory/actors/online-meeting.webp',              'tag' => 'Online Meeting'],
        ['key' => 'professional',                'name' => 'Chloe',   'image' => 'assets/extensions/ugc-factory/actors/professional.webp',                'tag' => 'Professional'],
        ['key' => 'recording-a-podcast',         'name' => 'Lucas',   'image' => 'assets/extensions/ugc-factory/actors/recording-a-podcast.webp',         'tag' => 'Recording A Podcast'],
        ['key' => 'skin-care-routine',           'name' => 'Isla',    'image' => 'assets/extensions/ugc-factory/actors/skin-care-routine.webp',           'tag' => 'Skin Care Routine'],
        ['key' => 'taking-a-break-at-the-office','name' => 'Zoe',     'image' => 'assets/extensions/ugc-factory/actors/taking-a-break-at-the-office.webp','tag' => 'Taking A Break At The Office'],
        ['key' => 'talking-to-the-camera',       'name' => 'Aria',    'image' => 'assets/extensions/ugc-factory/actors/talking-to-the-camera.webp',       'tag' => 'Talking To The Camera'],
        ['key' => 'top-view',                    'name' => 'Nora',    'image' => 'assets/extensions/ugc-factory/actors/top-view.webp',                    'tag' => 'Top View'],
        ['key' => 'traveling',                   'name' => 'Xing',     'image' => 'assets/extensions/ugc-factory/actors/traveling.webp',                   'tag' => 'Traveling'],
        ['key' => 'walking-on-the-street',       'name' => 'Ruby',    'image' => 'assets/extensions/ugc-factory/actors/walking-on-the-street.webp',       'tag' => 'Walking On The Street'],
        ['key' => 'while-doing-makeup',          'name' => 'Grace',   'image' => 'assets/extensions/ugc-factory/actors/while-doing-makeup.webp',          'tag' => 'While Doing Makeup'],
        ['key' => 'working-out-at-the-gym',      'name' => 'Mason',   'image' => 'assets/extensions/ugc-factory/actors/working-out-at-the-gym.webp',      'tag' => 'Working Out At The Gym'],
    ],

    /*
    |----------------------------------------------------------------------
    | Actor-prompt starter chips (Create New Actor)
    |----------------------------------------------------------------------
    */
    'actor_starters' => [
        'A friendly young woman holding a phone, selfie style, natural daylight, plain wall background.',
        'A confident man in a white t-shirt, front-facing portrait, soft studio light.',
        'A beauty creator with glowing skin, holding a skincare product, cozy bedroom background.',
        'A casual lifestyle influencer outdoors, golden hour, candid expression.',
    ],

    /*
    |----------------------------------------------------------------------
    | Script starter chips (Text mode)
    |----------------------------------------------------------------------
    */
    'script_starters' => [
        'Honestly? This is the one product I can not stop recommending to my friends.',
        'Okay, I was skeptical at first — but three weeks in, here is my honest review.',
        'Stop scrolling. If you have been looking for a sign to try this, this is it.',
        'I get asked about this every single day, so let me finally break it down for you.',
    ],

    /*
    |----------------------------------------------------------------------
    | Limits
    |----------------------------------------------------------------------
    */
    'limits' => [
        'script_max_chars' => 1200,
        'image_max_mb'     => 10,
        'audio_max_mb'     => 25,
        'record_max_secs'  => 120,
    ],
];
