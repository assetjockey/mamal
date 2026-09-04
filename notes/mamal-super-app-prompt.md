# Mamal — The Predictive Commerce Super App

> *The higgsfield.ai of marketing and commerce: a unified platform that predicts human behavior in relation to brands, products, and personal identity — then acts on those predictions to improve revenue, service, content, advertising, and customer experience.*

---

## Table of Contents

1. [Vision & Positioning](#1-vision--positioning)
2. [Platform Architecture & Interoperability](#2-platform-architecture--interoperability)
3. [Predictive Analytics Engine — The Behavioral Core](#3-predictive-analytics-engine--the-behavioral-core)
4. [Automation Engine — Streamlined Intelligence](#4-automation-engine--streamlined-intelligence)
5. [Tool 1: Audit — Search & AI Visibility Intelligence](#5-tool-1-audit--search--ai-visibility-intelligence)
6. [Tool 2: Confirm — Social Proof, Trust & Local Commerce](#6-tool-2-confirm--social-proof-trust--local-commerce)
7. [Tool 3: Link — URL Management, Bio Links, QR & File Transfer](#7-tool-3-link--url-management-bio-links-qr--file-transfer)
8. [Tool 4: Market — AI Content, Ads, SEO & Social Suite](#8-tool-4-market--ai-content-ads-seo--social-suite)
9. [Tool 5: Monitor — Uptime, Performance & Incident Management](#9-tool-5-monitor--uptime-performance--incident-management)
10. [Tool 6: Track — Web Analytics, Competitive Intelligence & Audience](#10-tool-6-track--web-analytics-competitive-intelligence--audience)
11. [Design System — UI/UX, Look, Feel, Motion & Components](#11-design-system--uiux-look-feel-motion--components)
12. [Customer Experience (CX) — End-to-End Journeys](#12-customer-experience-cx--end-to-end-journeys)
13. [Pricing, Credits & Monetization](#13-pricing-credits--monetization)
14. [AI Provider Registry & Cost Management](#14-ai-provider-registry--cost-management)
15. [Plugin Ecosystem & Additional Features](#15-plugin-ecosystem--additional-features)
16. [Mobile Responsiveness & PWA](#16-mobile-responsiveness--pwa)
17. [Recommended Tech Stack](#17-recommended-tech-stack)
18. [Quality Gates & Iteration Protocol](#18-quality-gates--iteration-protocol)

---

## 1. Vision & Positioning

### What Mamal Is

Mamal is a **super app for commerce intelligence**. It unifies six independent-but-interoperable tools — Audit, Confirm, Link, Market, Monitor, Track — under one workspace, one billing relationship, and one behavioral prediction engine. Each tool solves a discrete problem (SEO auditing, social proof, link management, content/ad creation, uptime monitoring, web analytics), but the platform's true value is the **predictive layer** that sits above all six: a machine-learning system that ingests signals from every tool to forecast how humans will interact with a brand, product, or personal identity — and then triggers automated actions to capitalize on those predictions.

### The Higgsfield Analogy

Just as higgsfield.ai brought generative AI to video creation with an emphasis on *lifelike human motion and behavior*, Mamal brings predictive AI to marketing and commerce with an emphasis on *lifelike human decision-making and behavior*. Where higgsfield predicts how a body moves, Mamal predicts how a customer moves — through a funnel, across channels, toward or away from a purchase decision.

### Core Thesis

> Every interaction a human has with a brand leaves a signal. Every signal can be measured. Every measurement can inform a prediction. Every prediction can trigger an action. The platform that closes this loop fastest — from signal to prediction to action — wins.

### What Makes Mamal Different

1. **Prediction-first, not reporting-first.** Dashboards show what happened; Mamal shows what will happen and does something about it.
2. **Six tools, one brain.** A link click in Link informs a churn prediction in Track, which triggers a retention widget in Confirm, which is measured by Monitor — and the user configures none of this plumbing.
3. **AI is additive, never load-bearing.** Every feature works without AI. AI makes it better: smarter predictions, better copy, automated fixes. Turn AI off and the tool still does its job with human-written guidance, manual configuration, and rule-based defaults. Lifetime plans prove this structurally — they exclude AI at the database level.
4. **Free tier costs nothing to run.** No AI calls, no expensive crawls, no background jobs. The free tier is a static-compute-only surface that demonstrates capability and earns trust.

---

## 2. Platform Architecture & Interoperability

### Structural Principles

Each tool is a **self-contained package** (`tools/audit`, `tools/confirm`, `tools/link`, `tools/market`, `tools/monitor`, `tools/track`) that:

- **Owns its own database tables**, migrations, and RLS policies
- **Registers a `ToolManifest`** declaring its features, entitlements, event types, commands, resources, and UI routes
- **Publishes events** to the transactional outbox bus (e.g., `audit.crawl.completed`, `track.visitor.converted`)
- **Subscribes to events** from other tools without importing them (e.g., Audit subscribes to `monitor.target.recovered` to auto-trigger a re-crawl when a site comes back up)
- **Dispatches commands** cross-tool via `commands.dispatch('confirm.widget.trigger')` — the only legal cross-tool invocation path

### Interoperability Model

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          PREDICTIVE ANALYTICS ENGINE                     │
│   (ingests events from all 6 tools → trains models → emits predictions) │
└─────────────────────────┬───────────────────────────────────────────────┘
                          │ prediction.* events
┌─────────────────────────▼───────────────────────────────────────────────┐
│                          AUTOMATION ENGINE                               │
│   (trigger → condition → action; cross-tool recipes; user-built flows)  │
└──┬──────┬──────┬──────┬──────┬──────┬───────────────────────────────────┘
   │      │      │      │      │      │
┌──▼──┐┌──▼──┐┌──▼──┐┌──▼──┐┌──▼──┐┌──▼──┐
│AUDIT││CNFRM││LINK ││MRKT ││MNTR ││TRACK│
└─────┘└─────┘└─────┘└─────┘└─────┘└─────┘
   ▲      ▲      ▲      ▲      ▲      ▲
   └──────┴──────┴──────┴──────┴──────┘
        Transactional Outbox Bus
        (events, commands, dead letters)
```

### How Tools Hand Off Work

**Scenario:** A user shortens a product URL in Link → a visitor clicks it → Track records the visit and attributes it to the link → the Predictive Engine scores the visitor's purchase intent → if intent > 0.7, Confirm shows a social proof notification → if they convert, Market drafts a follow-up email → Audit checks that the landing page's SEO is intact.

None of these tools import each other. Each reacts to events on the bus. The user configured none of the plumbing — it is a **seeded recipe** in the automation engine that came pre-installed.

### Async & Ubiquitous Operation

- Tools operate **asynchronously**: a crawl in Audit does not block a campaign in Market.
- Tools operate **in unison**: when an automation recipe connects them, the handoff happens within one event-loop cycle of the bus relay.
- Tools operate **ubiquitously**: the same API, MCP, webhook, and UI surfaces serve every tool identically. A new tool brings its own entries into the command palette, its own resources into the search, its own entitlements into the resolver — by registering a manifest, not by editing shared code.

---

## 3. Predictive Analytics Engine — The Behavioral Core

### 3.1 What It Predicts

The engine predicts **human behavior in relation to commerce** across five domains:

| Prediction Domain | What It Forecasts | Input Signals | Output |
|---|---|---|---|
| **Purchase Intent** | Probability a visitor will buy within N days | Page views, link clicks, dwell time, return frequency, cart actions, social proof interactions | 0–100 score per visitor segment |
| **Churn Risk** | Probability a customer stops engaging | Login frequency decay, support ticket sentiment, billing failures, feature usage drop-off | 0–100 score per customer |
| **Content Resonance** | Which topics/formats will perform best next week | Historical post engagement, trending topics, competitor content performance, seasonal patterns | Ranked topic suggestions with predicted engagement |
| **Ad Performance** | Predicted CTR/ROAS for a creative before launch | Historical ad performance by creative type, audience segment, platform, time of day, copy sentiment | Predicted CTR, CPA, ROAS with confidence interval |
| **Brand Affinity** | How a person's relationship with a brand is evolving | Social mentions, review sentiment, NPS scores, repeat purchase rate, referral activity | Affinity trajectory (growing/stable/declining) |

### 3.2 How It Works — Step by Step

#### Step 1: Data Collection

Every tool contributes behavioral signals to a unified **event fact table** (`@mamal/events`):

| Tool | Events Contributed |
|---|---|
| **Audit** | `audit.crawl.completed`, `audit.score.changed`, `audit.issue.found` |
| **Confirm** | `confirm.widget.viewed`, `confirm.widget.clicked`, `confirm.conversion.recorded`, `confirm.coupon.redeemed`, `confirm.review.submitted` |
| **Link** | `link.click.recorded`, `link.qr.scanned`, `link.biopage.visited`, `link.file.downloaded` |
| **Market** | `market.post.published`, `market.post.engaged`, `market.ad.impression`, `market.ad.clicked`, `market.article.generated`, `market.email.opened` |
| **Monitor** | `monitor.target.down`, `monitor.target.recovered`, `monitor.ssl.expiring`, `monitor.performance.degraded` |
| **Track** | `track.session.started`, `track.pageview.recorded`, `track.goal.completed`, `track.referrer.identified`, `track.visitor.returning` |

Events are recorded with:
- **Workspace ID** (tenant isolation)
- **Visitor fingerprint** (hashed, privacy-compliant — no PII in the fact table)
- **Timestamp** (uuidv7 gives chronological ordering for free)
- **Attribution chain** (which link, which campaign, which ad creative brought this visitor)
- **Contextual dimensions** (device, geo, referrer, page, UTM parameters)

#### Step 2: Feature Engineering

Raw events are transformed into **behavioral features** — the variables the model learns from:

| Feature Category | Specific Features |
|---|---|
| **Engagement Velocity** | Sessions per day (7d rolling avg), pages per session, time between sessions (decay rate), click depth |
| **Conversion Signals** | Cart-add frequency, checkout abandonment rate, coupon redemption rate, social proof click-through rate |
| **Content Interaction** | Article read completion rate, video watch %, social post save rate, ad creative A/B preference |
| **Temporal Patterns** | Day-of-week activity distribution, hour-of-day peak, seasonal purchase correlation, recency of last action |
| **Channel Affinity** | Preferred referrer source, organic vs. paid visit ratio, social platform distribution, email open rate |
| **Sentiment Signals** | Review sentiment score, feedback form tone, support ticket urgency, social mention polarity |

Feature engineering runs as a **scheduled service** (`services/worker-ai`) that processes new events in micro-batches, computes rolling aggregates, and writes feature vectors to the prediction store.

#### Step 3: Model Training

The platform uses **OpenAI Astra** as the default predictive analytics provider (configurable to swap providers). Models are trained per workspace because each business has different baselines:

- **Classification models** for binary outcomes (will churn: yes/no, will purchase: yes/no)
- **Regression models** for continuous outcomes (predicted revenue this month, expected CTR)
- **Time-series models** for trend forecasting (traffic next week, best posting time)
- **Clustering models** for audience segmentation (high-value vs. at-risk vs. dormant)

Training triggers:
- **Initial training**: After 1,000 events (minimum viable signal)
- **Retraining**: Weekly, or when model accuracy drifts below threshold
- **Real-time scoring**: Feature vectors are scored against the latest model on every significant event

#### Step 4: Prediction Delivery

Predictions are delivered through three channels:

1. **Dashboard widgets** — Every tool's dashboard shows relevant predictions:
   - Audit: "Based on current trajectory, your SEO score will drop 8 points by next month if these 3 critical issues aren't fixed"
   - Track: "Visitor segment 'Repeat Browsers' has 73% purchase intent — 2.4x higher than last month"
   - Market: "Posts about [topic] published on Thursdays at 2pm predict 3.2x engagement vs. your current schedule"

2. **Automation triggers** — Predictions fire events that the automation engine consumes:
   - `prediction.churn.high` → trigger retention email via Market
   - `prediction.purchase.imminent` → trigger social proof widget via Confirm
   - `prediction.content.trending` → suggest article topic via Market
   - `prediction.ad.underperforming` → suggest creative swap via Market

3. **API & MCP** — External systems and AI agents can query predictions:
   ```
   GET /api/v1/predictions/purchase-intent?segment=returning-visitors
   MCP: predictions.query({ domain: 'churn', threshold: 70 })
   ```

#### Step 5: Feedback Loop

Every prediction is tracked against its actual outcome:

- Prediction: "Visitor X has 80% purchase intent" → Did they purchase? → Outcome recorded
- Prediction: "This ad creative will achieve 2.3% CTR" → What was the actual CTR? → Outcome recorded
- Prediction: "Customer Y has 65% churn risk" → Did they churn? → Outcome recorded

This feedback is automatically fed back into the next training cycle, creating a **self-improving loop**.

### 3.3 Predictive Analytics UI — Screens & Components

#### The Predictions Dashboard (`/predictions`)

A dedicated top-level section in the dual-tier sidebar, appearing after all 6 tools:

| Screen | What It Shows |
|---|---|
| `/predictions` | Overview: active prediction models, accuracy scores, top predictions needing action, prediction volume over time |
| `/predictions/purchase-intent` | Purchase intent scores by visitor segment, trend over time, top converting segments, attribution to link/ad/content |
| `/predictions/churn-risk` | At-risk customers ranked by churn probability, decay curves, recommended retention actions, historical accuracy |
| `/predictions/content` | Topic recommendations ranked by predicted engagement, best posting times, content gap analysis, competitor content performance |
| `/predictions/ads` | Pre-launch creative scoring, predicted CTR/ROAS by audience, A/B test recommendations, budget allocation suggestions |
| `/predictions/brand` | Brand affinity trajectory, sentiment trend, NPS prediction, share of voice vs. competitors |
| `/predictions/settings` | Model configuration, training schedule, data retention for features, provider selection, accuracy thresholds |

#### Prediction Cards (Embedded in Every Tool)

Each tool's dashboard includes a **"Predictions" panel** — a collapsible card that shows the top 3 most actionable predictions relevant to that tool:

**Design:**
- Card with `--surface-lavender-card` background (#e8e9ff) to differentiate from standard data cards
- Header: "AI Predictions" with a sparkle icon (✦) and a toggle to collapse
- Each prediction row: one-line summary, confidence badge (high/medium/low as pill), and a "Take Action" button that either:
  - Opens the relevant automation recipe
  - Navigates to the relevant tool screen
  - One-click executes the recommended action
- Footer: "Powered by [provider name] · Last updated 4m ago · [Model accuracy: 87%]"

#### Prediction Detail Modal

Clicking any prediction opens a detail modal:

- **What**: Plain-english summary ("73% of visitors in segment 'Organic Blog Readers' are predicted to make their first purchase within 14 days")
- **Why**: Feature importance chart — which signals drove this prediction (bar chart of top 5 features)
- **Confidence**: Confidence interval with historical accuracy for this prediction type
- **Recommended Action**: 1–3 specific actions, each with an "Execute" button that triggers the automation
- **History**: How this prediction has evolved over the last 30 days (line chart)
- **Feedback**: "Was this prediction useful?" — thumbs up/down for model improvement

### 3.4 How Predictive Analytics Improves Specific Outcomes

#### Improving Revenue

| Signal → Prediction → Action |
|---|
| Track detects returning visitor with high page depth → Purchase intent model scores 82% → Confirm shows "12 people bought this in the last hour" social proof → Conversion rate increases 18% |
| Market's ad analytics show declining ROAS → Ad performance model predicts creative fatigue → Market auto-generates 3 new ad variants and A/B tests them → ROAS recovers within 48 hours |
| Link shows QR code scans spiking in a new city → Brand affinity model identifies emerging market → Market auto-creates localized social posts → Revenue from new market grows 3x |

#### Writing Better Articles

| Signal → Prediction → Action |
|---|
| Track shows trending search queries driving traffic → Content resonance model ranks topics by predicted engagement → Market's AI blogger auto-generates article on top topic with corresponding images → Published to WordPress with predicted 2.7x engagement |
| Audit finds competitor ranking for keywords you don't → Content gap analysis predicts which gaps are worth filling → Market generates SEO-optimized draft with internal linking suggestions → Article ranks within 2 weeks |
| Track shows which existing articles have declining traffic → Content decay model predicts which will lose ranking next → Market suggests refresh edits and new sections → Traffic restored before decline completes |

#### Creating Better Social Media Posts

| Signal → Prediction → Action |
|---|
| Track identifies peak engagement windows per platform → Temporal model predicts optimal posting times → Market auto-schedules posts for predicted peak → Engagement increases 45% |
| Market analyzes which post formats (carousel, video, text) perform best per audience segment → Content resonance model ranks format + topic combinations → Market auto-generates in the predicted best format → Save 3 hours/day of content planning |
| Confirm shows which social proof messages get most clicks → Message effectiveness model predicts winning copy patterns → Market applies those patterns to social post copy → Click-through rate improves 28% |

#### Improving Advertising

| Signal → Prediction → Action |
|---|
| Market's ad creative library is scored pre-launch → Ad performance model predicts CTR for each variant → Only variants above predicted 2% CTR threshold are launched → Wasted ad spend reduced 35% |
| Track identifies which audience segments have highest purchase intent → Audience model creates lookalike segments → Market auto-creates targeted ad campaigns for those segments → CAC drops 22% |
| Monitor detects that a landing page is slow (performance degradation) → Performance-conversion correlation model predicts revenue impact → Alert triggers Audit to diagnose → Fix is applied before ad budget is wasted on a broken page |

#### Creating a Better Service

| Signal → Prediction → Action |
|---|
| Confirm's feedback forms detect emerging service issues → Sentiment model predicts which issues will become public reviews → Alert sent to support team with specific issue and affected customers → Issue resolved before 1-star review is posted |
| Track shows customer journey friction points → Churn model identifies which friction points predict churn → Audit provides specific fix guidance for those pages → Customer experience improves where it matters most |
| Confirm's loyalty card data shows redemption patterns → Loyalty model predicts which rewards drive repeat visits vs. one-time use → Recommendations for reward optimization → Repeat visit rate increases 31% |

---

## 4. Automation Engine — Streamlined Intelligence

### 4.1 Architecture

The automation engine (`@mamal/automations`) is a **Trigger → Condition → Action** pipeline:

```
TRIGGER (event from any tool or prediction)
  → CONDITION (evaluate against rules, thresholds, segments)
    → ACTION (execute command in any tool)
```

### 4.2 Pre-Built Automation Templates (1–3 Click Setup)

The platform ships with **seeded cross-tool recipes** — automation templates that users activate with 1–3 clicks:

| Template Name | Trigger | Condition | Action | Setup Clicks |
|---|---|---|---|---|
| **SEO Rescue** | `monitor.target.recovered` | Site was down > 5 min | `audit.crawl.queue` — re-crawl to check for SEO damage | 1 |
| **Smart Social Proof** | `prediction.purchase.imminent` | Purchase intent > 70% | `confirm.widget.show` — display social proof notification | 1 |
| **Churn Prevention** | `prediction.churn.high` | Churn risk > 65% | `market.email.send` — send retention offer with personalized copy | 2 (choose email template) |
| **Trending Content** | `prediction.content.trending` | Predicted engagement > 2x avg | `market.article.draft` — auto-generate article on trending topic | 2 (choose blog platform) |
| **Ad Creative Refresh** | `prediction.ad.underperforming` | Predicted CTR < 1.5% | `market.ad.generate` — create 3 new ad variants | 1 |
| **Link Intelligence** | `link.click.spike` | Click rate > 3x 7d avg | `track.segment.create` — create audience segment from clickers | 1 |
| **Uptime → Trust** | `monitor.target.down` | Any downtime | `confirm.widget.pause` — pause social proof to avoid "site is fast!" on a broken site | 1 |
| **Review Recovery** | `confirm.review.low` | Review score < 3 | `confirm.feedback.route` — route to private recovery form instead of public review | 1 |
| **Smart Scheduling** | `market.post.drafted` | New post ready | `prediction.timing.optimal` → schedule for predicted best time | 1 |
| **Performance Guard** | `monitor.performance.degraded` | Page load > 3s | `audit.crawl.queue` + `market.ad.pause` — diagnose and stop wasting ad spend | 2 (choose ad platforms) |

### 4.3 Canvas-Based Custom Automations

Users can build their own automations on a **visual canvas**:

**Canvas Design:**
- **Node-based flow editor** — drag trigger, condition, and action nodes onto a canvas
- **Connection wires** — draw lines between nodes to define the flow
- **Node palette** — left sidebar listing all available triggers (grouped by tool), conditions (comparison operators, time windows, segment membership), and actions (grouped by tool)
- **Live preview** — right sidebar shows a simulation of the flow with sample data
- **Version history** — every save creates a version; roll back with one click

**Node Types:**
- **Trigger nodes** (blue, rounded): Event from any tool or prediction engine
- **Condition nodes** (yellow, diamond): Evaluate a boolean expression
- **Branch nodes** (orange, diamond): If/else split
- **Action nodes** (green, rounded): Execute a command in any tool
- **Delay nodes** (gray, rectangle): Wait N minutes/hours/days
- **Loop nodes** (purple, rounded): Repeat until condition changes

**Canvas Interactions & Motions:**
- Drag a node from the palette → it appears with a subtle scale-up animation (0 → 1, 200ms ease-out)
- Connect two nodes → a bezier curve draws from source to target with a flowing particle animation along the wire
- Hover a node → it lifts slightly (2px translate-y) with a soft shadow appearing (200ms)
- Delete a node → it shrinks to 0 with a fade (150ms) and connected wires retract
- Run simulation → data packets (small dots) flow along the wires in real-time, pausing at condition nodes to show evaluation
- Error state → node border turns red with a gentle pulse animation (1s ease-in-out infinite)

### 4.4 Automation Analytics

Each automation has a performance dashboard:

- **Executions over time** (area chart)
- **Success/failure rate** (donut chart)
- **Average latency** (trigger → action completion)
- **Revenue attribution** (if the action led to a conversion, how much revenue is attributed)
- **A/B comparison** (if two automations target the same trigger, which performs better)

---

## 5. Tool 1: Audit — Search & AI Visibility Intelligence

### 5.1 Purpose

Find website issues impacting search and AI visibility — and fix them fast with step-by-step guidance. The platform's SEO intelligence layer that crawls websites, evaluates 72+ rules, and provides weighted scores with actionable fix guidance.

### 5.2 Core Features

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Site Crawling** | BFS crawler with SSRF guard, robots/sitemap discovery, bounded 25-page slices | Predicts which pages will lose ranking based on issue severity × page importance |
| **72-Rule Engine** | Crawlability, on-page, links, performance, security, AI visibility checks | Rules weighted by predicted SEO impact, not just severity |
| **AI Visibility Category** | Content extractability, attribution readiness, AI crawler access checks | Predicts which AI answer engines will cite or ignore each page |
| **Score & Grade** | Weighted score per site with trend over time | Predicts score trajectory: "At current fix rate, you'll reach 85 by Oct 15" |
| **Fix Guidance** | Every rule has `why` and `howToFix` prose — no AI required | AI adds contextual fix briefs; never replaces the static guidance |
| **Link Graph** | Persisted internal/external link graph with broken link detection | Predicts which broken links will impact ranking most based on traffic flow |
| **Free Tools** | 18 instant tools (robots tester, meta inspector, etc.) — no sign-up | Acquisition surface; unlimited for local-compute tools, rate-limited for fetch tools |
| **Compare** | Diff two audit runs: what was fixed, what was introduced | Predicts impact of fixes on score before the next crawl runs |
| **Export** | CSV and JSON export on every plan — your data is never locked | Branded PDF reporting on paid tiers |
| **Scheduling** | Manual, daily, weekly, monthly — entitlement-gated on the server | Predictive scheduling: crawl frequency adjusts based on site change rate |

### 5.3 Screens

| Route | Description | Key Components |
|---|---|---|
| `/audit` | Websites list with score, delta, live crawl indicator | SiteCard grid, score sparkline, crawl progress bar |
| `/audit/sites/[id]` | Score trend, per-category pass rate, what to fix first | TrendChart, CategoryBreakdown, PriorityList, CrawlStats |
| `/audit/sites/[id]/pages` | Every crawled URL with facts | DataTable with inline fact badges, sortable/filterable |
| `/audit/sites/[id]/settings` | Schedule, page/depth caps, robots, exclude patterns | Form with radio groups (schedule), number inputs (caps), textarea (patterns) |
| `/audit/issues` | Findings grouped by rule, with evidence and fix guidance | CollapsibleRuleGroup, EvidencePanel, FixGuidePanel |
| `/audit/runs` | Every crawl: status, trigger, score, duration | RunList with status badges, duration bars |
| `/audit/rules` | 72-check catalogue with weights and thresholds | RuleTable with category filters, weight sliders (for paid tiers) |
| `/audit/sites/[id]/compare` | What was fixed and introduced between two runs | DiffView with green/red indicators, improvement summary |
| `/audit/tools` | 18 instant free tools | ToolGrid with instant input → result cards |
| `/audit/reports` | Export interface | FormatSelector (CSV/JSON/PDF), date range picker |

### 5.4 AI Features (Toggleable)

- **Audit Summary**: Natural-language summary of the crawl results
- **Per-Page Fix Brief**: AI-generated contextual fix instructions based on the specific page's issues
- **Alt Text Suggestions**: AI-generated alt text for images missing it
- **Score Prediction**: ML model predicts future score based on current trajectory and planned fixes

---

## 6. Tool 2: Confirm — Social Proof, Trust & Local Commerce

### 6.1 Purpose

Increase sales, trust, and credibility through automated social proof, push notifications, review management, coupons, loyalty cards, feedback collection, lead capture, and booking pages.

### 6.2 Core Features

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Social Proof Widgets** | 44 widget types across 8 render families, 30 themes | Predicts which widget type + message drives most conversions per audience segment |
| **Push Notifications** | Web push with RFC 8291 encryption, segment targeting | Predicts optimal send time and message variant per subscriber |
| **Targeting Rules** | 23 fields, 16 operators, evaluated in browser | Suggests targeting rules based on predicted highest-converting segments |
| **Review Booster** | Route happy customers to Google/Facebook, low scores to private recovery | Predicts review sentiment before submission based on session behavior |
| **Coupons** | Create offers, collect claims, issue unique codes, track redemptions | Predicts which coupon value/type drives repeat purchases vs. one-time use |
| **Loyalty Cards** | QR stamp cards, visit tracking, reward unlocking, staff redemptions | Predicts which reward thresholds maximize customer lifetime value |
| **Feedback Forms** | Private feedback collection, issue routing, follow-up | Sentiment analysis predicts which feedback items need immediate escalation |
| **Lead Forms** | Quote requests, callbacks, waitlists, consultation leads | Predicts lead quality score and probability of conversion |
| **Booking Pages** | Appointment pages for local services, request management | Predicts no-show probability and suggests optimal booking confirmation flow |
| **Widget Runtime** | `confirm.js` — 5.22 KB gzipped, CLS 0, runs on third-party sites | Runtime is lean; all intelligence is in the server-side payload builder |

### 6.3 Screens

| Route | Description | Key Components |
|---|---|---|
| `/confirm` | Dashboard: active widgets, conversion rate trend, top-performing widgets | WidgetGrid, ConversionChart, PerformanceRanking |
| `/confirm/widgets` | All widgets with type, status, site, targeting summary | WidgetTable with preview thumbnails, status toggles |
| `/confirm/widgets/new` | Widget creation wizard (3 panes: content, appearance, targeting) | TypeSelector, SettingsForm (auto-generated from zod schema), LivePreview (real runtime in sandboxed iframe), TargetingPanel |
| `/confirm/widgets/[id]` | Widget editor with live preview | Same 3-pane layout as creation, with performance stats |
| `/confirm/conversions` | Conversion feed with privacy-projected data (first name, city, country only) | ConversionList with time-relative timestamps, attribution links |
| `/confirm/push` | Push campaigns: compose, segment, schedule, send | CampaignComposer, SegmentBuilder (reuses targeting rule engine), DeliveryStats |
| `/confirm/push/subscribers` | Subscriber list with segment membership | SubscriberTable with segment badges, retired indicators |
| `/confirm/reviews` | Review booster dashboard: routing rules, collected reviews, recovery | ReviewFlowBuilder, SentimentChart, RecoveryQueue |
| `/confirm/coupons` | Coupon management: create, track claims, redemption analytics | CouponBuilder, RedemptionTable, ROICalculator |
| `/confirm/loyalty` | Loyalty card management: QR cards, visit tracking, rewards | LoyaltyCardDesigner, VisitTimeline, RewardManager |
| `/confirm/feedback` | Feedback forms: form builder, response inbox, issue routing | FormBuilder, ResponseInbox, IssueRouter, SentimentChart |
| `/confirm/leads` | Lead forms: form builder, lead inbox, quality scoring | FormBuilder, LeadInbox, QualityScoreColumn, ConversionTracker |
| `/confirm/booking` | Booking pages: page builder, appointment inbox, follow-up | BookingPageBuilder, CalendarView, AppointmentInbox, NoShowTracker |

### 6.4 Widget Editor — Detailed UX

**Three-pane layout:**

1. **Left pane (Settings)**: Auto-generated form from the widget type's zod schema. Fields include:
   - Message template with `{{placeholder}}` tokens
   - Display duration, delay, animation type
   - Minimum threshold (e.g., "show only if 3+ recent sales")
   - Link URL and CTA text

2. **Center pane (Preview)**: Real `confirm.js` runtime in a sandboxed iframe (no `allow-same-origin`). Updates on every keystroke without round-trip. Shows:
   - Desktop preview (full width)
   - Mobile preview (375px, togglable)
   - Position simulation (where on the page it appears)
   - Animation preview (play/pause)

3. **Right pane (Targeting)**: Rule builder with editable sample visitor:
   - Field selector (country, device, referrer, URL path, time of day, etc.)
   - Operator selector (equals, contains, matches regex, greater than, etc.)
   - Value input
   - `explain()` output showing why the sample visitor does/doesn't match
   - "Who will see this" estimated reach (based on Track data)

**Interactions & Motions:**
- Switching widget type → center pane cross-fades (300ms) to new preview
- Adding a targeting rule → rule row slides in from top (200ms ease-out)
- Toggling desktop/mobile preview → pane width animates (300ms spring) with device frame overlay
- Save → success toast slides up from bottom-right with 10s undo window
- Error in validation → affected field shakes (200ms) with red border pulse

---

## 7. Tool 3: Link — URL Management, Bio Links, QR & File Transfer

### 7.1 Purpose

A comprehensive URL shortening, link management, bio link page builder, QR code generator, and file transfer platform.

### 7.2 Core Features

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **URL Shortening** | Custom short URLs with branded domains, expiration, password protection | Predicts click-through rate for different URL formats and UTM strategies |
| **Bio Link Pages** | Drag-and-drop bio page builder with themes, blocks, analytics | Predicts which block arrangement drives most clicks per audience |
| **QR Codes** | Dynamic QR codes with embedded logos, customizable designs, scan analytics | Predicts scan rates by placement context (print, digital, packaging) |
| **File Transfer** | Upload, share, track downloads with expiration and access controls | Predicts download completion rates and suggests optimal file sizes |
| **Link Analytics** | Click tracking with geo, device, referrer, time breakdown | Predicts future click patterns and identifies emerging traffic sources |
| **Campaign Management** | Group links into campaigns with UTM parameters | Predicts campaign ROI before launch based on historical performance |
| **Custom Domains** | Connect branded domains for short links | — |
| **Bulk Operations** | Import/export links in CSV, bulk-create short URLs | — |
| **Deep Links** | Platform-specific deep linking for mobile apps | Predicts app-open vs. web-fallback rate by device/platform |
| **Link Rotation** | A/B test multiple destinations behind one link | Auto-optimizes rotation weights based on conversion prediction |

### 7.3 Screens

| Route | Description | Key Components |
|---|---|---|
| `/link` | Dashboard: total links, clicks today, top-performing links, click trend | StatCards, ClickTrendChart, TopLinksTable |
| `/link/links` | All short links with click count, status, destination | LinkTable with inline copy button, QR popover, status toggle |
| `/link/links/new` | Create short link with options | URLInput, SlugEditor, DomainSelector, ExpirationPicker, PasswordToggle, UTMBuilder |
| `/link/links/[id]` | Link detail: click analytics, geo map, device breakdown, referrers | ClickChart, GeoMap, DevicePieChart, ReferrerTable, TimeHeatmap |
| `/link/bio` | Bio link pages list | BioPageGrid with live preview thumbnails |
| `/link/bio/new` | Bio page builder | DragDropEditor with block palette, theme selector, live preview |
| `/link/bio/[id]` | Bio page editor and analytics | Same builder with click analytics per block |
| `/link/qr` | QR code library | QRGrid with scan counts, design previews |
| `/link/qr/new` | QR code generator | QRDesigner with logo upload, color picker, shape selector, download (SVG/PNG/PDF) |
| `/link/transfer` | File transfer dashboard | FileList with download counts, expiration status, storage usage |
| `/link/transfer/upload` | Upload interface | DragDropUpload with progress bar, access control settings |
| `/link/domains` | Custom domain management | DomainTable with DNS verification status, SSL indicator |
| `/link/campaigns` | Campaign management | CampaignTable with aggregated click stats, UTM summary |

### 7.4 Bio Link Page Builder — Detailed UX

**Left sidebar (Block palette):**
- **Content blocks**: Link, Header, Text, Image, Video, Divider, Spacer
- **Social blocks**: Social icons bar, Follow button, Share button
- **Commerce blocks**: Product card, Donation, Payment link
- **Embed blocks**: YouTube, Spotify, SoundCloud, Map, Calendar
- **Form blocks**: Email capture, Contact form, Newsletter signup

**Center canvas:**
- Live preview at mobile width (375px) centered on canvas
- Blocks are drag-reorderable with a smooth 200ms spring animation
- Selected block shows a blue outline with resize handles
- Phone frame overlay (toggleable) for context

**Right panel (Settings):**
- **Page settings**: Title, bio text, avatar upload, page URL, SEO meta
- **Theme selector**: Grid of theme previews (light, dark, gradient, animated)
- **Block settings**: Per-block configuration (URL, label, icon, color, animation)
- **Analytics**: Click count per block, top-performing blocks highlighted in green

**Motions:**
- Drag block from palette → ghost follows cursor, insertion point highlighted with blue line
- Drop block → it expands from 0 height to full height (200ms spring)
- Reorder blocks → surrounding blocks smoothly slide to accommodate (200ms)
- Delete block → slide left with fade (150ms)
- Theme change → all blocks cross-fade to new theme (400ms)

---

## 8. Tool 4: Market — AI Content, Ads, SEO & Social Suite

### 8.1 Purpose

An all-in-one content creation, advertising, SEO, and social media management platform. The heaviest tool in the suite, Market encompasses content generation (articles, social posts, ad copy, video, images), ad campaign management, SEO research and optimization, social scheduling and monitoring, and brand management.

### 8.2 Core Feature Groups

#### 8.2.1 AI Content Studio

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **AI Article Writer** | Generate full articles with images based on trending topics | Predicts which topics will drive most organic traffic in the next 30 days |
| **AI Blog Agent** | Auto-blogging with Shopify, WooCommerce, WordPress, Ghost support | Predicts optimal publishing frequency and topic mix for traffic growth |
| **AI Humanizer** | Rewrite AI-generated content to pass AI detection | Scores content "naturalness" and iterates until threshold met |
| **AI Rewriter** | Rephrase content while preserving meaning | Suggests which sections need rewriting based on engagement prediction |
| **SEO Editor** | Write with real-time SEO scoring and keyword suggestions | Predicts ranking potential for target keywords before publishing |
| **AI Image Generator** | Generate images for articles, social posts, ads | Predicts which image styles drive highest engagement per platform |
| **AI Video Generator** | Text-to-video, image-to-video, UGC factory | Predicts video completion rate by format and platform |
| **AI Voiceover** | Text-to-speech for video narration, podcasts | — |
| **AI Music** | Generate background music for videos | — |
| **AI Creative Suite** | 20+ templates for on-brand asset creation | Suggests templates based on predicted best-performing creative type |

#### 8.2.2 Social Media Suite

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Multi-Platform Posting** | Facebook, Google, Instagram, Threads, TikTok, YouTube, Twitter/X | Predicts optimal posting time per platform per audience segment |
| **Auto-Posting** | Schedule and auto-publish across all platforms | AI selects best time based on prediction model |
| **Social Calendar** | Visual calendar view of all scheduled content | Highlights gaps and suggests content for predicted high-engagement windows |
| **Multi-Account** | Manage multiple social accounts per platform | Cross-account performance comparison with predictions |
| **Brand Mentions** | Monitor brand mentions across social platforms | Sentiment prediction and escalation triggers |
| **Influencer Discovery** | Find influencers who move the needle | Predicts influencer ROI based on audience overlap and engagement patterns |
| **DM/Comment Agent** | AI-powered social media response agent | Predicts which comments need human attention vs. AI response |
| **Carousel Support** | Multi-image posts with per-image analytics | Predicts optimal carousel length and image order |
| **Stories** | Post stories across platforms | — |

#### 8.2.3 Ad Generation & Campaign Management

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Ad Copy Generator** | AI-generated ad copy for all platforms | Pre-launch copy scoring with predicted CTR |
| **Ad Image Generator** | Product photoshoots, fashion studio, avatar studio | Predicts which visual style drives highest conversion per audience |
| **Ad Video Generator** | UGC factory, video agent, social media studio | Predicts video ad completion rate and CPA |
| **Ad Drafting** | Draft ad sets with targeting, budget, schedule | Budget allocation optimizer based on predicted ROAS per audience |
| **Ad Campaign Management** | Launch and manage campaigns on Facebook, Google, Instagram, TikTok, YouTube, Twitter/X | Real-time bid adjustment based on performance prediction |
| **Ad Analytics** | CTR, CPA, ROAS, conversion tracking | Anomaly detection with predicted normal ranges |
| **Channel Broadcast** | Cross-platform ad distribution | Predicts which platforms to prioritize based on audience affinity |

#### 8.2.4 SEO & AI Search

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Keyword Research** | DataForSEO-powered keyword discovery | Predicts keyword difficulty trajectory and optimal targeting window |
| **Rank Tracking** | Daily position tracking for target keywords | Predicts ranking changes before they happen based on SERP movement patterns |
| **Backlink Analysis** | Backlink profile audit and monitoring | Predicts which backlinks will be lost and their ranking impact |
| **Technical SEO Audit** | Overlaps with Audit tool — delegates via bus | — (handled by Audit tool) |
| **AI Search Visibility** | Track how LLMs cite your brand in AI answers | Predicts AI answer inclusion probability based on content structure |
| **Local SEO** | Google Business Profile optimization, directory management | Predicts local pack ranking for target queries |
| **Competitor Analysis** | Track competitor rankings, content, ads | Predicts competitive gaps worth targeting |
| **Trend Checking** | Real-time trending topic detection | Predicts trend duration and optimal content creation window |
| **Store Revenue Estimation** | Shopify/ecommerce store revenue estimation | Predicts revenue trends based on traffic and conversion patterns |

### 8.3 Screens (High-Level — Market is the largest tool)

| Route | Description |
|---|---|
| `/market` | Dashboard: content performance, social metrics, ad ROAS, SEO score |
| `/market/content` | AI content studio: article writer, humanizer, rewriter, SEO editor |
| `/market/content/articles` | Article library with publish status, traffic, engagement |
| `/market/content/articles/new` | Article creation wizard with topic selection, outline, generation, editing |
| `/market/social` | Social media command center: calendar, posts, mentions, influencers |
| `/market/social/calendar` | Visual calendar with drag-to-schedule |
| `/market/social/posts` | Post library with per-platform performance |
| `/market/social/posts/new` | Post composer with AI generation, multi-platform preview |
| `/market/social/mentions` | Brand mention feed with sentiment badges |
| `/market/social/influencers` | Influencer discovery with audience overlap scoring |
| `/market/ads` | Ad command center: campaigns, creatives, analytics |
| `/market/ads/campaigns` | Campaign list with performance metrics |
| `/market/ads/campaigns/new` | Campaign builder with audience, budget, creative selection |
| `/market/ads/studio` | Creative studio: image, video, UGC generation |
| `/market/ads/studio/images` | Image generation with product photoshoot, fashion studio |
| `/market/ads/studio/videos` | Video generation with UGC factory, avatar studio |
| `/market/ads/copy` | Copy library with performance scoring |
| `/market/ads/analytics` | Ad analytics: CTR, CPA, ROAS, attribution |
| `/market/seo` | SEO dashboard: rank tracker, keyword research, backlinks |
| `/market/seo/keywords` | Keyword research tool with difficulty scoring |
| `/market/seo/rankings` | Position tracking with trend charts |
| `/market/seo/backlinks` | Backlink profile with quality scoring |
| `/market/seo/ai-search` | AI search visibility tracker |
| `/market/seo/local` | Local SEO management: GBP, directories |
| `/market/seo/competitors` | Competitor analysis dashboard |
| `/market/seo/trends` | Trending topic discovery |
| `/market/brands` | Brand management: voice, assets, guidelines |
| `/market/projects` | Project organization for content and campaigns |
| `/market/team` | Team management for Market |

---

## 9. Tool 5: Monitor — Uptime, Performance & Incident Management

### 9.1 Purpose

Comprehensive uptime monitoring, API monitoring, performance analytics, incident management, status pages, and synthetic browser testing.

### 9.2 Core Features

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Website Monitoring** | HTTP/HTTPS checks from multiple global locations | Predicts downtime probability based on historical patterns |
| **API Monitoring** | REST/GraphQL/gRPC endpoint monitoring with assertion chains | Predicts API degradation before it becomes an outage |
| **SSL Monitoring** | Certificate expiration tracking and renewal alerts | — (deterministic, no prediction needed) |
| **Port Monitoring** | TCP port availability checks | — |
| **Ping Monitoring** | ICMP ping with latency tracking | Predicts latency spikes based on time-of-day and load patterns |
| **DNS Monitoring** | DNS resolution checks and propagation tracking | — |
| **Keyword Monitoring** | Check for presence/absence of specific content on a page | — |
| **Performance Analytics** | Page load timing, TTFB, FCP, LCP, CLS breakdown | Predicts performance impact on conversion rate |
| **Status Pages** | Public and private status pages with incident history | Auto-generates incident descriptions from monitoring data |
| **Incident Management** | Alert routing, escalation, post-mortem tracking | Predicts incident severity and expected resolution time |
| **Synthetic Monitoring** | Real browser tests for critical user flows | Predicts which flows are most likely to break based on deployment patterns |
| **Internal Monitoring** | Behind-the-firewall checks with agent deployment | — |
| **Multi-Location** | Checks from 10+ global regions | Predicts geo-specific availability issues |

### 9.3 Screens

| Route | Description | Key Components |
|---|---|---|
| `/monitor` | Dashboard: overall uptime %, active incidents, check summary | UptimeGauge, IncidentFeed, CheckStatusGrid |
| `/monitor/checks` | All monitoring checks with status, uptime %, response time | CheckTable with colored status dots, sparkline response time |
| `/monitor/checks/new` | Create new check (type selector → configuration wizard) | TypeSelector, ConfigForm (varies by type), TestButton |
| `/monitor/checks/[id]` | Check detail: uptime chart, response time trend, incidents, settings | UptimeBar (day-by-day colored blocks), ResponseTimeChart, IncidentTimeline |
| `/monitor/incidents` | All incidents with severity, duration, affected checks | IncidentTable with severity badges, duration bars |
| `/monitor/incidents/[id]` | Incident detail: timeline, affected checks, post-mortem | IncidentTimeline with status transitions, AffectedChecks list |
| `/monitor/status-pages` | Public status page management | StatusPageList, PublicURLColumn, CustomDomainStatus |
| `/monitor/status-pages/[id]` | Status page editor and preview | StatusPageBuilder, ComponentSelector, LivePreview |
| `/monitor/performance` | Performance analytics: Core Web Vitals trends | WebVitalsChart, PageLoadWaterfall, GeoPerformanceMap |
| `/monitor/alerts` | Alert configuration: channels, escalation rules | AlertChannelTable (email, Slack, webhook, SMS, push), EscalationChain |

---

## 10. Tool 6: Track — Web Analytics, Competitive Intelligence & Audience

### 10.1 Purpose

Privacy-friendly web analytics, competitive intelligence, audience insights, and trend benchmarking. See where traffic comes from, track the hottest trends, benchmark competitors, and unlock audience insights you can act on.

### 10.2 Core Features

| Feature | Description | Predictive Enhancement |
|---|---|---|
| **Web Analytics** | Privacy-first analytics (no cookies by default), lightweight script | Predicts traffic trends and anomalies |
| **Real-Time Dashboard** | Live visitor count, active pages, current referrers | Predicts traffic surge patterns and capacity needs |
| **Traffic Sources** | Referrer breakdown: organic, paid, social, direct, email | Predicts which traffic sources will grow/decline |
| **Goal Tracking** | Custom conversion goals with funnel visualization | Predicts conversion rate changes and identifies friction points |
| **UTM Attribution** | Full campaign attribution with multi-touch support | Predicts which attribution paths lead to highest-value customers |
| **Heatmaps** | Click, scroll, and movement heatmaps | Predicts which page areas will receive most attention |
| **Session Replay** | Privacy-compliant session recordings | AI-powered session analysis identifies frustration signals |
| **Competitive Intelligence** | Estimate competitor traffic, keywords, technology stack | Predicts competitive market share shifts |
| **Audience Segments** | Auto-created and custom audience segments | Predicts segment behavior differences and recommends targeting |
| **Trend Benchmarking** | Compare your metrics against industry benchmarks | Predicts where you'll land vs. benchmarks in 30/60/90 days |
| **Visitor Profiles** | Anonymous visitor behavior profiles (no PII) | Predicts individual visitor intent and lifetime value |
| **Custom Reports** | Drag-and-drop report builder with scheduling | — |
| **Data Export** | CSV, JSON, API access to all data | — |

### 10.3 Screens

| Route | Description | Key Components |
|---|---|---|
| `/track` | Dashboard: visitors today, pageviews, bounce rate, top pages, referrers | StatCards with trend arrows, VisitorChart, TopPagesTable, ReferrerChart |
| `/track/realtime` | Live dashboard: active visitors, current pages, live event stream | LiveCounter (animates on change), ActivePagesLive, EventStream |
| `/track/pages` | Page analytics: all pages with views, time on page, bounce rate | PageTable with sparklines, entry/exit rates |
| `/track/referrers` | Traffic source breakdown with multi-level drill-down | SourceTreeMap, ReferrerTable, ChannelGrouping |
| `/track/campaigns` | UTM campaign performance | CampaignTable with conversion rates, revenue attribution |
| `/track/goals` | Conversion goals with funnel visualization | FunnelChart, GoalTable with completion rates, value tracking |
| `/track/audience` | Audience segments with behavior profiles | SegmentCards, BehaviorComparison, OverlapDiagram |
| `/track/audience/[id]` | Segment detail: demographics, behavior, predicted actions | SegmentProfile, BehaviorTimeline, PredictedActions |
| `/track/heatmaps` | Heatmap viewer by page | PageSelector, HeatmapOverlay (click/scroll/move toggle) |
| `/track/recordings` | Session replay library | RecordingList with duration, page count, frustration score |
| `/track/competitors` | Competitive intelligence dashboard | CompetitorTable, TrafficComparison, KeywordOverlap |
| `/track/trends` | Industry trend benchmarking | TrendChart, BenchmarkComparison, MarketSharePie |
| `/track/reports` | Custom report builder and scheduled reports | ReportBuilder (drag-drop widgets), ScheduleSelector |
| `/track/settings` | Tracking code installation, domains, goals configuration | SnippetCode with copy button, DomainVerification, GoalBuilder |

---

## 11. Design System — UI/UX, Look, Feel, Motion & Components

### 11.1 Design Philosophy

**Indigo-ink ledger on frosted glass.** The visual language borrows from Stripe's financial-instrument aesthetic: dense information, crisp rules, generous whitespace, and color appearing only when something needs to be acted on. The platform feels like a precision tool — confident, restrained, and data-rich.

### 11.2 Color System

#### Light Theme (Default)

| Token | Value | Role |
|---|---|---|
| `--accent` | `#533afd` | Primary action: filled buttons, selected nav, focused conversion moments |
| `--accent-hover` | `#7389ff` | Hover state for accent elements |
| `--accent-solid` | `#4830e0` | Button fills that need white text at WCAG AA on all states |
| `--accent-wash` | `#e8e9ff` | Light tinted background for prediction cards, selected states |
| `--text-primary` | `#061b31` | Headings and primary body text |
| `--text-secondary` | `#64748d` | Labels, captions, supporting text |
| `--text-tertiary` | `#50617a` | Meta text, timestamps, helper copy |
| `--text-faint` | `#6b7fa0` | Cleared 4.5:1 on every surface including accent-wash |
| `--surface-canvas` | `#ffffff` | Page background |
| `--surface-band` | `#f8fafd` | Section banding, sidebar background |
| `--surface-frost` | `#e5edf5` | Borders, dividers, hover backgrounds |
| `--surface-lavender` | `#e8e9ff` | Highlighted cards, prediction panels |
| `--status-success` | `#0d7c43` | Passes WCAG as text on white — not the default web green |
| `--status-warning` | `#9a5b00` | Passes WCAG as text on white |
| `--status-error` | `#d32f2f` | Error states, critical alerts |
| `--on-accent` | `#ffffff` | Text on filled accent surfaces |

#### Dark Theme

All values have dark-mode counterparts. Key shifts:
- Canvas becomes `#0d1117`
- Accent lifts to `#7389ff` for legibility
- Text primary becomes `#e6edf3`
- Surfaces shift to `#161b22`, `#21262d`, `#30363d`

### 11.3 Typography

**Primary font:** Inter Tight (variable, weight 300/400) — substitute for Stripe's proprietary sohne-var.

| Role | Size | Weight | Line Height | Letter Spacing |
|---|---|---|---|---|
| Display | 56px | 300 | 1.03 | -1.40px |
| Heading LG | 48px | 300 | 1.03 | -0.96px |
| Heading | 32px | 300 | 1.10 | -0.64px |
| Heading SM | 26px | 300 | 1.12 | -0.26px |
| Subheading | 22px | 300 | 1.10 | -0.22px |
| Body LG | 20px | 300 | 1.40 | -0.20px |
| Body | 16px | 400 | 1.20 | -0.16px |
| Body SM | 14px | 400 | 1.40 | -0.14px |
| Caption | 12px | 400 | 1.45 | -0.12px |

**Key principle:** Headlines whisper at weight 300 even at 56px — authority through restraint, not volume. Weight 400 appears only at 14–16px where small-size readability needs the extra stroke.

**OpenType features:** `"ss01" on, "tnum" on` (tabular numerals for all data displays).

### 11.4 Spacing & Layout

| Token | Value |
|---|---|
| Base unit | 8px |
| Spacing scale | 8, 16, 24, 32, 40, 48, 64, 80, 96 |
| Page max-width | 1320px |
| Section gap | 96px |
| Card padding | 32px |
| Element gap | 8px |
| Border radius | 4px (buttons, cards, inputs) · 9999px (tags, pills) |

### 11.5 Navigation — Dual-Tier Sidebar

The platform uses a **dual-tier sidebar** navigation:

#### Tier 1 — Tool Rail (Left Edge, 56px Wide)

A narrow vertical rail showing the 6 tool icons + the prediction icon:

```
┌──────┐
│  🏠  │  Home / Overview
│──────│
│  🔍  │  Audit
│  ✓   │  Confirm
│  🔗  │  Link
│  📢  │  Market
│  📡  │  Monitor
│  📊  │  Track
│──────│
│  🔮  │  Predictions
│──────│
│  ⚙️  │  Settings
└──────┘
```

- Each icon is 24px, centered in a 40×40px hit area
- Active tool: icon fills with `--accent`, background tints with `--accent-wash`
- Hover: `--surface-frost` background with 150ms ease
- Tooltip on hover shows tool name (200ms delay)

#### Tier 2 — Section Sidebar (200px Wide)

Expands from the selected tool showing its sections:

```
┌─────────────────────┐
│ AUDIT               │
│─────────────────────│
│ 📋 Overview         │
│ 🌐 Websites         │
│ ⚠️  Issues          │
│ 📜 Runs             │
│ 📏 Rules            │
│ 🔧 Tools            │
│ 📤 Reports          │
│─────────────────────│
│ PREDICTIONS         │
│ 📈 Score Forecast   │
│ 🎯 Priority Fixes   │
└─────────────────────┘
```

- Section items: 14px body-sm, `--text-secondary`
- Active section: `--accent` text, `--accent-wash` background pill
- Hover: `--surface-frost` background (150ms)
- Collapsed state: Tier 2 slides away (200ms), Tier 1 remains visible
- Keyboard: `[` toggles sidebar collapse

### 11.6 Component Library

#### Data Cards

- White surface, no shadow, 1px `--surface-frost` border
- 32px internal padding
- Header: caption-size label + heading-sm value
- Optional sparkline (48px tall, inline, `--accent` stroke)
- Optional trend arrow (▲ green / ▼ red / → gray with percentage)

#### Data Tables

- Edge-to-edge within white card
- Headers: 12px caption, uppercase, `--text-secondary`, `--surface-band` background
- Rows: 14px body-sm, 1px `--surface-frost` bottom border
- Hover: `--surface-band` background (100ms)
- Selected: `--accent-wash` background
- Sortable columns: click header → ascending/descending with sort icon transition (150ms rotate)
- Sticky header on scroll
- Bulk actions: checkbox column, floating action bar appears (200ms slide-up)
- Empty state: illustration + message + CTA button
- Loading state: skeleton rows (shimmer animation, 1.5s infinite)

#### Charts

- **Line charts**: 2px stroke, `--accent` primary series, `--text-faint` secondary series
- **Area charts**: Same as line with 10% opacity fill
- **Bar charts**: `--accent` fill, 4px radius top corners, 2px gap between bars
- **Donut charts**: 16px stroke width, 160px diameter, label in center
- **Sparklines**: 48px tall, no axis labels, `--accent` stroke
- **Heatmaps**: Intensity scale from `--surface-band` to `--accent`
- **Geo maps**: Vector world map with country fills on intensity scale

All charts:
- Hover: crosshair + tooltip with exact values (150ms fade-in)
- Animate on mount: line draws left-to-right (600ms ease-out), bars rise from bottom (400ms spring)
- Responsive: charts resize fluidly, legend wraps on mobile
- Time range selector: tabs (24h, 7d, 30d, 90d, custom) above chart

#### Forms

- **Text inputs**: 40px height, 4px radius, 1px `--surface-frost` border, `--surface-canvas` background
- **Focus state**: border transitions to `--accent` (150ms), subtle box-shadow (`0 0 0 3px var(--accent-wash)`)
- **Labels**: 14px body-sm, `--text-primary`, 4px gap above input
- **Helper text**: 12px caption, `--text-secondary`, 4px gap below input
- **Error state**: border becomes `--status-error`, error message appears below (200ms slide-down), field shakes on submit (200ms)
- **Select dropdowns**: Native-styled with custom chevron, same sizing as text inputs
- **Toggles**: 40×20px track, 16px dot, `--surface-frost` off state, `--accent` on state, 200ms spring transition
- **Radio/Checkbox**: 20px, `--accent` checked state with check animation (150ms spring)

#### Buttons

| Type | Style | Usage |
|---|---|---|
| Primary filled | `--accent-solid` bg, `--on-accent` text, 4px radius | Main CTA — one per screen section |
| Ghost outline | Transparent bg, `--accent` text, 1px `--lavender-border` | Secondary action paired with filled |
| Tertiary | Transparent bg, `--accent` text, no border | Low-emphasis inline action |
| Danger | `--status-error` bg, white text | Destructive actions |
| Disabled | 40% opacity, `cursor: not-allowed` | Unavailable actions |

All buttons: 40px height, padding 0 24px, 14px body-sm weight 400.
Hover: filled lightens (100ms), outline gets `--surface-band` bg (100ms).
Press: scale(0.98) + darken (50ms).
Loading: text replaced by 16px spinner (fade transition).

#### Command Palette (⌘K)

- Full-width overlay at top of viewport with backdrop blur
- Search input: 56px height, display-size placeholder text, auto-focus
- Results grouped by: Tools, Resources, Commands, Navigation
- Each result: icon + label + path (right-aligned, caption-size)
- Selected result: `--accent-wash` background
- Navigation: arrow keys move selection, Enter executes, Escape closes
- Entrance: slides down from top + backdrop fades in (200ms)
- Exit: slides up + backdrop fades out (150ms)

#### Toast Notifications

- Bottom-right positioning, stacked with 8px gap
- Slide-up entrance (200ms spring), slide-right exit (150ms)
- Auto-dismiss: 5s default, 10s for undoable actions
- Undo button: `--accent` text link inside the toast
- Types: success (green left accent), error (red left accent), info (blue left accent), warning (amber left accent)

#### Empty States

- Centered in the content area
- 120px illustration (line art, `--text-faint` stroke)
- Heading: heading-sm, `--text-primary`
- Description: body, `--text-secondary`, max-width 400px
- CTA: primary filled button

#### Loading States

- **Skeleton screens**: Gray rectangles matching content shape, shimmer animation (1.5s linear infinite, left-to-right gradient sweep)
- **Progress bars**: 4px height, `--accent` fill, `--surface-frost` track, width animates on update (200ms)
- **Spinners**: 20px circle, 2px stroke, `--accent` partial arc, 0.8s rotation
- **Crawl progress**: Combined progress bar + "X of Y pages" counter + estimated time remaining

#### Modal Dialogs

- Centered overlay with backdrop blur (150ms fade-in)
- Max-width 640px (detail modals), max-width 480px (confirm modals)
- 4px radius, white surface, no shadow
- Header: heading-sm + close button (24px ×)
- Footer: action buttons right-aligned
- Entrance: scale(0.95) → scale(1) + fade (200ms spring)
- Exit: scale(1) → scale(0.95) + fade (150ms)
- Escape to close, click-outside to close (configurable)

#### Upgrade Gates

- When a feature requires a higher tier, the UI shows an `UpgradeGate`:
  - Semi-transparent overlay on the locked section
  - Lock icon + "Upgrade to [Plan Name] to unlock [Feature]"
  - Primary button: "View Plans"
  - The gate names the specific entitlement that is missing, not a generic "Upgrade" message

### 11.7 Motion System

| Motion Type | Duration | Easing | Usage |
|---|---|---|---|
| Micro-interaction | 100–150ms | ease-out | Button hover/press, toggle switch, checkbox |
| State transition | 200ms | ease-in-out | Panel open/close, tab switch, modal enter |
| Content change | 300ms | spring (0.7 tension) | Chart animation, list reorder, page transition |
| Attention | 400–600ms | ease-out | Chart mount, first-time feature highlight |
| Celebratory | 800ms | spring (0.5 tension) | Goal completion, milestone reached |

**`prefers-reduced-motion: reduce`**: One global CSS rule flattens every transition and animation to `0ms`. No per-component exceptions.

### 11.8 Landing Page Design

**Style:** Inspired by Stripe, Framer templates, and the reference sites.

**Structure:**
1. **Hero** — Left-aligned display text (56px weight 300), no hero image. The typography IS the hero. Two CTAs: filled primary + ghost outline.
2. **Social proof rail** — Customer logos in their native brand colors, vertically stacked.
3. **Tool showcase** — Each of the 6 tools presented in a card with:
   - Small uppercase label (12px, `--text-secondary`)
   - Heading (32px weight 300)
   - Body paragraph (16px, `--text-tertiary`)
   - Screenshot or animation showing the tool in action
4. **Prediction feature** — Full-width section with a live demo of the prediction engine (animated flow diagram showing signals → prediction → action)
5. **Pricing** — Toggle between per-tool and unified pricing. Three tiers + lifetime. See §13.
6. **CTA repeat** — Same hero CTA pair
7. **Footer** — `--surface-band` background, multi-column link lists

**Responsive breakpoints:**
- 1920px: Full-width sections, 3-column grids
- 1280px: Constrained content width, 3-column grids
- 768px: 2-column grids, sidebar collapses
- 375px: Single column, stacked layout, bottom navigation

---

## 12. Customer Experience (CX) — End-to-End Journeys

### 12.1 First-Time User Journey (Free Tier)

```
1. ARRIVE at landing page
   └─ Hero communicates value in one sentence
   └─ CTA: "Start Free" (no credit card)

2. SIGN UP (/sign-up)
   └─ Email + password only (or Google/GitHub OAuth)
   └─ Workspace, Default project, and owner seat provisioned by database hook
   └─ RLS scope set on first render

3. ONBOARD (/welcome)
   └─ Two questions maximum:
       a. "What's your website?" (adds site to core, shared by all tools)
       b. "What do you want to do first?" (routes to tool)
   └─ No questions about company size, industry, or role — these are excuses to gate features

4. FIRST VALUE (< 60 seconds from sign-up)
   └─ If chose Audit: crawl auto-queues, first results appear in ~30 seconds
   └─ If chose Track: tracking code shown with one-click copy, first pageview appears in real-time
   └─ If chose Link: first short link created inline, QR code generated
   └─ If chose Confirm: first social proof widget configured with preview
   └─ If chose Market: first article topic suggested based on website analysis
   └─ If chose Monitor: first uptime check created, instant check runs

5. GUIDED SETUP CHECKLIST (docked bottom-right)
   └─ 4 steps per tool, tool-specific
   └─ Progress bar with strikethrough animation on completion
   └─ Dismissible but re-accessible from Help menu
   └─ Steps reflect what the tool can actually deliver, not marketing fluff

6. DISCOVER CROSS-TOOL VALUE
   └─ Connected panel: "Your audit found 3 issues that may be affecting your tracked conversions"
   └─ Prediction card: "Based on your first 48 hours of data, we predict [X]"
   └─ Automation suggestion: "Enable 'SEO Rescue' to auto-crawl after downtime"
```

### 12.2 Daily User Journey (Active Subscriber)

```
1. OPEN DASHBOARD
   └─ Home shows cross-tool overview: key metrics from each active tool
   └─ Prediction panel: top 3 actionable predictions across all tools
   └─ Notification badge: alerts, completed jobs, automation results

2. ACT ON PREDICTIONS
   └─ Click prediction → see detail → execute recommended action (1 click)
   └─ Or: dismiss prediction with reason (improves model)

3. USE TOOLS
   └─ ⌘K command palette for instant navigation
   └─ Each tool's dashboard shows its own prediction panel
   └─ Cross-tool links: "This page has 3 audit issues" on a Track page detail

4. REVIEW AUTOMATIONS
   └─ Automation log: what ran, what it did, what the outcome was
   └─ Revenue attribution: "This automation generated $X in attributed revenue"

5. EXPORT & SHARE
   └─ Every tool supports CSV/JSON export on every plan
   └─ Branded PDF reports on paid tiers
   └─ Shareable dashboard links (read-only, time-limited)
```

### 12.3 Limit Visibility & Upgrade Path

Every limit is visible **before** it is hit:

- "4 of 5 websites used" — not "You've exceeded your limit"
- "2,847 of 50,000 crawl pages used this period"
- When approaching limit (>80%): amber usage bar
- When at limit: clear message with upgrade CTA naming the specific entitlement
- When on free tier using a paid feature: `UpgradeGate` with specific plan recommendation

---

## 13. Pricing, Credits & Monetization

### 13.1 Model Overview

The platform supports **three concurrent monetization models**:

| Model | Description |
|---|---|
| **Per-Tool Subscription** | Subscribe to individual tools at separate pricing tiers |
| **Unified Subscription** | Subscribe to all 6 tools at a bundled price (discount vs. individual) |
| **Credits** | Purchase credits outright or receive included credits with any subscription; usable across all tools |
| **Lifetime** | One-time purchase for perpetual access — **excludes all AI features structurally** (database trigger, resolver, and `ai.execute()` all enforce this) |

### 13.2 Subscription Tiers (Per Tool)

Each tool has **independently configurable tiers** that can be created, edited, and deleted by the admin:

| Tier | Characteristics |
|---|---|
| **Free** | Costs platform nothing to run. No AI calls, no background jobs, no external API calls. Demonstrates capability. |
| **Starter** | Entry-level paid tier. Moderate limits. Some AI features. Monthly credits included. |
| **Professional** | Higher limits. Full AI features. More monthly credits. |
| **Business** | Highest limits. Team features. Priority support. Most monthly credits. |

### 13.3 Unified Tiers

Unified pricing bundles all 6 tools:

| Tier | What It Includes |
|---|---|
| **Unified Starter** | Starter-level access to all 6 tools + cross-tool predictions + automations |
| **Unified Professional** | Professional-level access to all 6 tools + priority AI + advanced predictions |
| **Unified Business** | Business-level access to all 6 tools + custom models + dedicated support |

Limits merge: `MAX` for per-feature limits (e.g., max websites = highest of any plan), `SUM` for quotas (e.g., crawl pages = sum of all plans).

### 13.4 Lifetime Pricing

- One-time purchase for perpetual access to all tools
- **Excludes AI features at three independent enforcement points:**
  1. Database trigger on `plan_entitlements` prevents AI entitlements on lifetime plans
  2. Entitlement resolver returns `ai_excluded_lifetime` for any AI feature
  3. `ai.execute()` re-resolves entitlements immediately before every vendor call
- Lifetime holders with 5,000 credits and their own API key still get `ai_excluded_lifetime`
- Does not include credits (credits are a running cost; lifetime is a one-time cost)

### 13.5 Credits System

- Credits can be purchased outright or come included with subscriptions
- Credits are **FIFO expiring buckets**: `reserve()` draws from oldest first, `capture()` trues up to actual cost, `release()` restores to the *same* buckets (preserving expiry)
- A failed AI generation costs the user nothing
- Credits work across all tools and features
- Each AI operation has a credit cost displayed before execution
- Usage dashboard shows: credits remaining, credits used this period, credits by tool, credits by feature

### 13.6 Admin Pricing Controls

Admins can:
- Create/edit/delete unlimited tiers per tool
- Create/edit/delete unlimited unified tiers
- Set feature limits per tier per entitlement
- Set monthly credit grants per tier
- Set credit prices (bulk discount tiers)
- Set lifetime pricing
- Preview entitlement resolution for any plan combination

---

## 14. AI Provider Registry & Cost Management

### 14.1 Provider Configuration

Every AI feature routes through `@mamal/ai`, which maintains a **provider registry** with hot-swappable drivers:

| Role | Default Provider | Configurable Alternatives |
|---|---|---|
| **Predictive Analytics** | OpenAI Astra | Claude, DeepSeek, Gemini |
| **Automation/Orchestration** | Claude | OpenAI, Gemini, Grok |
| **Reasoning** | DeepSeek Pro | Claude, OpenAI o3, Gemini |
| **Video Generation** | Seedance 2.5 (ByteDance) | Kling, Minimax, Veo 3, Sora 2 |
| **Image Generation** | NanoBanana (Gemini 2.5 Flash Image) | DALL-E 3, Flux Pro, Stable Diffusion, Imagen 4 |
| **Audio/Voice** | ElevenLabs | Google TTS, Azure TTS, OpenAI TTS |
| **Music** | — (configurable) | — |
| **SEO/Ranking Data** | DataForSEO API | — |
| **Text Generation** | OpenAI GPT-5 | Claude Opus 5, Gemini 3.5, DeepSeek, Grok 4, GLM, Kimi, Qwen |

### 14.2 Switching Providers

1–3 click provider switch:
1. Navigate to `/settings/ai-providers`
2. Select the feature role (e.g., "Image Generation")
3. Select the new provider from the dropdown → credentials auto-validated → switch active

### 14.3 Cost Management

- **Every API call has a recorded cost** from the provider's pricing
- **Costs auto-update** based on provider pricing changes (daily sync)
- **25% margin** automatically applied to every user-facing AI action
- **Cost breakdown** visible in admin dashboard:
  - Cost by provider, by feature, by user, by workspace
  - Margin analysis: revenue from credits vs. cost to providers
  - Projected monthly cost based on current usage trajectory
- **Budget alerts**: Set maximum daily/weekly/monthly spend per provider
- **Cost per action** displayed to users before executing AI operations

### 14.4 Full Provider List

All providers are toggleable (on/off per feature):

**LLM Providers:** OpenAI (GPT-5.x, o3, o4-mini), Anthropic (Claude Opus 5, Sonnet 4.6), Google (Gemini 3.5 Flash/Pro), DeepSeek (R1, Pro), xAI (Grok 4.x), Meta, GLM, Kimi, Qwen, Perplexity, OpenRouter

**Image Providers:** OpenAI (GPT Image 2.0), Google (Imagen 4, NanoBanana), Stability AI (SD3, Flux), Midjourney, Freepik, Novita

**Video Providers:** ByteDance (Seedance 2.5), Kling (2.6+), Google (Veo 3.1), OpenAI (Sora 2), Minimax, Luma

**Audio Providers:** ElevenLabs (v3), Google TTS, Azure TTS, OpenAI TTS, Speechify

**Music Providers:** (configurable)

**SEO Data:** DataForSEO

### 14.5 Master AI Toggle

- **Instance-level**: Admin can disable all AI features across the entire platform (singleton row in `instance_settings`)
- **Workspace-level**: Workspace owner can disable AI for their workspace
- **Per-feature toggles**: Each AI feature can be individually toggled on/off
- When AI is off, the UI shows the **specific reason**: "AI is switched off for this workspace", "not enough credits", "no provider key is configured" — never a generic denial

---

## 15. Plugin Ecosystem & Additional Features

### 15.1 Core Plugins

| Plugin | Description |
|---|---|
| **Affiliate** | Referral program with one-time or recurring commission, link tracking, payouts |
| **Email Signatures** | HTML email signature generator with brand consistency |
| **Image Optimizer** | Automatic image compression and format conversion (WebP, AVIF) |
| **Newsletters** | Email newsletter builder with subscriber management and analytics |
| **Push Notifications** | Web push notification system (integrated with Confirm's push infrastructure) |
| **PWA** | Progressive Web App configuration for the platform itself |
| **Teams** | Team management with roles, permissions, seat management |
| **Offload** | Media offloading to S3/R2/GCS for storage optimization |
| **Payment Blocks** | Custom payment form blocks for bio link pages |
| **Pro Blocks** | Advanced bio link page blocks (countdown, accordion, FAQ, testimonials) |

### 15.2 Onboarding System

- Free tier costs the platform nothing to run — no external API calls, no background jobs
- 2-question onboarding flow (website + first tool)
- Guided setup checklist per tool
- Progressive disclosure: features revealed as the user demonstrates need

### 15.3 Automation Templates

Pre-built recipes (see §4.2) plus user-creatable flows on the visual canvas (see §4.3).

---

## 16. Mobile Responsiveness & PWA

### 16.1 Responsive Breakpoints

| Breakpoint | Layout Adaptation |
|---|---|
| **≥1920px** | Full dual-tier sidebar, 3–4 column grids, full chart detail |
| **1280–1919px** | Full dual-tier sidebar, 3-column grids, standard chart detail |
| **768–1279px** | Tier 2 sidebar collapses (toggle), 2-column grids, simplified charts |
| **375–767px** | Both tiers collapse to bottom tab bar, single column, stacked cards |
| **<375px** | Same as 375px with tighter padding (16px → 12px) |

### 16.2 Mobile Adaptations

- **Bottom tab bar** replaces sidebar at <768px: 5 icons (Home, Tools, Predictions, Search, Settings)
- **Tools menu** at mobile: full-screen drawer listing all 6 tools
- **Data tables** at mobile: horizontally scrollable with `tabindex="0"` for keyboard access
- **Charts** at mobile: simplified (fewer data points, no hover tooltip — tap to inspect)
- **Modals** at mobile: full-screen sheets sliding up from bottom
- **Forms** at mobile: single-column, larger touch targets (48px minimum)

### 16.3 PWA Features

- **Install prompt** after 2nd visit with engagement threshold
- **Offline mode**: cached dashboard skeleton with "Last updated X ago" indicator
- **Push notifications**: integrated with the Confirm push infrastructure
- **App icon**: platform icon with badge count for unread notifications
- **Background sync**: queue actions taken offline and sync when connected

---

## 17. Recommended Tech Stack

### 17.1 Existing Stack (Already Built)

The platform already has a mature monorepo (`/platform`) with:

| Layer | Technology | Notes |
|---|---|---|
| **Monorepo** | pnpm 11 + Turborepo | Workspace-aware, parallel builds |
| **Runtime** | Node 22+ | ES modules, top-level await |
| **Framework** | Next.js 16 (App Router) | SSR, API routes, middleware |
| **Language** | TypeScript 5.9 | Strict mode, project references |
| **Database** | PostgreSQL 16 + Drizzle ORM | 64-table schema, RLS on every tenant table |
| **Schema Validation** | Zod | Shared between forms, API, MCP |
| **Testing** | Vitest + Playwright | 300+ unit/integration tests, 5+ E2E specs |
| **Linting** | ESLint 10 | Architectural boundary enforcement |
| **Workers** | Cloudflare Workers + Hono | Redirect, ingest, widget, bridge |
| **Background Jobs** | Custom queue + scheduler | Claim-and-enqueue, leader lock |
| **Auth** | Better Auth | Workspace provisioning, API keys, permission grants |

### 17.2 Recommended Additions

| Need | Recommendation | Rationale |
|---|---|---|
| **Predictive Analytics ML** | Python microservice (FastAPI) with scikit-learn/XGBoost + OpenAI Astra for advanced predictions | Heavy ML workloads are better in Python; REST boundary keeps the monorepo clean |
| **Time-Series DB** | ClickHouse (the Postgres adapter already implements the interface) | Event volume will exceed Postgres's comfortable range; the interface is already designed for this swap |
| **Real-Time** | Cloudflare Durable Objects or Ably | Live counters, real-time analytics, push |
| **Media Processing** | Cloudflare R2 + Workers for image/video processing | Storage + edge processing |
| **Email** | Resend or AWS SES | Transactional + marketing emails |
| **Canvas Flow Editor** | React Flow | Battle-tested, extensible, performant |
| **Charts** | Recharts or Nivo | React-native, composable, accessible |
| **Maps** | MapLibre GL JS | Open-source, vector tiles, geo analytics |

---

## 18. Quality Gates & Iteration Protocol

### 18.1 Four Gates Per Tool

Every tool passes through four quality gates before it is considered complete:

| Gate | What It Verifies |
|---|---|
| **G1 — Design** | Renders at 375/768/1280/1920px, light + dark, 0 axe-core violations (WCAG 2.1 AA), tokens-only (no raw hex), `prefers-reduced-motion`, keyboard-only navigation, skip link, all 5 states (empty/loading/error/404/over-limit) |
| **G2 — Workflow** | ⌘K reaches everything, new user completes primary job with no docs, every limit visible before hit, every long job cancellable/resumable, every destructive action undoable |
| **G3 — Function** | Unit + integration tests green, Playwright covers primary + 2 secondary journeys, public API documented and exercised, MCP tools documented and exercised, cross-tool handoffs demonstrated live |
| **G4 — Operation** | Load-tested at tier's limit, p95 budgets met (production build), jobs resume after kill, entitlements + credits enforced on free/lifetime/over-limit, retention actually deletes, runbook written |

### 18.2 Iteration Loops

Each tool iterates through four loops until it meets its gate:

```
LOOP 1: Design Loop
  → Build UI → Test at all breakpoints → Test both themes → Run axe-core
  → Fix violations → Re-test → REPEAT until 0 violations

LOOP 2: Workflow Loop
  → Walk through as new user → Identify confusion points → Simplify
  → Add ⌘K commands → Add limits visibility → Add undo
  → REPEAT until workflow is effortless

LOOP 3: Function Loop
  → Write tests → Build features → Run tests → Fix failures
  → Add E2E specs → Test cross-tool handoffs → Test MCP
  → REPEAT until all tests green

LOOP 4: Operation Loop
  → Load test → Identify bottlenecks → Optimize → Re-test
  → Test kill/resume → Test entitlements → Test retention
  → REPEAT until all budgets met
```

### 18.3 Build Order

Tools are built sequentially, each benefiting from the platform infrastructure laid down by its predecessors:

| Phase | Tool | Why This Order |
|---|---|---|
| 1 | **Audit** ✅ | Exercises the crawler, rule engine, job queue, and scoring — the hardest infrastructure |
| 2 | **Confirm** ✅ | Exercises the widget runtime, targeting engine, push system, and cross-tool bus |
| 3 | **Link** | Exercises the redirect worker, QR engine, bio page builder, and file storage |
| 4 | **Track** | Exercises the analytics ingest pipeline, ClickHouse adapter, and audience segmentation |
| 5 | **Monitor** | Exercises the scheduler, multi-location checks, incident management, and status pages |
| 6 | **Market** | The largest tool — exercises every AI driver, every social API, every ad platform |
| 7 | **Predictions** | After all 6 tools produce events, the prediction engine has signals to learn from |
| 8 | **Automations Canvas** | After predictions exist, the canvas can wire triggers → conditions → actions across all tools |

---

> **This document is the complete prompt specification.** It defines what to build, why, how each piece connects, what the user sees, how they interact with it, and how each feature predicts and acts on human behavior. Every screen, component, motion, and decision point is described.
>
> The next step is user review of this prompt, followed by execution against the existing `/platform` codebase.
