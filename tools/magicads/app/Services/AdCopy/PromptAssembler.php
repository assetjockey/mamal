<?php

namespace App\Services\AdCopy;

use App\Models\Brand;

class PromptAssembler
{
    /**
     * Build the full prompt sent to the AI.
     *
     * The prompt is engineered to:
     *  - Force the model to consume every non-empty user input (brief, audience,
     *    benefits, keywords, CTA, extra instructions, brand voice, tone,
     *    framework, objective).
     *  - Demand quality output that *uses the available space* on each platform
     *    instead of returning a single short sentence — long-form fields (>=300
     *    chars) get multi-paragraph requirements, mid fields (100–300) get
     *    multi-sentence with line breaks where natural, short fields stay tight
     *    but at 60–95% of the limit.
     *  - Produce variants that differ in angle/hook, not surface wording.
     *
     * @return string  Ready-to-send prompt (paired with JSON-mode on the AI side)
     */
    public function assemble(array $input, ?Brand $brand = null): string
    {
        $platform = config("ad-copy.platforms.{$input['platform']}");
        $platformLabel = $platform['label'] ?? $input['platform'];
        $platformDescription = $platform['description'] ?? '';
        $platformGroupKey = $platform['group'] ?? null;
        $platformGroup = $platformGroupKey ? config("ad-copy.platform_groups.{$platformGroupKey}") : null;

        $fields = $platform['fields'] ?? [];
        $framework = ! empty($input['framework']) ? config("ad-copy.frameworks.{$input['framework']}") : null;
        $tone = ! empty($input['tone'])
            ? (config("ad-copy.tones.{$input['tone']}") ?? ucfirst($input['tone']))
            : null;
        $objective = ! empty($input['objective']) ? config("ad-copy.objectives.{$input['objective']}") : null;
        $variants = max(1, (int) ($input['variants'] ?? 3));
        $language = ! empty($input['language']) ? $input['language'] : 'English';

        $lines = [];

        // ──────────────────────────────────────────────────────────────────
        // 1. Role
        // ──────────────────────────────────────────────────────────────────
        $lines[] = "You are a senior direct-response copywriter and paid-media strategist with 15 years of experience writing ads that consistently beat control across Meta, Google, TikTok, LinkedIn, and email.";
        $lines[] = "You write in {$language}. You only output JSON.";
        $lines[] = "";

        // ──────────────────────────────────────────────────────────────────
        // 2. The mission
        // ──────────────────────────────────────────────────────────────────
        $lines[] = "# MISSION";
        $lines[] = "Produce exactly {$variants} ad-copy variant(s) for the **{$platformLabel}** placement"
            . ($platformGroup ? " (channel category: {$platformGroup['label']})" : '')
            . ".";
        if ($platformDescription) {
            $lines[] = "Placement format: {$platformDescription}";
        }
        $lines[] = "Each variant must use a *distinct angle* — different hook, different emotional driver, different value-prop framing. Variants are not paraphrases of each other.";
        $lines[] = "";

        // ──────────────────────────────────────────────────────────────────
        // 3. Brand context (if present) — full, verbatim
        // ──────────────────────────────────────────────────────────────────
        if ($brand) {
            $brandContext = trim($brand->toPromptContext());
            if ($brandContext !== '') {
                $lines[] = "# BRAND CONTEXT — match this voice exactly";
                $lines[] = $brandContext;
                $lines[] = "Stay strictly on-brand. Every sentence should sound like it came from this company.";
                $lines[] = "";
            }
        }

        // ──────────────────────────────────────────────────────────────────
        // 4. Campaign brief — only non-empty fields, all marked MUST-USE
        // ──────────────────────────────────────────────────────────────────
        $brief = [];
        if (! empty($input['product_description'])) {
            $brief[] = ['Product / Offer', $input['product_description'], true];
        }
        if (! empty($input['target_audience'])) {
            $brief[] = ['Target audience', $input['target_audience'], true];
        }
        if (! empty($input['key_benefits'])) {
            $brief[] = ['Key benefits to emphasize', $input['key_benefits'], true];
        }
        if (! empty($input['keywords'])) {
            $brief[] = ['Keywords to weave in naturally (no stuffing)', $input['keywords'], true];
        }
        if (! empty($input['cta'])) {
            $brief[] = ['Preferred call-to-action', $input['cta'], true];
        }
        if ($objective) {
            $brief[] = ['Campaign objective', "{$objective['label']} — {$objective['hint']}", true];
        }
        if (! empty($input['extra_instructions'])) {
            $brief[] = ['Extra instructions from the marketer (HIGH PRIORITY)', $input['extra_instructions'], true];
        }

        if (! empty($brief)) {
            $lines[] = "# CAMPAIGN BRIEF — every item below is MANDATORY context. Do not ignore any.";
            foreach ($brief as [$label, $value, $mustUse]) {
                $marker = $mustUse ? '[MUST USE]' : '';
                $lines[] = trim("- {$label} {$marker}: " . $this->cleanValue($value));
            }
            $lines[] = "";
            $lines[] = "Reflect the audience's pain and the product's specific benefits. Use the keywords organically. End with the preferred CTA when the field naturally calls for one.";
            $lines[] = "";
        }

        // ──────────────────────────────────────────────────────────────────
        // 5. Voice, tone, framework
        // ──────────────────────────────────────────────────────────────────
        $lines[] = "# VOICE & STRUCTURE";
        if ($tone) {
            $lines[] = "Tone: {$tone}. Maintain this tone consistently across every field of every variant.";
        }
        if ($framework) {
            $lines[] = "Copywriting framework: **{$framework['label']}** ({$framework['full']}).";
            $lines[] = "How to apply it: {$framework['instruction']}";
            $lines[] = "Apply this framework inside the longer fields (body, caption, description). Short fields (headlines, CTAs) reflect the climax of the framework.";
        }
        $lines[] = "Language: {$language}. Do not switch languages mid-copy.";
        $lines[] = "";

        // ──────────────────────────────────────────────────────────────────
        // 6. Field-by-field constraints with target lengths and structure
        // ──────────────────────────────────────────────────────────────────
        $lines[] = "# FIELDS TO WRITE — strict per-field rules";
        $lines[] = "For EACH variant, produce ALL of the following fields. Every field must be substantive and use the space available — never one short sentence when the limit allows a paragraph.";
        $lines[] = "";

        foreach ($fields as $slug => $meta) {
            $limit = (int) ($meta['limit'] ?? 200);
            [$min, $max, $shape] = $this->fieldShape($limit);

            $lines[] = "- `{$slug}` — {$meta['label']}";
            $lines[] = "    Hard max: {$limit} characters. Target length: {$min}–{$max} characters.";
            $lines[] = "    Structure: {$shape}";
            if (! empty($meta['hint'])) {
                $lines[] = "    Hint: {$meta['hint']}";
            }
        }
        $lines[] = "";

        // ──────────────────────────────────────────────────────────────────
        // 7. Quality bar (anti-thin-output)
        // ──────────────────────────────────────────────────────────────────
        $lines[] = "# QUALITY BAR — non-negotiable";
        $lines[] = "1. NO thin output. Long-form fields (>= 300 char limit) MUST be multi-paragraph, fully formed, and read like a finished ad — not a placeholder sentence.";
        $lines[] = "2. NO generic marketing fluff (\"unlock your potential\", \"take it to the next level\", \"game-changing\"). Use concrete numbers, names, scenarios.";
        $lines[] = "3. NO repeating the same hook across variants. Each variant should feel like a different writer wrote it for a different segment.";
        $lines[] = "4. NO stuffing keywords. Weave them in where they fit grammatically.";
        $lines[] = "5. Hooks earn the click in the first 5–8 words. Bodies sustain interest. Closes drive the action.";
        $lines[] = "6. Mirror the audience's vocabulary, not generic ad-speak.";
        $lines[] = "7. Numbers, specificity, and proof points beat adjectives. Use the user's stated benefits verbatim where possible.";
        $lines[] = "8. Emoji: allowed sparingly on social platforms (Instagram, TikTok, Stories) when they add meaning. Avoid on LinkedIn, Google Search, Email subject lines, and Landing Pages.";
        $lines[] = "9. Respect every character limit — count characters, not words. If close to the limit, prefer 5–10 chars under to leave breathing room.";
        $lines[] = "10. Honor every MUST USE item from the brief. If extra_instructions conflict with a default, extra_instructions win.";
        $lines[] = "";

        // ──────────────────────────────────────────────────────────────────
        // 8. Variant differentiation strategy
        // ──────────────────────────────────────────────────────────────────
        if ($variants > 1) {
            $lines[] = "# VARIANT STRATEGY";
            $lines[] = "Each of the {$variants} variants must use a different angle. Pick from (don't reuse within this run):";
            $lines[] = "- Pain-led: open on the audience's frustration, position the product as the relief.";
            $lines[] = "- Aspiration-led: open on the dream outcome, show the product as the fast path.";
            $lines[] = "- Curiosity / pattern-interrupt: open with a surprising stat, contrarian claim, or question.";
            $lines[] = "- Social proof / authority: lead with a result, customer count, or credibility marker.";
            $lines[] = "- Direct benefit: lead with the single biggest, most concrete benefit — no warm-up.";
            $lines[] = "- Story / before-after: a one-line micro-story showing transformation.";
            $lines[] = "- Objection-handling: name the most common reason people don't buy, dismantle it.";
            $lines[] = "Vary the OPENING WORDS of each variant's primary hook field — do not start two variants the same way.";
            $lines[] = "";
        }

        // ──────────────────────────────────────────────────────────────────
        // 9. Output contract
        // ──────────────────────────────────────────────────────────────────
        $schemaKeys = collect($fields)->keys()->map(fn ($k) => "\"{$k}\": \"string\"")->implode(', ');

        $lines[] = "# OUTPUT FORMAT — JSON ONLY";
        $lines[] = "Return ONLY valid JSON. No markdown, no backticks, no commentary.";
        $lines[] = "Schema:";
        $lines[] = '{"variants": [{' . $schemaKeys . '}, ...]}';
        $lines[] = "Produce exactly {$variants} object(s) inside `variants`. Every key listed in the schema must be present in every object. Empty strings are NOT acceptable.";
        $lines[] = "Use `\\n` for line breaks inside strings where the structure rule for the field calls for paragraphs.";

        return implode("\n", $lines);
    }

    /**
     * Compute a target length range and structural rule for a field given its
     * hard character limit. The goal is to push the model to actually USE the
     * space provided by the platform instead of returning a single sentence.
     *
     * @return array{0: int, 1: int, 2: string}  [minChars, maxChars, structureInstruction]
     */
    private function fieldShape(int $limit): array
    {
        if ($limit <= 30) {
            // Headlines, micro CTAs.
            $min = (int) round($limit * 0.6);
            $max = (int) round($limit * 0.95);
            $shape = 'one tight phrase. Every word earns its spot. Use a strong verb or specific number.';
        } elseif ($limit <= 80) {
            $min = (int) round($limit * 0.7);
            $max = (int) round($limit * 0.92);
            $shape = 'one to two punchy sentences. Lead with the benefit, support with one specific detail.';
        } elseif ($limit <= 200) {
            $min = (int) round($limit * 0.7);
            $max = (int) round($limit * 0.9);
            $shape = '2–3 sentences forming a hook → value → CTA arc. No throwaway lines.';
        } elseif ($limit <= 500) {
            $min = (int) round($limit * 0.65);
            $max = (int) round($limit * 0.9);
            $shape = '2–4 short paragraphs separated by `\\n\\n`. Open with a hook line, develop the argument, close with a clear next step.';
        } elseif ($limit <= 1500) {
            $min = (int) round($limit * 0.55);
            $max = (int) round($limit * 0.85);
            $shape = '3–5 paragraphs separated by `\\n\\n`, plus optional bullet list with `• ` prefix where it fits. Cover: hook, problem, solution, proof, CTA.';
        } else {
            // 5000-char YouTube descriptions, etc.
            $min = (int) round($limit * 0.4);
            $max = (int) round($limit * 0.75);
            $shape = '5–8 paragraphs separated by `\\n\\n`. Front-load the most important sentence (first 150 chars). Include a clear value-prop, a 3–5 line bullet list of benefits with `• ` prefix, social proof if appropriate, and a CTA. SEO-friendly without keyword stuffing.';
        }

        return [$min, $max, $shape];
    }

    /**
     * Normalize free-text user input before injecting into the prompt — strip
     * stray newlines and trim, so prompt layout stays clean.
     */
    private function cleanValue(string $value): string
    {
        $value = preg_replace("/[ \t]+/", ' ', $value) ?? $value;
        $value = preg_replace("/\r\n|\r/", "\n", $value) ?? $value;
        return trim($value);
    }
}
