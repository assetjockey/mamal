# MASTER SYSTEM & ARCHITECTURAL PROMPT: THE HIGGSFIELD.AI OF COMMERCE & HUMAN BEHAVIOR INTELLIGENCE

> **Execution Directive**: You are an elite Principal Systems Architect, AI/ML Research Engineer, and Product Design Director. You are tasked with engineering **MAMAL PLATFORM (`/platform`)** — the unified super-app that fuses generative multimodal marketing with predictive human behavior analytics for commerce.
>
> This document is the **definitive, exhaustive specification prompt**. It refines, breaks down, and standardizes every architectural layer, predictive ML pipeline, closed-loop automation engine, tool consolidation specification, dual-tier UI/UX system, and monetization mechanic.

---

```
========================================================================================
SYSTEM PROMPT: AUTONOMOUS BUILD & OPERATIONAL DIRECTIVE
ROLE: Autonomous Lead Architect & Full-Stack Systems Engineer
APPLICATION: Mamal Platform (/platform) — The Higgsfield.ai of Marketing & Commerce
BASE WORKSPACE: /Users/cuanchai/GitHub/mamal/platform
========================================================================================
```

---

## 1. SYSTEM IDENTITY & CORE MISSION

You are building the premier super-app for commerce intelligence and generative execution — **the Higgsfield.ai of marketing and commerce**. 

The platform synthesizes two traditionally siloed domains into a singular, real-time closed loop:
1. **Predictive Human Behavior Intelligence**: Forecasting consumer actions (purchase propensity, churn risk, price elasticity, brand receptivity, scroll-stop probability, virality resonance).
2. **Generative Multimodal Execution**: Instantly translating behavioral predictions into high-converting assets (video ads via Seedance 2.5, product photography via Nanobanana, audio/voiceovers via ElevenLabs, automated long-form articles, ad campaigns, dynamic social proof widgets, and smart short links).

Every feature, tool, and automation operates as an interconnected node across a unified event bus (`@mamal/bus`) and shared relational/telemetry stores (Postgres 17 + ClickHouse).

---

## 2. PREDICTIVE ANALYTICS ENGINE FOR HUMAN BEHAVIOR

The predictive engine shifts businesses from *reactive reporting* to *prescriptive pre-emption*. It continuously ingests user and visitor interactions across all touchpoints, processes signals through an ensemble ML pipeline, and outputs behavioral scores that directly drive downstream automations.

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                         PREDICTIVE BEHAVIOR DATA & ML PIPELINE                         │
└────────────────────────────────────────────────────────────────────────────────────────┘
  DATA INGESTION               FEATURE PIPELINE            MODEL ENSEMBLE        PRESCRIPTIVE ACTUATION
┌─────────────────┐          ┌───────────────────┐       ┌─────────────────┐    ┌───────────────────────┐
│ /track Telemetry│─────────▶│ Behavioral State  │──────▶│ OpenAI Astra    │───▶│ Real-Time Incentives  │
│ Clickstream     │          │ Graph: RFM+, Dwell│       │ (Behavioral)    │    │ (Discounts, Cart Save)│
│ Session Replays │          │ Decay, Attention  │       └─────────────────┘    └───────────────────────┘
├─────────────────┤          ├───────────────────┤       ┌─────────────────┐    ┌───────────────────────┐
│ /confirm Proof  │─────────▶│ Intent Vectorizer │──────▶│ DeepSeek Pro/R1 │───▶│ Dynamic Content &     │
│ Form Submissions│          │ Interaction Paths,│       │ (Reasoning/pLTV)│    │ Ad Mutation (Seedance)│
│ Reviews & Scans │          │ Micro-hesitations │       └─────────────────┘    └───────────────────────┘
├─────────────────┤          ├───────────────────┤       ┌─────────────────┐    ┌───────────────────────┐
│ /link & /market │─────────▶│ Brand Sentiment & │──────▶│ XGBoost / PyTorch│───▶│ Proactive Retention & │
│ Short URL clicks│          │ Receptivity Graph │       │ Edge Classifiers│    │ VIP Concierge Escal.  │
│ Ad impressions  │          │ Cross-Tool Correl.│       │ (Latency <15ms) │    │                       │
└─────────────────┘          └───────────────────┘       └─────────────────┘    └───────────────────────┘
```

### 2.1 Specific Human Behaviors Modeled & Predicted

The predictive engine computes eight discrete behavioral vectors for every visitor, customer, and target audience:

1. **High-Propensity Checkout Intent (`p(buy)`: $0.00 - 1.00$)**
   - *Signals*: Mouse cursor velocity decrescendo over checkout elements, repeated tab switching between pricing tiers, scroll depth velocity on shipping/guarantee blocks, cart-mutation cadence, and historical session frequency.
   - *Output*: Real-time confidence score triggering friction-removal interventions before the user exits.

2. **Price Sensitivity & Willingness-To-Pay (WTP) Elasticity**
   - *Signals*: Dwell-time comparison across tier cards, promo code input trials, interaction with volume discount selectors, referral origin (organic search vs. discount aggregator vs. luxury ad).
   - *Output*: Dynamic price resistance quotient ($R_{\text{price}}$) determining whether to present an urgency incentive, an upsell bundle, or zero discount to preserve gross margins.

3. **Dynamic Churn & Disengagement Trajectory (`p(churn)`)**
   - *Signals*: Inter-session latency lengthening ($> 2.5\times$ historical baseline), drop in feature breadth utilization, declining open rates on automated push campaigns, unresolved negative sentiment in feedback forms.
   - *Output*: Churn hazard rate curve projected across 7, 30, and 90 days.

4. **Predictive Lifetime Value (`pLTV`)**
   - *Signals*: Initial acquisition channel, speed of first-action completion, cross-category browse diversity, initial basket margin, brand affinity velocity.
   - *Output*: Forecasted 365-day net revenue yield, segmenting visitors into VIP, Standard, or High-Maintenance cohorts.

5. **Brand & Persona Receptivity Quotient (Personal Brand vs. Corporate Brand)**
   - *Signals*: Reaction latency to founder-led video content vs. corporate catalog imagery, comment sentiment, bio-link avatar click patterns, influencer UGC interaction depth.
   - *Output*: Persona affinity vector directing whether downstream marketing delivers humanized storytelling or enterprise feature breakdowns.

6. **Creative Scroll-Stop & Hook Retention Rate (`p(hook)`)**
   - *Signals*: First 3-second video playback completion rates, viewport scroll acceleration during feed view, micro-scrubbing actions, audio toggle states.
   - *Output*: Hook-efficacy index ($H_{\text{eff}}$) forecasting whether an ad or social post will capture attention across TikTok, Instagram Reels, and YouTube Shorts.

7. **Viral Resonance & UGC Adoption Probability**
   - *Signals*: Social re-share velocity, bio-link bookmark frequency, QR coupon pass-throughs, quote requests from shared links.
   - *Output*: Virality coefficient ($\kappa$-factor prediction) recommending whether to inject paid ad spend behind organic posts.

8. **Ad Creative Fatigue & Decay Velocity**
   - *Signals*: Frequency-to-CTR decay slope, negative feedback rates on Meta/TikTok ad placements, CTR variance across 48-hour rolling windows.
   - *Output*: Remaining creative half-life (in hours/days) before ad performance drops below target ROAS.

---

### 2.2 End-to-End Predictive Machine Learning Pipeline

1. **Data Collection & Ingestion**:
   - Telemetry from `/track` (cookieless clicks, cursor movements, session replays, custom event tags).
   - Interaction events from `/confirm` (social proof impressions, coupon claims, loyalty QR stamps, booking inquiries).
   - Campaign telemetry from `/market` (ad spend, CTR, ROAS, video watch time, social engagement).
   - Routing telemetry from `/link` (short link hops, geo/device resolution, biolink block taps, file download completions).
   - Infrastructure correlation from `/monitor` (page latency impact on bounce probabilities).
   - Health and visibility scores from `/audit` (technical crawl performance vs. organic user drop-off).

2. **Data Preparation & Normalization**:
   - Edge sanitization stripping PII (passwords, payment data, personal tokens).
   - Session stitching uniting cross-device anonymous traffic to authenticated profiles upon login or email capture.
   - Temporal bucketing into 1-minute, 1-hour, and 24-hour aggregation slices.
   - Z-score normalization for continuous metrics (session duration, time-on-page, click count).

3. **Feature Engineering**:
   - **RFM+ Behavioral Embeddings**: Recency, Frequency, Monetary value augmented with *Engagement Depth* (scroll velocity, focus-loss count, DOM interaction diversity).
   - **Attention Density Heatmaps**: Gaussian-smoothed $(x, y)$ coordinate clusters mapped to semantic page elements (CTA, reviews, pricing, guarantee).
   - **Graph Interaction Centrality**: User navigation sequences transformed into Directed Acyclic Graphs (DAGs) to identify high-conversion transition paths.

4. **Model Training, Ensembling & Inference**:
   - **Default Predictive Backbone**: **OpenAI Astra** for high-dimensional behavioral forecasting and consumer intent simulation.
   - **Deep Reasoning Engine**: **DeepSeek Pro / R1** for complex causal inference, churn root-cause attribution, and multi-touch revenue impact analysis.
   - **Edge Propensity Classifiers**: Lightweight gradient-boosted trees (XGBoost / LightGBM) compiled to WebAssembly/ONNX running on Cloudflare Workers edge nodes for sub-15ms inference during live page sessions.

5. **Continuous Evaluation & Calibration**:
   - Calibration monitored via Brier score and AUC-ROC curves.
   - Automated model drift detection: If model accuracy drops below $92\%$ on trailing 7-day conversion predictions, the system triggers an asynchronous fine-tuning job via BullMQ.

---

## 3. CLOSED-LOOP PREDICTIVE AUTOMATION ENGINE

Predictive analytics achieves maximum ROI when it triggers automated actions without requiring human intervention. The automation engine bridges behavioral scores with operational tools to drive revenue, optimize content, enhance services, and scale advertising.

### 3.1 Revenue Optimization Automations
- **Dynamic Exit-Intent Interception**: When a user's `p(buy)` exceeds $0.60$ but their cursor exhibits rapid upward movement toward the browser tab bar, trigger a targeted `/confirm` slide-in overlay displaying an exclusive, time-decaying 10% coupon code with social proof ("34 customers purchased this in the last hour").
- **Predictive Cart Recovery via Web Push**: If a checkout is abandoned with an estimated `pLTV > $500`, automatically schedule a multi-tier Web Push campaign via `/confirm` (66pusher engine) delivered at the user's mathematically predicted peak engagement hour.
- **Smart Loyalty Rewards Unlock**: When a customer's in-store QR stamp count (`/confirm` Loyalty Card) reaches within 1 stamp of a reward threshold and their visit cadence is slipping, push an automated SMS/Web Push granting bonus double-stamps if redeemed within 48 hours.

### 3.2 Customer Service & Retention Automations
- **Proactive Churn Neutralization**: If a subscriber's session frequency declines by $>50\%$ over 14 days, the automation engine routes a private, frictionless feedback form (`/confirm` Feedback Form) directly to the user's dashboard or inbox.
- **Sentiment-Driven Support Escalation**: If an incoming review or feedback rating is $\le 2$ stars, the system immediately intercepts the response from publishing to public channels (Google/Facebook reviews), routes it into a private recovery ticket, alerts the workspace team via Slack/Discord, and issues an automated conciliatory voucher.

### 3.3 Article & Editorial Automations (AI SEO & LLM Citations)
- **Trend-to-Article Auto-Pilot**: Ingest trending commercial keywords from `/market` (trends-checker + DataForSEO). When a search query shows exponential momentum ($>40\%$ week-over-week search volume increase), automatically invoke the AI Article Wizard.
- **Autonomous Multi-CMS Publishing**: Generate a 2,500-word, fully cited, humanized article with contextual Nanobanana diagrams. Format with proper H1-H4 semantic hierarchy, structured schema markup (JSON-LD), and auto-publish directly to Shopify, WooCommerce, WordPress, or Ghost.
- **LLM Citation Engineering**: Continuously audit brand citations across ChatGPT, Perplexity, Claude, and Gemini via `/audit`. If competitors gain share of voice for key category prompts, trigger auto-updates to existing articles to optimize semantic relevance and entity grounding.

### 3.4 Social Media Content Automations
- **Algorithmic Optimal Dispatch**: Machine learning models analyze target audience interaction patterns across Facebook, Instagram, TikTok, Threads, YouTube, and Twitter/X, auto-scheduling generated posts at the precise minute of maximum audience receptivity.
- **Automated Repurposing Engine**: When an article or product page is published, the automation engine automatically breaks down the content into a Twitter/X thread, a LinkedIn thought-leadership carousel, an Instagram visual post (Nanobanana), and a 15-second TikTok/YouTube Short video script (Seedance 2.5).

### 3.5 Advertising & Media Buying Automations
- **Autonomous Creative Refresh**: When `/market` ad analytics detect an ad's CTR falling below its 7-day moving average by $\ge 25\%$, trigger the MagicAds generator to draft 5 fresh ad copy variants and 3 new video hooks.
- **Predictive Multi-Armed Bandit Budget Shifting**: Automatically shift ad budgets toward high-converting creatives based on early 3-hour micro-signals, maximizing return on ad spend (ROAS) across Google Ads, Meta Ads, and TikTok Ads.

### 3.6 Automation UX: 1-to-3 Click Templates & Visual Canvas
1. **Pre-Built Recipe Library (1-to-3 Click Setup)**:
   - "Turn High-Intent Abandoned Carts into Verified Reviews & Re-orders"
   - "Auto-Draft Weekly Trending Blog & Syndicate to All Social Networks"
   - "Intercept Negative Customer Feedback Before It Reaches Google Maps"
   - "Auto-Generate Seedance Video Ads When Competitor Prices Rise"
2. **Drag-and-Drop Visual Automation Canvas**:
   - Interactive DAG canvas built with React Flow, supporting Trigger nodes, Predictive Filter nodes, Conditional branches, and cross-tool Action executions with live state highlights.

---

## 4. DEEP BREAKDOWN OF THE SIX UNIFIED TOOLS

### 4.1 Tool 1: `/audit` (Search & AI Visibility Auditor)
**Consolidates**: `66audit`, `phprank`, `crawlseo-main`, `open-seo-main`, `linkinator-main`.
- 72-rule deep technical SEO crawler with bounded-slice distributed workers.
- AI Search visibility & LLM citation tracking (ChatGPT, Perplexity, Claude, Gemini).
- 18 instant client-side tools (robots.txt tester, meta tag checker, status codes, hash tools).
- White-labeled PDF and CSV export reporting.

### 4.2 Tool 2: `/confirm` (Social Proof, Trust & Local Engagement)
**Consolidates**: `66socialproof`, `66pusher`, `localboostai`.
- 41 notification widget types with granular geo/device/path targeting.
- Web push marketing engine (subscribers, segments, recurring campaigns, RSS automations).
- LocalBoostAI suite: Review booster with private recovery, dynamic coupons, digital QR loyalty stamp cards, private feedback forms, lead capture forms, and service booking pages.

### 4.3 Tool 3: `/link` (Smart URLs, Dynamic QR, Bio-Links & File Transfers)
**Consolidates**: `66biolinks`, `66qrcode`, `66transfer`, `linkqr`, `phpshort`, `droppy`, `swipgle`.
- 82+ bio-link blocks (music, video, products, Stripe/PayPal payment buttons, newsletter signups).
- 34+ dynamic QR configurations (custom body/eye styles, logos, gradients, vector export).
- Enterprise short links with custom domains, deep linking, expiration, and A/B rotation.
- Branded large file transfers with AES-256 encryption, password protection, and pluggable S3/R2 storage.

### 4.4 Tool 4: `/market` (Multimodal Ad Studio, Social Media & SEO Suite)
**Consolidates**: `magicads`, `magicai`, `open-seo-main`, `trends-checker-master`, `crawlseo-main`, `linkinator-main`, `stackposts`.
- MagicAds studio supporting 30+ ad platforms with typed fields.
- Seedance 2.5 for AI video ads and product avatar demonstrations.
- Nanobanana for studio product photoshoots and generative image editing.
- ElevenLabs v3 for voiceover and multi-language dubbing.
- Auto-blogging with Shopify, WooCommerce, WordPress, and Ghost publishing.
- Stackposts social studio for scheduling across 7 networks.
- Semrush-grade SEO keyword explorer and competitor spy tools.

### 4.5 Tool 5: `/monitor` (Uptime, API & Infrastructure Reliability)
**Consolidates**: `66uptime`, `phpuptime`.
- Multi-location synthetic availability checks (HTTP, TCP, UDP, ICMP, DNS, SMTP).
- Real-browser synthetics testing checkout flows with failure screenshot captures.
- API contract testing with JSON assertion chains.
- SSL/TLS and WHOIS expiration alerts at 30, 14, and 7 days.
- Public/private status pages with custom domains and multi-channel notifications (Slack, SMS, Discord).

### 4.6 Tool 6: `/track` (Privacy-First Analytics, Replays & Competitor Intelligence)
**Consolidates**: `66analytics`, `phpanalytics`, `similarweb`, `stalkr`, `xtrabar`.
- Cookieless privacy-compliant tracking migrating to identified profiles upon authentication.
- High-fidelity DOM session replays with client-side sensitive data masking.
- Dynamic heatmaps (Click, Movement, Scroll) across desktop, tablet, and mobile.
- Multi-stage conversion funnels with drop-off analytics.
- SimilarWeb-grade competitor traffic and referral benchmarking.

---

## 5. DESIGN SYSTEM, UI/UX & CUSTOMER EXPERIENCE (CX)

- **Aesthetic**: Stripe-inspired indigo-ink ledger on frosted glass.
- **Palette**: Indigo Ink (`#533afd`), Midnight Ink (`#061b31`), Mist (`#f8fafd`), Frost (`#e5edf5`), Periwinkle Wash (`#e8e9ff`). High-contrast dark mode.
- **Surface Elevation**: Zero drop shadows. Elevation conveyed via background shifts and 1px hairline borders. 4px border-radius (`rounded-[4px]`).
- **Typography**: `sohne-var` / `Inter Tight` (weights 300 and 400). Tabular figures (`"tnum" on`) enforced across all metrics and tables.
- **Dual-Tier Sidebar Shell**: Tier 1 (64px icon rail) for workspaces and master tools; Tier 2 (220px contextual drawer) for sub-navigation, resource lists, and quota meters.
- **Accessibility & Motion**: 100% WCAG 2.1 AA compliance (0 contrast violations), ⌘K command palette, toasts with 5-second undo countdowns, and full `prefers-reduced-motion` compliance.

---

## 6. MULTI-MODEL AI ROUTING & REVENUE MARGIN ENGINE

| Specialized Capability | Default Model | Pluggable Alternatives |
|---|---|---|
| **Behavioral Predictive Analytics** | **OpenAI Astra** | Qwen Max, Claude 3.7 Sonnet |
| **Closed-Loop Automation Engine** | **Anthropic Claude 3.7 Sonnet** | GPT-5, Moonshot Kimi k1.5 |
| **Deep Reasoning & Attribution** | **DeepSeek Pro / R1** | OpenAI o3, Gemini 2.5 Pro |
| **Multimodal Video Generation** | **Seedance 2.5** | Kling 3.0 Pro, Google Veo 3 |
| **High-End Product Photography** | **Nanobanana** | Flux 2 Flex, Imagen 4 |
| **Voiceover, Speech & Dubbing** | **ElevenLabs v3** | Azure Cognitive Speech, Speechify |
| **SEO, SERP & Backlink Data** | **DataForSEO API** | Open-SEO Engine |

- **Automatic 25% Gross Revenue Margin**:
  $$\text{User Credits Deducted} = \lceil (\text{Upstream Provider Cost USD} \times 1.25) \times 100 \rceil$$
- **Master AI Kill-Switch**: One administrative toggle halts all external AI API consumption instantly across the workspace while preserving 100% deterministic tool functionality.

---

## 7. MONETIZATION, ENTITLEMENTS & PRICING ARCHITECTURE

1. **Free Tier ($0/month)**: 18 client-side SEO tools, 1 basic uptime check, 5 short links, 1 social proof widget, and cookieless tracking up to 5,000 pageviews/month. Costs $0 in external compute.
2. **Per-Tool Subscriptions ($19 - $49/month)**: Full access to a specific tool with generous quotas and baseline monthly credits.
3. **Unified Platform Subscription ($99 - $249/month)**: Unlocks all 6 tools, interop automations, team collaboration, and high-volume monthly credit pools.
4. **Universal Credit Wallet (FIFO Buckets)**: Universal currency spendable across all tools. Plan credits expire monthly; purchased credit top-ups rollover for 365 days.
5. **Lifetime Tier (LTD - Strictly Non-Marginal)**: One-time payment ($299 - $599) granting permanent access to all 6 tools. Strictly excludes free AI usage (users bring their own API keys or purchase credit top-ups).

---

## 8. EXTENSION & PLUGIN ECOSYSTEM

Modular plugins configured per workspace: **Affiliate System**, **Email Signature Generator**, **Image Optimizer**, **Newsletters Engine**, **Push Notifications**, **PWA Generator**, and **Teams & RBAC**.

---

## 9. RECOMMENDED PRODUCTION TECH STACK

- **Monorepo**: Turborepo + pnpm workspaces
- **Framework**: Next.js 16 (React 19, RSC, Server Actions)
- **Validation**: TypeScript 5.8 Strict + Zod 4
- **Databases**: PostgreSQL 17 (Drizzle ORM) + ClickHouse (analytics facts)
- **Queue & Cache**: Redis 7.4 (Valkey) + BullMQ
- **Edge Compute**: Cloudflare Workers (Hono framework)
- **Storage**: Cloudflare R2 (zero egress fees)
- **Auth**: Better Auth (multi-tenancy, OAuth, 2FA, passkeys)
- **Billing**: Stripe Billing + Paddle adapter

---

## 10. SYSTEMATIC DEVELOPMENT QUALITY LOOPS

- **Loop 1 (G1 Design & A11y Gate)**: Responsive down to 320px. Zero horizontal overflow. Zero WCAG 2.1 AA violations.
- **Loop 2 (G2 Ubiquitous UX Gate)**: All resources reachable via ⌘K palette in $\le 3$ keystrokes. Immediate undo toasts.
- **Loop 3 (G3 Cross-Tool Interop Gate)**: Bidirectional event handover verified across all 6 tools via `@mamal/bus`.
- **Loop 4 (G4 Operational Reliability Gate)**: Sub-15ms edge routing. SSRF security guards. Graceful AI rate-limit handling with zero credit leakage.

---

## 11. STEP-BY-STEP IMPLEMENTATION SEQUENCE

1. **Step 1: Core Foundation** (`@mamal/db`, `@mamal/bus`, `@mamal/ai`, `@mamal/ui`).
2. **Step 2: `/audit` Tool** (72-rule crawler + AI search visibility).
3. **Step 3: `/confirm` Tool** (41 proof widgets + web push + LocalBoostAI).
4. **Step 4: `/link` Tool** (Bio-links + dynamic QR + branded file transfers).
5. **Step 5: `/market` Tool** (MagicAds + Seedance 2.5 + Nanobanana + Stackposts).
6. **Step 6: `/monitor` Tool** (Multi-region probes + browser synthetics + status pages).
7. **Step 7: `/track` Tool** (ClickHouse telemetry + session replays + heatmaps).
8. **Step 8: Predictive Engine & Visual Automation Canvas deployment**.
