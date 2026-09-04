<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Number of variants generated per request
    |--------------------------------------------------------------------------
    */
    'variants_per_generation' => 1,

    /*
    |--------------------------------------------------------------------------
    | Ad platforms with real 2025 character limits + format hints
    |--------------------------------------------------------------------------
    | Each platform lists the fields we want generated and their limits.
    | Limits reflect what each network ENFORCES (the technical max). The hint
    | tells the AI what part of that budget actually shows above the fold so
    | the most important content is front-loaded.
    |
    | Group keys: social | search | direct | web | commerce | content
    */
    'platforms' => [

        // ────────────────────────────────────────────────────────────────
        // SOCIAL MEDIA
        // ────────────────────────────────────────────────────────────────

        'facebook_feed' => [
            'label' => 'Facebook / Meta Feed',
            'icon'  => 'rectangle-group',
            'tint'  => 'blue',
            'group' => 'social',
            'description' => 'Standard single-image / video News Feed ad with primary text, headline, and link description.',
            'fields' => [
                'primary_text'   => ['label' => 'Primary Text',     'limit' => 500,  'hint' => 'Visible above the image. Mobile truncates around 125 chars — pack the hook & main value-prop in the first 125, expand below.'],
                'headline'       => ['label' => 'Headline',         'limit' => 40,   'hint' => 'Bold text under the image. Mobile truncates ~27 chars. Lead with the strongest benefit or offer.'],
                'description'    => ['label' => 'Link Description', 'limit' => 30,   'hint' => 'Small text under the headline. Often used as a CTA or trust signal (free shipping, 30-day trial).'],
            ],
        ],

        'instagram_feed' => [
            'label' => 'Instagram Feed',
            'icon'  => 'camera',
            'tint'  => 'pink',
            'group' => 'social',
            'description' => 'Feed post caption + hashtag bundle.',
            'fields' => [
                'hook'           => ['label' => 'First Line (Hook)','limit' => 125,  'hint' => 'The only line shown before "more". Must stop the scroll on its own — pose a tension, contrarian claim, or specific result.'],
                'caption'        => ['label' => 'Full Caption',     'limit' => 2200, 'hint' => 'Multi-paragraph caption. Use the hook as the first paragraph, then story / value / CTA. Line breaks via `\n\n`.'],
                'hashtags'       => ['label' => 'Hashtags',         'limit' => 300,  'hint' => '8–15 hashtags total: 3 broad, 5–8 niche, 2–3 branded. Space-separated. Max 30 hashtags allowed.'],
            ],
        ],

        'instagram_story' => [
            'label' => 'Instagram Story / Reel Overlay',
            'icon'  => 'squares-2x2',
            'tint'  => 'fuchsia',
            'group' => 'social',
            'description' => 'On-screen text for Stories and Reels (high-contrast, ultra-short, 9:16 safe-zone).',
            'fields' => [
                'hook'           => ['label' => 'Opening Hook (s1–2)','limit' => 60,  'hint' => 'On-screen text for the first 1–2 seconds. Big-font friendly — keep under ~6 words.'],
                'body'           => ['label' => 'Mid-card Text',    'limit' => 100,  'hint' => 'Supporting on-screen line that delivers the value. 1 short sentence, ~10–14 words.'],
                'caption'        => ['label' => 'Reel Caption',     'limit' => 2200, 'hint' => 'Caption shown below the Reel — use the hook + a tight version of the value-prop and a CTA.'],
                'cta'            => ['label' => 'CTA Sticker',      'limit' => 24,   'hint' => 'Swipe-up / tap-here phrase. 2–4 words, action verb.'],
            ],
        ],

        'tiktok' => [
            'label' => 'TikTok In-Feed Ad',
            'icon'  => 'musical-note',
            'tint'  => 'zinc',
            'group' => 'social',
            'description' => 'Native vertical video ad: spoken hook, on-screen text, caption, and hashtags.',
            'fields' => [
                'spoken_hook'    => ['label' => 'Spoken Hook (first 3s)','limit' => 100, 'hint' => 'What the creator literally says in the first 3 seconds. Pattern-interrupt, conversational, NOT salesy.'],
                'on_screen_text' => ['label' => 'On-Screen Text',   'limit' => 100,  'hint' => 'Bold overlay text. Up to 4 lines visible. Reinforces the spoken hook.'],
                'caption'        => ['label' => 'Caption',          'limit' => 2200, 'hint' => 'Conversational tone, lowercase ok. 1–3 short sentences plus hashtags. First 60 chars are the most-seen.'],
                'hashtags'       => ['label' => 'Hashtags',         'limit' => 200,  'hint' => '3–5 hashtags: mix of trending + niche. Include brand hashtag. Space-separated.'],
            ],
        ],

        'youtube_shorts' => [
            'label' => 'YouTube Shorts',
            'icon'  => 'play',
            'tint'  => 'red',
            'group' => 'social',
            'description' => 'Short-form vertical video — spoken hook, title, and short description.',
            'fields' => [
                'spoken_hook'    => ['label' => 'Spoken Hook (first 2s)','limit' => 100, 'hint' => 'Very first words spoken on camera. Pattern-interrupt or curiosity gap.'],
                'title'          => ['label' => 'Short Title',      'limit' => 100,  'hint' => 'Shows under the video. Front-load benefit + keyword. Includes #shorts is optional.'],
                'description'    => ['label' => 'Description',      'limit' => 700,  'hint' => 'Short paragraph. First 50 chars matter most. Add 2–3 hashtags at the end.'],
            ],
        ],

        'youtube' => [
            'label' => 'YouTube (Long-form)',
            'icon'  => 'play-circle',
            'tint'  => 'red',
            'group' => 'social',
            'description' => 'Standard YouTube video — SEO title, multi-paragraph description, and tags.',
            'fields' => [
                'title'          => ['label' => 'Video Title',      'limit' => 100,  'hint' => 'SEO + click-driving. Front-load the primary keyword + curiosity hook.'],
                'description'    => ['label' => 'Description',      'limit' => 5000, 'hint' => 'Full-form description. First 150 chars show in search snippets — make them count. Use paragraphs, timestamps (00:00), and a CTA.'],
                'tags'           => ['label' => 'Tags',             'limit' => 500,  'hint' => 'Comma-separated, 8–12 tags. Mix exact-match keyword, broad keyword, branded.'],
            ],
        ],

        'linkedin' => [
            'label' => 'LinkedIn Sponsored',
            'icon'  => 'briefcase',
            'tint'  => 'sky',
            'group' => 'social',
            'description' => 'Single-image / document Sponsored Content with intro text, headline, and link description.',
            'fields' => [
                'intro_text'     => ['label' => 'Intro Text',       'limit' => 600,  'hint' => 'Above the asset. First 150 chars before "see more". Professional, value-led, no hype.'],
                'headline'       => ['label' => 'Headline',         'limit' => 200,  'hint' => 'Below the asset. ~70 chars visible on mobile. Sharp, B2B-flavored.'],
                'description'    => ['label' => 'Link Description', 'limit' => 100,  'hint' => 'Optional small text under the headline. Adds urgency or proof.'],
            ],
        ],

        'linkedin_post' => [
            'label' => 'LinkedIn Organic Post',
            'icon'  => 'document-text',
            'tint'  => 'sky',
            'group' => 'social',
            'description' => 'Native LinkedIn post — long-form thought leadership, listicle, or story.',
            'fields' => [
                'hook'           => ['label' => 'First 2 lines',    'limit' => 210,  'hint' => 'Only ~210 chars show before "see more" on desktop (~140 mobile). Earn the click here.'],
                'post'           => ['label' => 'Full Post',        'limit' => 3000, 'hint' => 'Multi-paragraph. Short paragraphs (1–3 lines each). Use blank lines for white space. Land a clear takeaway and end with a discussion-starting question or CTA.'],
                'hashtags'       => ['label' => 'Hashtags',         'limit' => 100,  'hint' => '3–5 relevant professional hashtags placed at the end of the post.'],
            ],
        ],

        'x_twitter' => [
            'label' => 'X / Twitter Promoted',
            'icon'  => 'chat-bubble-left-right',
            'tint'  => 'zinc',
            'group' => 'social',
            'description' => 'Promoted Post (single tweet) plus optional follow-up reply.',
            'fields' => [
                'tweet'          => ['label' => 'Promoted Tweet',   'limit' => 280,  'hint' => 'Hook + value + CTA in 280 chars. Any link costs 23 chars — leave room. Conversational, no all-caps.'],
                'thread_reply'   => ['label' => 'Reply / Follow-up','limit' => 280,  'hint' => 'Optional second tweet expanding the offer or adding social proof.'],
            ],
        ],

        'threads' => [
            'label' => 'Threads (Meta)',
            'icon'  => 'queue-list',
            'tint'  => 'zinc',
            'group' => 'social',
            'description' => 'Threads post — longer than X, more conversational than LinkedIn.',
            'fields' => [
                'post'           => ['label' => 'Threads Post',     'limit' => 500,  'hint' => 'Up to 500 chars. Conversational, opinionated, lowercase ok. Lead with a hook line.'],
            ],
        ],

        'pinterest' => [
            'label' => 'Pinterest',
            'icon'  => 'bookmark',
            'tint'  => 'red',
            'group' => 'social',
            'description' => 'Pin title + description with strong SEO keywords (search-driven platform).',
            'fields' => [
                'title'          => ['label' => 'Pin Title',        'limit' => 100,  'hint' => 'First 30–40 chars are most visible in feeds. Benefit-driven, keyword-rich.'],
                'description'    => ['label' => 'Description',      'limit' => 500,  'hint' => 'Up to 500 in ads, 800 organic. First ~50 chars matter most. Naturally place primary + secondary keywords.'],
            ],
        ],

        'snapchat' => [
            'label' => 'Snapchat Ad',
            'icon'  => 'camera',
            'tint'  => 'amber',
            'group' => 'social',
            'description' => 'Snap Ads — short on-screen text inside 9:16 video creatives.',
            'fields' => [
                'brand_name'     => ['label' => 'Brand Name',       'limit' => 25,   'hint' => 'Visible in the ad chrome. Just the brand or product name.'],
                'headline'       => ['label' => 'Headline',         'limit' => 34,   'hint' => 'Big on-screen text under the brand. Punchy benefit, ~5 words.'],
                'description'    => ['label' => 'Description',      'limit' => 200,  'hint' => 'Optional supporting text or caption. Short, action-oriented.'],
            ],
        ],

        'reddit' => [
            'label' => 'Reddit Promoted Post',
            'icon'  => 'chat-bubble-bottom-center-text',
            'tint'  => 'zinc',
            'group' => 'social',
            'description' => 'Reddit-style promoted post — sound native to the subreddit, never salesy.',
            'fields' => [
                'title'          => ['label' => 'Post Title',       'limit' => 300,  'hint' => 'Reddit-native voice. No marketing speak. Curiosity, useful information, or controversial take.'],
                'body'           => ['label' => 'Post Body',        'limit' => 10000,'hint' => 'Conversational, value-first. Tell a story or share a finding. Mention the product organically near the end. Markdown allowed (use `**bold**`, `>quote`, `- list`).'],
            ],
        ],

        // ────────────────────────────────────────────────────────────────
        // SEARCH ADS
        // ────────────────────────────────────────────────────────────────

        'google_search' => [
            'label' => 'Google Search Ads (RSA)',
            'icon'  => 'magnifying-glass',
            'tint'  => 'emerald',
            'group' => 'search',
            'description' => 'Responsive Search Ad — Google rotates headlines + descriptions to find the best combos.',
            'fields' => [
                'headline_1'     => ['label' => 'Headline 1',       'limit' => 30,   'hint' => 'Include the primary keyword exactly.'],
                'headline_2'     => ['label' => 'Headline 2',       'limit' => 30,   'hint' => 'Lead with the biggest benefit.'],
                'headline_3'     => ['label' => 'Headline 3',       'limit' => 30,   'hint' => 'Include a CTA or differentiator.'],
                'headline_4'     => ['label' => 'Headline 4',       'limit' => 30,   'hint' => 'Social proof or specific result.'],
                'headline_5'     => ['label' => 'Headline 5',       'limit' => 30,   'hint' => 'Urgency / offer / scarcity.'],
                'description_1'  => ['label' => 'Description 1',    'limit' => 90,   'hint' => 'Unique selling point + benefit. Include keyword.'],
                'description_2'  => ['label' => 'Description 2',    'limit' => 90,   'hint' => 'Reinforce + CTA. End with action verb.'],
                'description_3'  => ['label' => 'Description 3',    'limit' => 90,   'hint' => 'Trust signal (guarantee, reviews, free trial).'],
                'path_1'         => ['label' => 'Display URL Path 1','limit' => 15,  'hint' => 'Optional path shown in URL (e.g. "free-trial").'],
                'path_2'         => ['label' => 'Display URL Path 2','limit' => 15,  'hint' => 'Optional second path (e.g. "no-cc-required").'],
            ],
        ],

        'microsoft_ads' => [
            'label' => 'Microsoft / Bing Ads',
            'icon'  => 'magnifying-glass-circle',
            'tint'  => 'sky',
            'group' => 'search',
            'description' => 'Microsoft Advertising RSA — same shape as Google, slightly older audience skew.',
            'fields' => [
                'headline_1'     => ['label' => 'Headline 1',       'limit' => 30,   'hint' => 'Primary keyword.'],
                'headline_2'     => ['label' => 'Headline 2',       'limit' => 30,   'hint' => 'Top benefit.'],
                'headline_3'     => ['label' => 'Headline 3',       'limit' => 30,   'hint' => 'CTA + offer.'],
                'description_1'  => ['label' => 'Description 1',    'limit' => 90,   'hint' => 'Unique angle + benefit + CTA.'],
                'description_2'  => ['label' => 'Description 2',    'limit' => 90,   'hint' => 'Trust signal + secondary CTA.'],
            ],
        ],

        'pmax_assets' => [
            'label' => 'Performance Max Assets',
            'icon'  => 'cube-transparent',
            'tint'  => 'emerald',
            'group' => 'search',
            'description' => 'Asset group for Google Performance Max — short headline, long headline, description, business name.',
            'fields' => [
                'short_headline' => ['label' => 'Short Headline',   'limit' => 30,   'hint' => 'Punchy, keyword-led.'],
                'long_headline'  => ['label' => 'Long Headline',    'limit' => 90,   'hint' => 'Full sentence value-prop. Used in display + YouTube.'],
                'description'    => ['label' => 'Description',      'limit' => 90,   'hint' => 'Single benefit + CTA.'],
                'business_name'  => ['label' => 'Business Name',    'limit' => 25,   'hint' => 'Brand name as it should appear.'],
            ],
        ],

        // ────────────────────────────────────────────────────────────────
        // DIRECT RESPONSE
        // ────────────────────────────────────────────────────────────────

        'email' => [
            'label' => 'Email Marketing',
            'icon'  => 'envelope',
            'tint'  => 'amber',
            'group' => 'direct',
            'description' => 'Marketing email — subject, preview, body, and CTA button.',
            'fields' => [
                'subject'        => ['label' => 'Subject Line',     'limit' => 60,   'hint' => 'Inbox shows ~30–40 chars on mobile. Curiosity + concrete promise. No spam triggers (FREE!!, $$$).'],
                'preview'        => ['label' => 'Preview Text',     'limit' => 110,  'hint' => 'Inbox preview after subject. Continue the thought, don\'t repeat the subject.'],
                'body'           => ['label' => 'Email Body',       'limit' => 2500, 'hint' => '4–7 short paragraphs. Open with a hook, build the case, social proof, single clear CTA. Line breaks via `\n\n`.'],
                'cta'            => ['label' => 'CTA Button',       'limit' => 30,   'hint' => 'Action verb + benefit. 2–4 words.'],
                'ps'             => ['label' => 'P.S. Line',        'limit' => 200,  'hint' => 'Optional. Short sentence under the signature reinforcing urgency or a bonus.'],
            ],
        ],

        'cold_outreach' => [
            'label' => 'Cold Outbound Email',
            'icon'  => 'paper-airplane',
            'tint'  => 'amber',
            'group' => 'direct',
            'description' => 'B2B sales-outreach email — short, personalized, value-first, single ask.',
            'fields' => [
                'subject'        => ['label' => 'Subject Line',     'limit' => 50,   'hint' => 'Casual, lowercase ok. Looks like an internal email, not a campaign.'],
                'opener'         => ['label' => 'Personalized Opener','limit' => 200,'hint' => 'A line that makes them sure you\'re not blasting. Reference their company, role, or recent news.'],
                'pitch'          => ['label' => 'Value Pitch',      'limit' => 400,  'hint' => 'In 2–3 sentences: who you help, the result you produce, why it matters to them specifically.'],
                'cta'            => ['label' => 'Soft CTA',         'limit' => 200,  'hint' => 'Low-friction ask. Question or specific time slot, not "let me know if interested".'],
            ],
        ],

        'sms' => [
            'label' => 'SMS / Push Notification',
            'icon'  => 'device-phone-mobile',
            'tint'  => 'violet',
            'group' => 'direct',
            'description' => '160-char SMS or short push message — single ask, link placeholder.',
            'fields' => [
                'message'        => ['label' => 'Message',          'limit' => 160,  'hint' => 'One SMS segment is 160 chars. Identify the brand, give one reason, include `{url}` link placeholder. Add `Reply STOP to opt out` only when required (counts toward limit).'],
            ],
        ],

        'whatsapp' => [
            'label' => 'WhatsApp Click-to-Chat / Broadcast',
            'icon'  => 'chat-bubble-left-ellipsis',
            'tint'  => 'emerald',
            'group' => 'direct',
            'description' => 'WhatsApp Business message — opener for click-to-chat ads or broadcast template.',
            'fields' => [
                'opener'         => ['label' => 'First Message',    'limit' => 300,  'hint' => 'The auto-message the prospect sees when they tap the WhatsApp ad. Conversational, warm, single question to start the chat.'],
                'follow_up'      => ['label' => 'Follow-up Message','limit' => 600,  'hint' => 'Auto-reply or scripted next message that delivers the offer / next step.'],
            ],
        ],

        // ────────────────────────────────────────────────────────────────
        // WEB & LANDING
        // ────────────────────────────────────────────────────────────────

        'landing_hero' => [
            'label' => 'Landing Page Hero',
            'icon'  => 'rocket-launch',
            'tint'  => 'indigo',
            'group' => 'web',
            'description' => 'Above-the-fold hero — H1, sub-headline, CTA button, social-proof line.',
            'fields' => [
                'headline'       => ['label' => 'Headline (H1)',    'limit' => 80,   'hint' => 'The single biggest promise. Concrete, specific, benefit-led. ~5–10 words.'],
                'subheadline'    => ['label' => 'Sub-headline',     'limit' => 200,  'hint' => 'Expand the H1. Who it\'s for, what it does, how it\'s different. 1–2 sentences.'],
                'cta'            => ['label' => 'Primary CTA',      'limit' => 30,   'hint' => 'Action verb + value. "Start free trial", "Get my plan".'],
                'secondary_cta'  => ['label' => 'Secondary CTA',    'limit' => 30,   'hint' => 'Lower-friction option (Watch demo, See pricing).'],
                'social_proof'   => ['label' => 'Social-Proof Line','limit' => 120,  'hint' => 'Trust marker — customer count, awards, well-known logos. "Trusted by 10,000+ teams".'],
            ],
        ],

        'landing_features' => [
            'label' => 'Landing Page — Features Section',
            'icon'  => 'sparkles',
            'tint'  => 'indigo',
            'group' => 'web',
            'description' => 'A grid of 3 feature blocks: short title + 1-paragraph benefit-led description.',
            'fields' => [
                'feature_1_title'       => ['label' => 'Feature 1 Title',       'limit' => 50,  'hint' => 'Benefit-led, not feature-led. ~3–5 words.'],
                'feature_1_description' => ['label' => 'Feature 1 Description', 'limit' => 250, 'hint' => '1–2 sentences. Lead with what the user gets.'],
                'feature_2_title'       => ['label' => 'Feature 2 Title',       'limit' => 50,  'hint' => 'Different benefit dimension than #1.'],
                'feature_2_description' => ['label' => 'Feature 2 Description', 'limit' => 250, 'hint' => 'Sustain the angle from #2 title.'],
                'feature_3_title'       => ['label' => 'Feature 3 Title',       'limit' => 50,  'hint' => 'Third distinct benefit (often integrations or scale).'],
                'feature_3_description' => ['label' => 'Feature 3 Description', 'limit' => 250, 'hint' => '1–2 sentences supporting the third title.'],
            ],
        ],

        'faq' => [
            'label' => 'Landing Page — FAQ',
            'icon'  => 'question-mark-circle',
            'tint'  => 'indigo',
            'group' => 'web',
            'description' => 'Five objection-handling Q&As — converts hesitant visitors.',
            'fields' => [
                'q1' => ['label' => 'Q1', 'limit' => 120, 'hint' => 'Common objection (price, time, fit).'],
                'a1' => ['label' => 'A1', 'limit' => 400, 'hint' => 'Empathetic, specific, dismantles the objection in 2–3 sentences.'],
                'q2' => ['label' => 'Q2', 'limit' => 120, 'hint' => 'Differentiation question ("How is this different from X?").'],
                'a2' => ['label' => 'A2', 'limit' => 400, 'hint' => 'Position vs alternatives without trash-talking competitors.'],
                'q3' => ['label' => 'Q3', 'limit' => 120, 'hint' => 'Onboarding / time-to-value question.'],
                'a3' => ['label' => 'A3', 'limit' => 400, 'hint' => 'Make the path to value concrete and short.'],
                'q4' => ['label' => 'Q4', 'limit' => 120, 'hint' => 'Money-back / trust question.'],
                'a4' => ['label' => 'A4', 'limit' => 400, 'hint' => 'State guarantee, refund policy, or trust signal directly.'],
                'q5' => ['label' => 'Q5', 'limit' => 120, 'hint' => 'Aspirational close ("What kind of results can I expect?").'],
                'a5' => ['label' => 'A5', 'limit' => 400, 'hint' => 'Specific outcome with caveat about effort. Builds belief.'],
            ],
        ],

        // ────────────────────────────────────────────────────────────────
        // E-COMMERCE
        // ────────────────────────────────────────────────────────────────

        'product_description' => [
            'label' => 'Product Page Copy',
            'icon'  => 'shopping-bag',
            'tint'  => 'zinc',
            'group' => 'commerce',
            'description' => 'Standalone e-commerce product page — title, bullets, full description.',
            'fields' => [
                'title'          => ['label' => 'Product Title',    'limit' => 100,  'hint' => 'Brand + main descriptor + key attribute. Front-load search keywords.'],
                'short_pitch'    => ['label' => 'Short Pitch',      'limit' => 200,  'hint' => 'The above-the-fold one-liner under the title. The main reason someone buys this.'],
                'bullets'        => ['label' => 'Key Benefit Bullets','limit' => 700,'hint' => '5 benefit-led bullets, each prefixed with `• `. Lead each bullet with the BENEFIT in bold-style caps then explain.'],
                'description'    => ['label' => 'Full Description', 'limit' => 1500, 'hint' => '3–4 short paragraphs. Story-style: who it\'s for, problem it solves, how it works, what\'s in the box / what to expect, guarantee.'],
            ],
        ],

        'amazon_listing' => [
            'label' => 'Amazon Product Listing',
            'icon'  => 'shopping-cart',
            'tint'  => 'amber',
            'group' => 'commerce',
            'description' => 'Amazon Sponsored Product / Brand listing — strict 200-char title, 5 bullets, 2000-char description.',
            'fields' => [
                'title'          => ['label' => 'Product Title',    'limit' => 200,  'hint' => 'Pattern: Brand + Product Type + Top Feature + Material + Size/Quantity. No promotional language ("BEST"). First 80 chars show on mobile.'],
                'bullet_1'       => ['label' => 'Bullet 1',         'limit' => 500,  'hint' => 'Lead the bullet with a CAPITAL benefit phrase, then explain. Most important benefit first.'],
                'bullet_2'       => ['label' => 'Bullet 2',         'limit' => 500,  'hint' => 'Second-most-important benefit or differentiator.'],
                'bullet_3'       => ['label' => 'Bullet 3',         'limit' => 500,  'hint' => 'Use case or compatibility detail.'],
                'bullet_4'       => ['label' => 'Bullet 4',         'limit' => 500,  'hint' => 'Quality / materials / warranty.'],
                'bullet_5'       => ['label' => 'Bullet 5',         'limit' => 500,  'hint' => 'Trust closer — guarantee, support, packaging.'],
                'description'    => ['label' => 'Description',      'limit' => 2000, 'hint' => 'Long-form description. Multi-paragraph. Story + benefits + how-to-use + closer. Used when A+ Content is unavailable.'],
            ],
        ],

        'shopify_collection' => [
            'label' => 'Shopify Collection Page',
            'icon'  => 'rectangle-stack',
            'tint'  => 'zinc',
            'group' => 'commerce',
            'description' => 'Collection / category page — short title and SEO-friendly description.',
            'fields' => [
                'title'          => ['label' => 'Collection Title', 'limit' => 70,   'hint' => 'Keyword-rich plural noun phrase ("Men\'s Trail Running Shoes").'],
                'description'    => ['label' => 'Collection Description','limit' => 800, 'hint' => '2–3 short paragraphs. Set the scene, name the buyer, name top benefits. Drop primary keyword 2–3 times naturally.'],
            ],
        ],

        // ────────────────────────────────────────────────────────────────
        // CONTENT & DISPLAY
        // ────────────────────────────────────────────────────────────────

        'blog_seo' => [
            'label' => 'Blog / SEO Article Frame',
            'icon'  => 'document-text',
            'tint'  => 'indigo',
            'group' => 'content',
            'description' => 'SEO frame for a blog article — meta title, meta description, H1, intro, outline.',
            'fields' => [
                'meta_title'     => ['label' => 'Meta Title (SEO)', 'limit' => 60,   'hint' => 'Front-load primary keyword. Add brand name at end.'],
                'meta_description'=> ['label' => 'Meta Description','limit' => 160,  'hint' => 'Front-load keyword + benefit. End with implicit CTA.'],
                'h1'             => ['label' => 'H1 Headline',      'limit' => 80,   'hint' => 'On-page title. Stronger / longer than meta title.'],
                'intro'          => ['label' => 'Intro Paragraph',  'limit' => 600,  'hint' => 'Hook, promise, why-trust-us, what the reader will get. 2–3 short paragraphs.'],
                'outline'        => ['label' => 'Section Outline (H2s)','limit' => 800, 'hint' => '5–7 H2 headings, each on its own line, listed as `## Heading`. Cover the topic from beginner question → advanced.'],
            ],
        ],

        'native_taboola' => [
            'label' => 'Native Ad (Taboola / Outbrain)',
            'icon'  => 'newspaper',
            'tint'  => 'zinc',
            'group' => 'content',
            'description' => 'Discovery ad shown in publisher feeds — looks like an editorial story.',
            'fields' => [
                'headline'       => ['label' => 'Headline',         'limit' => 60,   'hint' => 'Curiosity / list / news-style. Avoid hype words. ~50 chars sweet spot.'],
                'description'    => ['label' => 'Short Description','limit' => 150,  'hint' => 'Optional support copy. Editorial tone.'],
                'brand'          => ['label' => 'Brand / Source',   'limit' => 40,   'hint' => 'Name of the brand or publisher.'],
            ],
        ],

        'display_banner' => [
            'label' => 'Display Banner Overlay',
            'icon'  => 'photo',
            'tint'  => 'zinc',
            'group' => 'content',
            'description' => 'Static / HTML5 display ad — minimal text, ultra-tight.',
            'fields' => [
                'headline'       => ['label' => 'Headline',         'limit' => 35,   'hint' => 'Big text on the banner. ~4–6 words.'],
                'sub_text'       => ['label' => 'Sub-text',         'limit' => 60,   'hint' => 'Smaller line under the headline. One short benefit.'],
                'cta'            => ['label' => 'CTA',              'limit' => 18,   'hint' => 'Button text. Action verb + outcome.'],
            ],
        ],

        'press_release' => [
            'label' => 'Press Release',
            'icon'  => 'megaphone',
            'tint'  => 'sky',
            'group' => 'content',
            'description' => 'Newsworthy press release — headline, subhead, lead paragraph, body, boilerplate.',
            'fields' => [
                'headline'       => ['label' => 'Headline',         'limit' => 100,  'hint' => 'AP-style, factual, includes the news. No marketing fluff.'],
                'subheadline'    => ['label' => 'Sub-headline',     'limit' => 200,  'hint' => 'Supporting fact + value. One sentence.'],
                'lead'           => ['label' => 'Lead Paragraph',   'limit' => 500,  'hint' => 'Who, what, when, where, why — in the first paragraph. Dateline format.'],
                'body'           => ['label' => 'Body',             'limit' => 2500, 'hint' => 'Quote from a leader, supporting facts, customer / partner quote, additional context. 3–5 paragraphs.'],
                'boilerplate'    => ['label' => 'Boilerplate (About)','limit' => 600,'hint' => 'Standard "About [Company]" paragraph used in every release.'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform groups for UI grouping
    |--------------------------------------------------------------------------
    */
    'platform_groups' => [
        'social'   => ['label' => 'Social Media',     'icon' => 'users',                'tint' => 'pink'],
        'search'   => ['label' => 'Search Ads',       'icon' => 'magnifying-glass',     'tint' => 'emerald'],
        'direct'   => ['label' => 'Direct Response',  'icon' => 'envelope',             'tint' => 'amber'],
        'web'      => ['label' => 'Web & Landing',    'icon' => 'globe-alt',            'tint' => 'indigo'],
        'commerce' => ['label' => 'E-commerce',       'icon' => 'shopping-bag',         'tint' => 'teal'],
        'content'  => ['label' => 'Content & Display','icon' => 'newspaper',            'tint' => 'zinc'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Copywriting frameworks — proven formulas
    |--------------------------------------------------------------------------
    */
    'frameworks' => [
        'aida' => [
            'label' => 'AIDA',
            'full' => 'Attention → Interest → Desire → Action',
            'best_for' => 'General ads, quick conversions',
            'instruction' => 'Follow the AIDA framework: first grab ATTENTION with a bold hook, build INTEREST with a relatable insight, create DESIRE by painting the transformation, end with a clear ACTION.',
        ],
        'pas' => [
            'label' => 'PAS',
            'full' => 'Problem → Agitate → Solution',
            'best_for' => 'Pain-point-driven offers',
            'instruction' => 'Follow PAS: state the PROBLEM clearly, AGITATE the pain by making the reader feel the cost of inaction, then present the SOLUTION with the product.',
        ],
        'pasto' => [
            'label' => 'PASTOR',
            'full' => 'Problem → Amplify → Story → Transformation → Offer → Response',
            'best_for' => 'Long-form sales pages, webinars',
            'instruction' => 'Apply PASTOR: name the PROBLEM, AMPLIFY consequences, share a relevant STORY, paint the TRANSFORMATION, present the OFFER, ask for the RESPONSE.',
        ],
        'bab' => [
            'label' => 'BAB',
            'full' => 'Before → After → Bridge',
            'best_for' => 'Transformation stories',
            'instruction' => 'Follow BAB: paint the BEFORE state (the struggle), the AFTER state (the dream outcome), then the BRIDGE (how the product gets them there).',
        ],
        '4us' => [
            'label' => '4Us',
            'full' => 'Urgent, Unique, Useful, Ultra-specific',
            'best_for' => 'High-converting direct response',
            'instruction' => 'Make the copy URGENT (time-sensitive), UNIQUE (differentiated), USEFUL (clear benefit), and ULTRA-SPECIFIC (concrete numbers or details).',
        ],
        'fab' => [
            'label' => 'FAB',
            'full' => 'Features → Advantages → Benefits',
            'best_for' => 'Product marketing',
            'instruction' => 'Structure as FAB: list the FEATURE, explain the ADVANTAGE it provides, then translate into the BENEFIT for the reader.',
        ],
        'storytelling' => [
            'label' => 'Storytelling',
            'full' => 'Character → Conflict → Resolution',
            'best_for' => 'Brand ads, long-form',
            'instruction' => 'Tell a story: introduce a relatable CHARACTER, show their CONFLICT / struggle, and how the product brought RESOLUTION. Use sensory detail and emotion.',
        ],
        'question_hook' => [
            'label' => 'Question Hook',
            'full' => 'Provocative question → Answer → CTA',
            'best_for' => 'Engagement, curiosity',
            'instruction' => 'Open with a provocative QUESTION that makes the reader stop scrolling. Give the ANSWER that naturally leads to the product. End with a direct CTA.',
        ],
        'direct' => [
            'label' => 'Direct Benefit',
            'full' => 'Lead with the biggest benefit',
            'best_for' => 'Warm audiences, retargeting',
            'instruction' => 'Lead with the single biggest BENEFIT — no preamble. Be punchy and specific. Support with 1-2 proof points. Close with CTA.',
        ],
        'soap' => [
            'label' => 'SOAP',
            'full' => 'Subject → Opening → Acknowledgement → Problem',
            'best_for' => 'Cold outbound email',
            'instruction' => 'For cold outreach: SUBJECT that earns the open, OPENING that proves you did your homework, ACKNOWLEDGE their context, name the PROBLEM you solve, then a soft ask.',
        ],
        'foursteps' => [
            'label' => '4-Step Sales Letter',
            'full' => 'Promise → Picture → Proof → Push',
            'best_for' => 'Long-form sales emails, landing pages',
            'instruction' => 'Make a big PROMISE, paint a PICTURE of the outcome, show PROOF (data, testimonials, demos), PUSH for action with urgency.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tones of voice
    |--------------------------------------------------------------------------
    */
    'tones' => [
        'professional'  => 'Professional & authoritative',
        'friendly'      => 'Friendly & conversational',
        'bold'          => 'Bold & confident',
        'playful'       => 'Playful & witty',
        'luxurious'     => 'Luxurious & aspirational',
        'minimal'       => 'Minimal & understated',
        'urgent'        => 'Urgent & scarcity-driven',
        'inspirational' => 'Inspirational & motivating',
        'empathetic'    => 'Empathetic & caring',
        'edgy'          => 'Edgy & provocative',
        'technical'     => 'Technical & precise',
        'humorous'      => 'Humorous & light-hearted',
        'storyteller'   => 'Storyteller / narrative',
    ],

    /*
    |--------------------------------------------------------------------------
    | Campaign objectives — used to bias the AI
    |--------------------------------------------------------------------------
    */
    'objectives' => [
        'awareness'     => ['label' => 'Brand Awareness',      'icon' => 'eye',                  'hint' => 'Get noticed, build recognition'],
        'leads'         => ['label' => 'Lead Generation',      'icon' => 'user-plus',            'hint' => 'Capture emails & sign-ups'],
        'sales'         => ['label' => 'Sales & Conversion',   'icon' => 'shopping-cart',        'hint' => 'Drive immediate purchases'],
        'traffic'       => ['label' => 'Website Traffic',      'icon' => 'cursor-arrow-rays',    'hint' => 'Clicks & page views'],
        'engagement'    => ['label' => 'Engagement',           'icon' => 'heart',                'hint' => 'Likes, shares, comments'],
        'app_install'   => ['label' => 'App Install',          'icon' => 'device-phone-mobile',  'hint' => 'Drive downloads'],
        'retargeting'   => ['label' => 'Retargeting',          'icon' => 'arrow-path',           'hint' => 'Re-engage visitors'],
        'consideration' => ['label' => 'Consideration',        'icon' => 'light-bulb',           'hint' => 'Educate mid-funnel buyers'],
        'demo'          => ['label' => 'Book a Demo',          'icon' => 'calendar-days',        'hint' => 'Sales-assisted B2B funnel'],
        'event'         => ['label' => 'Event / Webinar',      'icon' => 'megaphone',            'hint' => 'Drive registrations'],
        'launch'        => ['label' => 'Product Launch',       'icon' => 'rocket-launch',        'hint' => 'Build anticipation around a new release'],
    ],

    /*
    |--------------------------------------------------------------------------
    | CTA library — quick picks
    |--------------------------------------------------------------------------
    */
    'cta_library' => [
        'Shop Now', 'Buy Now', 'Add to Cart', 'Get Started', 'Learn More',
        'Sign Up', 'Try Free', 'Start Free Trial', 'Book a Demo',
        'Schedule a Call', 'Download', 'Download Free', 'Join Waitlist',
        'Claim Offer', 'Get the Discount', 'Subscribe', 'Get Quote',
        'See Pricing', 'Compare Plans', 'Watch Demo', 'See It in Action',
        'Order Now', 'Reserve My Spot', 'Save My Seat', 'Get Instant Access',
        'Apply Now', 'Talk to Sales', 'Request Access',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI engine settings
    |--------------------------------------------------------------------------
    | Engines and their models now live in the `text_models` database table
    | (see App\Models\TextModel, the TextModelSeeder, and the DB-backed
    | App\Services\AdCopy\Support\EngineRegistry). This config no longer
    | defines engines or models — manage them in the database instead.
    */
];
