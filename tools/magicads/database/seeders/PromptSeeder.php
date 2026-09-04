<?php

namespace Database\Seeders;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the prompt library with a curated set of global prompts that are
 * available to every user inside the Image Studio (Creative Description)
 * and Video Studio (Scene Description).
 *
 * Idempotent: uses updateOrCreate keyed on (title, type, is_global), so
 * re-running won't duplicate the curated set and refreshes the body text.
 */
class PromptSeeder extends Seeder
{
    public function run(): void
    {
        // Curated prompts are owned by an admin so they show as "Curated"
        // and pass the is_global visibility scope for all users.
        $ownerId = User::role('admin')->value('id') ?? User::query()->value('id');

        foreach ($this->imagePrompts() as $prompt) {
            Prompt::updateOrCreate(
                ['title' => $prompt['title'], 'type' => 'image', 'is_global' => true],
                ['body' => $prompt['body'], 'user_id' => $ownerId],
            );
        }

        foreach ($this->videoPrompts() as $prompt) {
            Prompt::updateOrCreate(
                ['title' => $prompt['title'], 'type' => 'video', 'is_global' => true],
                ['body' => $prompt['body'], 'user_id' => $ownerId],
            );
        }
    }

    /**
     * 20 detailed image-ad prompts for the Image Studio.
     *
     * @return array<int, array{title: string, body: string}>
     */
    protected function imagePrompts(): array
    {
        return [
            [
                'title' => 'Hero Product on Seamless Studio Backdrop',
                'body'  => 'A premium product centered on a seamless gradient backdrop transitioning from soft warm beige to muted cream, lit by a large softbox from the upper left for gentle wraparound highlights and a subtle reflection beneath. Shot at a slight three-quarter angle with a shallow depth of field, crisp focus on the label, faint rim light separating the product from the background. Clean negative space at the top for a headline. Professional commercial product photography, ultra sharp, color-accurate, 4K advertising quality.',
            ],
            [
                'title' => 'Lifestyle Flat Lay with Natural Props',
                'body'  => 'A top-down flat lay on a textured oak surface, the product as the hero in the center surrounded by complementary lifestyle props — fresh eucalyptus sprigs, a linen napkin, a ceramic mug, and scattered coffee beans. Soft diffused morning light from the right casting long natural shadows. Warm, earthy color palette with balanced composition and breathing room around the edges. Editorial lifestyle photography, inviting and authentic, magazine-quality styling.',
            ],
            [
                'title' => 'Bold Color-Block Promo with Price Burst',
                'body'  => 'A vibrant color-blocked layout split diagonally between a saturated brand-color field and white, the product floating dynamically in the center with a soft drop shadow. A bold circular "starburst" badge in the top-right corner reserved for a discount percentage. High-energy, punchy contrast, modern sans-serif friendly negative space for headline and CTA. Eye-catching retail sale aesthetic, clean vector-style backdrop, crisp studio lighting on the product.',
            ],
            [
                'title' => 'Floating Product with Dynamic Splash',
                'body'  => 'The product suspended mid-air against a deep moody background, surrounded by a dramatic splash of liquid or powder frozen in motion, droplets catching sharp specular highlights. Strong directional key light from the side creating bold contrast and depth, subtle blue-and-amber color contrast in the splash. Sense of energy and freshness, high-speed flash photography look, hyper-detailed, premium beverage or cosmetics advertising style.',
            ],
            [
                'title' => 'Minimalist Scandinavian Scene',
                'body'  => 'A calm minimalist composition with the product placed on a pale plaster pedestal, set against a soft off-white wall. A single arched shadow falls across the background from warm side lighting. Muted neutral palette of bone, sand, and stone with one subtle accent of greenery. Generous negative space, balanced asymmetry, serene and sophisticated mood. Clean modern product photography, soft natural light, understated luxury aesthetic.',
            ],
            [
                'title' => 'Gradient Tech Glow Showcase',
                'body'  => 'A sleek tech product displayed at a hero three-quarter angle on a dark surface, illuminated by an indigo-to-amber gradient glow emanating from behind it. Subtle reflections on a glossy black floor, soft neon rim lighting tracing the product edges, faint floating particles of light in the background. Futuristic premium feel, high contrast, polished and modern. Cinematic product render, sharp detail, advertising-grade lighting.',
            ],
            [
                'title' => 'Before & After Split Comparison',
                'body'  => 'A clean vertical split-screen composition: the left side dim and desaturated representing "before", the right side bright, vibrant, and warm representing "after", with a crisp dividing line down the center. The product positioned prominently on the "after" side bathed in flattering light. Clear visual storytelling of transformation, balanced layout with space for short labels on each side. Polished commercial photography, persuasive and easy to read.',
            ],
            [
                'title' => 'Outdoor Golden-Hour Lifestyle',
                'body'  => 'A person joyfully using the product outdoors during golden hour, warm low sunlight creating a glowing rim around their hair and lens flare in the corner. Blurred natural background of a park or city street with soft bokeh. Candid, authentic expression, shallow depth of field keeping the subject and product in sharp focus. Aspirational lifestyle advertising, warm cinematic color grade, genuine human connection.',
            ],
            [
                'title' => 'Luxury Dark & Gold Elegance',
                'body'  => 'The product presented on a black marble surface with subtle gold veining, lit by a tight pool of warm light that fades into deep shadow at the edges. Delicate gold accents and a faint reflection beneath the product. Rich, opulent, high-end mood with dramatic chiaroscuro lighting. Plenty of dark negative space for elegant typography. Premium luxury brand photography, moody and refined, ultra-detailed.',
            ],
            [
                'title' => 'Playful Pastel Pop Composition',
                'body'  => 'A cheerful composition on a soft pastel pink and lavender background with floating geometric shapes — circles, arcs, and confetti — arranged playfully around the centered product. Bright even lighting, candy-like colors, soft shadows, a fun and youthful energy. Balanced and airy layout with room for a bold headline. Modern social-media-ready advertising aesthetic, vibrant yet clean.',
            ],
            [
                'title' => 'Ingredient-Forward Fresh Burst',
                'body'  => 'The product surrounded by its key natural ingredients arranged in a dynamic arc — fresh fruit slices, herbs, or botanicals — some mid-air as if dropping, droplets of water suspended around them. Bright, fresh, high-key lighting on a clean white-to-mint gradient background. Crisp focus showcasing texture and freshness, appetizing and natural. Premium food, beverage, or skincare advertising photography.',
            ],
            [
                'title' => 'Urban Street Style Hero',
                'body'  => 'A confident model holding or wearing the product against a textured urban backdrop — a graffiti wall or weathered concrete — during overcast diffused daylight. Streetwear aesthetic, slightly desaturated cinematic color grade with cool tones, shallow depth of field. Dynamic off-center composition with the subject looking just past the camera. Bold, edgy, contemporary fashion advertising mood, authentic and aspirational.',
            ],
            [
                'title' => 'Cozy Warm Interior Mood',
                'body'  => 'The product styled within a cozy home interior — on a wooden side table beside a softly glowing lamp, a knit throw blanket, and an out-of-focus warm-lit living room behind. Inviting amber tones, soft window light blending with warm artificial light, gentle shadows. Comfortable, homely, and relatable atmosphere. Lifestyle interior photography with shallow depth of field, warm and welcoming brand feel.',
            ],
            [
                'title' => 'Macro Texture & Detail Close-Up',
                'body'  => 'An extreme macro close-up highlighting the rich texture and craftsmanship of the product — visible material grain, stitching, droplets, or surface finish — with razor-sharp focus on the detail and a creamy blurred falloff. Soft directional lighting that rakes across the surface to emphasize depth and tactility. Intimate, premium, quality-focused mood. High-resolution macro product photography, abstract and striking.',
            ],
            [
                'title' => 'Floating Geometric Pedestals',
                'body'  => 'The product elevated on a floating geometric podium amid a surreal scene of pastel pedestals, arches, and spheres arranged at varying depths. Soft studio lighting with gentle gradient shadows, a dreamy minimal 3D-render aesthetic, harmonious muted color palette. Balanced composition with the product as the clear focal point and ample clean space. Modern, trendy, premium product staging, crisp and polished.',
            ],
            [
                'title' => 'High-Energy Sports Action',
                'body'  => 'A dynamic action shot of an athlete in motion using the product, frozen at the peak of movement with sweat droplets and dust kicked up around them. Dramatic stadium or outdoor lighting with strong rim light, motion-blur streaks in the background conveying speed and power. Bold, intense, adrenaline-charged mood, high contrast and saturated. Athletic performance advertising, cinematic and powerful.',
            ],
            [
                'title' => 'Clean SaaS / App UI Showcase',
                'body'  => 'A modern device mockup — sleek smartphone and laptop — displaying a clean app interface, floating at a slight angle on a soft indigo-to-slate gradient background. Subtle soft shadows beneath the devices, faint floating UI cards and notification bubbles around them suggesting features. Crisp, professional, tech-forward aesthetic with generous space for a headline. Polished SaaS marketing visual, sharp and contemporary.',
            ],
            [
                'title' => 'Seasonal Festive Celebration',
                'body'  => 'The product nestled in a warm festive scene with soft twinkling string lights bokeh in the background, evergreen sprigs, subtle metallic ornaments, and a dusting of faux snow. Cozy warm-and-cool color balance, gentle glowing highlights, celebratory yet tasteful styling. Inviting holiday mood with space reserved for a seasonal offer headline. Premium seasonal campaign photography, magical and warm.',
            ],
            [
                'title' => 'Monochrome Editorial Statement',
                'body'  => 'A striking high-contrast black-and-white editorial composition with the product as a bold focal point, dramatic single-source lighting carving deep shadows and bright highlights. Strong graphic shapes, confident negative space, timeless and sophisticated mood. A single subtle pop of brand color on the product for emphasis. Fine-art advertising photography, minimalist, premium, gallery-worthy.',
            ],
            [
                'title' => 'Aerial Top-Down Lifestyle Spread',
                'body'  => 'A beautifully arranged top-down hero shot of the product surrounded by a curated spread of complementary items, organized in a balanced grid-like composition on a richly textured surface. Even soft daylight with gentle natural shadows, cohesive color story, abundant detail yet uncluttered. Inviting "knolling" lifestyle aesthetic with breathing room for branding. Premium editorial flat-lay photography, crisp and stylish.',
            ],
        ];
    }

    /**
     * 20 detailed video-ad prompts for the Video Studio.
     *
     * @return array<int, array{title: string, body: string}>
     */
    protected function videoPrompts(): array
    {
        return [
            [
                'title' => 'Cinematic Product Reveal',
                'body'  => 'Open on a dark, moody scene with soft volumetric light beams. The product slowly emerges from shadow, rotating gently as a spotlight sweeps across its surface revealing glossy highlights and fine detail. Subtle particles of light drift through the air. The camera pushes in slowly with shallow depth of field. As the product reaches full light, the brand logo fades in elegantly beneath it, followed by a clean call-to-action. Premium, cinematic, slow and deliberate pacing.',
            ],
            [
                'title' => 'Fast-Paced Unboxing Sequence',
                'body'  => 'A series of quick, snappy close-up shots: hands picking up the package, fingers pulling the seal, the lid lifting to reveal the product inside, glints of light on the packaging. Each cut is tight and satisfying, synced to an upbeat rhythm with subtle whoosh transitions. The final shot pulls back to show the fully unboxed product hero-lit on a clean surface, then the logo and tagline animate in. Energetic, modern, tactile and exciting.',
            ],
            [
                'title' => 'UGC Selfie Testimonial',
                'body'  => 'A relatable everyday person films themselves selfie-style in natural home lighting, smiling and talking enthusiastically to the camera while holding the product. Handheld slightly shaky authentic feel, casual setting like a kitchen or living room. They gesture toward the product, show it close to the lens, and react with genuine delight. Quick zoom-in for emphasis on a key moment. Ends with them giving a thumbs up. Authentic, friendly, social-first UGC style.',
            ],
            [
                'title' => 'Lifestyle Day-in-the-Life Montage',
                'body'  => 'A warm montage following a person through their day seamlessly integrating the product into real moments — morning routine by a sunlit window, commuting through the city, a productive afternoon, a relaxed evening. Smooth match-cuts between scenes, golden natural lighting, candid genuine expressions. The product appears naturally in each beat as a helpful companion. Aspirational yet down-to-earth, warm cinematic color grade, uplifting pacing.',
            ],
            [
                'title' => 'Dramatic Problem-to-Solution Story',
                'body'  => 'Open on a relatable frustration shown in dim, desaturated tones — someone struggling with the problem your product solves, sighing in mild defeat. A beat of tension. Then the product is introduced and the scene instantly brightens, color floods back, and the person\'s expression shifts to relief and joy as they use it effortlessly. Camera moves from static and heavy to dynamic and light. Ends on a satisfied smile with the logo and CTA. Emotional, clear narrative arc.',
            ],
            [
                'title' => 'Floating Hero with Particle FX',
                'body'  => 'The product floats and slowly rotates in the center of a sleek gradient void, suspended weightlessly. Glowing particles and soft light streaks orbit around it, occasionally catching its edges with rim light. The camera arcs smoothly around the product in a 360-style move, revealing every angle. Feature callout text elegantly fades in and out beside the product at key rotations. Ends with the logo lockup. Futuristic, premium, hypnotic and smooth.',
            ],
            [
                'title' => 'Bold Flash-Sale Countdown',
                'body'  => 'High-energy opener: a bold price tag or discount percentage slams into frame with an impact shake and particle burst. Quick rhythmic cuts of the product from multiple angles, each punctuated by snappy graphic wipes in brand colors. A countdown timer ticks urgently in the corner. Text callouts like the offer and deadline pop on screen with kinetic motion. Ends on a strong pulsing call-to-action button. Urgent, punchy, conversion-driven.',
            ],
            [
                'title' => 'Slow-Motion Sensory Close-Ups',
                'body'  => 'A sequence of luxurious slow-motion macro shots emphasizing texture and sensation — liquid pouring and rippling, fabric flowing, droplets landing, light refracting through the product. Each shot lingers, beautifully lit with soft directional light and shallow focus. Rich, tactile, almost meditative pacing. The product is woven throughout as the source of these sensory moments. Concludes with the product hero shot and a refined logo reveal. Elegant and immersive.',
            ],
            [
                'title' => 'Split-Screen Comparison',
                'body'  => 'A clean split-screen runs two scenes side by side: the left showing the dull "ordinary" way (desaturated, sluggish), the right showing life with the product (vibrant, smooth, fast). Synchronized actions on both sides highlight the contrast frame by frame. Midway, the divider wipes away and the "with product" side takes over the full frame in full color. Ends with the product, a benefit headline, and CTA. Persuasive, clear, visually satisfying.',
            ],
            [
                'title' => 'Energetic Dance / Trend Hook',
                'body'  => 'Open on a confident person in a stylish, well-lit setting bursting into an upbeat, trendy dance move that immediately hooks attention. Quick beat-synced cuts, vibrant colors, dynamic camera that bounces with the rhythm. They incorporate the product playfully into the choreography, showing it off naturally. Text overlay with a catchy hook appears early. High replay-value energy. Ends with the product front and center and a snappy CTA. Fun, viral, social-native.',
            ],
            [
                'title' => 'Aspirational Brand Anthem',
                'body'  => 'A sweeping cinematic montage of inspiring wide shots — vast landscapes, determined people, moments of triumph — intercut with the product appearing as part of the journey. Epic, emotive pacing that builds toward a crescendo. Warm heroic lighting, slow-motion hero moments, a sense of purpose and belonging. Minimal text, letting visuals carry the emotion, before resolving on the brand logo and a powerful tagline. Premium, emotional, brand-building storytelling.',
            ],
            [
                'title' => 'App Screen Walkthrough',
                'body'  => 'A sleek smartphone floats at a slight angle against a soft gradient background, its screen animating through the app\'s key features with smooth UI transitions — tapping, swiping, and results appearing in real time. A finger or cursor guides the journey. Floating callout labels highlight each feature as it appears. Clean, modern, tech-forward look with subtle depth and soft shadows. Ends with the app icon, name, and download CTA. Crisp and professional.',
            ],
            [
                'title' => 'Cozy ASMR Routine',
                'body'  => 'An intimate, softly lit close-up sequence following a calming routine with the product — gentle pouring, soft taps, smooth application, satisfying small actions. Warm ambient lighting, shallow focus, slow and soothing pacing that invites relaxation. Emphasis on tactile, sensory micro-moments. The atmosphere is quiet and comforting. Ends gently with the product resting in frame and a soft logo fade-in. Calming, sensory, premium and inviting.',
            ],
            [
                'title' => 'Quick Tutorial in 3 Steps',
                'body'  => 'A bright, clear how-to broken into three concise on-screen steps, each introduced by a bold number animating into frame. Clean overhead and close-up shots demonstrate each step simply and clearly with the product. Snappy transitions between steps, friendly and well-lit setting, easy-to-follow pacing. After step three, a satisfying reveal of the finished result. Ends with the product and a CTA. Helpful, approachable, informative and quick.',
            ],
            [
                'title' => 'Dramatic Liquid / Splash Showcase',
                'body'  => 'Shot in crisp high-speed slow motion, the product is surrounded by dynamic splashes of liquid or bursts of powder erupting and freezing mid-air around it. Droplets catch sharp specular highlights against a deep contrasting background. The camera holds steady as the chaos blooms gracefully, then settles. Bold, fresh, high-impact energy. Resolves on the clean product hero shot with logo and tagline. Striking, premium beverage or cosmetics style.',
            ],
            [
                'title' => 'Founder / Behind-the-Scenes Story',
                'body'  => 'A warm, documentary-style sequence: the founder or team shown authentically at work — crafting, designing, or testing the product — interspersed with sincere direct-to-camera moments sharing the brand\'s mission. Natural lighting, handheld intimacy, genuine emotion. Real workspace details build trust and authenticity. The product is shown as the result of passion and care. Ends with a heartfelt message, the logo, and CTA. Honest, human, trust-building.',
            ],
            [
                'title' => 'Seasonal Holiday Spot',
                'body'  => 'A heartwarming festive scene with twinkling lights bokeh, cozy interiors, and gentle falling snow outside the window. Loved ones gather and share a joyful moment, with the product appearing as part of the celebration — a perfect gift or shared experience. Warm-and-cool color balance, soft glowing highlights, tender emotional pacing. A seasonal offer headline appears gracefully. Ends with the logo amid festive sparkle. Magical, warm, emotionally resonant.',
            ],
            [
                'title' => 'Punchy Feature Highlight Reel',
                'body'  => 'A rapid, high-energy reel cycling through the product\'s top features, each shown in a tight dynamic shot paired with a bold animated text callout. Kinetic transitions — quick zooms, whip pans, and graphic wipes in brand colors — keep momentum high. Upbeat rhythm, vibrant and crisp visuals, every second earning attention. Builds to a final montage flash of all features, then the product, logo, and a strong CTA. Modern, confident, attention-grabbing.',
            ],
            [
                'title' => 'Transformation Time-Lapse',
                'body'  => 'A satisfying time-lapse capturing a visible transformation powered by the product — a space being organized, a result developing, a project coming together — compressed into smooth accelerated motion. Stable locked-off framing with light naturally shifting to show passage of time. A clear, gratifying before-to-after payoff. The product is present throughout as the catalyst. Ends on the finished result with a benefit headline, logo, and CTA. Impressive and convincing.',
            ],
            [
                'title' => 'Testimonial Mashup with B-Roll',
                'body'  => 'A dynamic edit weaving together short snippets of happy customers speaking enthusiastically about the product, intercut with crisp b-roll of the product in real use. Warm authentic lighting, genuine smiles and reactions, on-screen captions reinforcing key quotes and star ratings. Upbeat but trustworthy pacing that builds social proof quickly. Closes on a strong combined endorsement, the product, logo, and a confident CTA. Credible, relatable, persuasive.',
            ],
        ];
    }
}
