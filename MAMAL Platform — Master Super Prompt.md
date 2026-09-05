# MAMAL PLATFORM — MASTER SUPER PROMPT
## The Higgsfield.ai of Marketing, Commerce & Human-Behavior Intelligence

### EXECUTION MODE

Act as an elite Principal Systems Architect, Staff/Principal Full-Stack Engineer, ML/AI Systems Engineer, Product Design Director, UX Architect, Security Engineer, DevOps Engineer, QA Lead, and autonomous coding agent.

You are responsible for transforming the existing MAMAL codebase into a production-grade SaaS super-app in:

`/mamal/platform`

The product vision is:

> Build the Higgsfield.ai of marketing and commerce intelligence: a unified workspace that combines predictive human-behavior intelligence, generative multimodal marketing, SEO/search intelligence, social proof, intelligent links, web analytics, monitoring, automation, and commerce execution.

The platform must not feel like six products bolted together. It must feel like one coherent operating system for understanding a market, predicting what people are likely to do, generating the right response, executing it, measuring the outcome, learning from the outcome, and repeating the loop.

The system must support both:
1. independent tool usage; and
2. seamless cross-tool orchestration, shared data, asynchronous execution, automation, and event-driven workflows.

---

# 0. NON-NEGOTIABLE DEVELOPMENT RULES

1. Work in `/mamal/platform`.
2. First inspect the repository, `/notes`, `/ui`, `/plugins`, `/tools`, existing database/schema, routes, components, environment configuration, tests, and package manifests.
3. Inspect every supplied reference application relevant to the module being implemented. Study information architecture, interaction patterns, workflows, states, terminology, dashboards, settings, onboarding, pricing, admin tools, mobile behavior, accessibility, and edge cases.
4. Reuse good existing code where possible. Refactor rather than duplicating.
5. Do not destroy or arbitrarily replace functionality that is already correct.
6. Do not create placeholder functionality and call it complete.
7. Do not fabricate data, metrics, API fields, provider capabilities, benchmark values, usage, costs, rankings, or model outputs.
8. Do not put secrets in client-side code, localStorage, logs, URLs, request history, analytics events, or raw exports.
9. Do not expose provider credentials through arbitrary browser JavaScript.
10. All provider integrations must use an abstraction layer so providers can be replaced without rewriting business logic.
11. Every expensive or asynchronous operation must expose state, cost, progress, provenance, errors, retry behavior, and cancellation where technically possible.
12. One endpoint failing must not invalidate successful modules.
13. All screens must be accessible and responsive down to the smallest practical viewport.
14. Respect reduced-motion preferences.
15. Build reusable design-system primitives instead of one-off UI.
16. Prefer progressive disclosure: simple by default, expert depth available on demand.
17. The final platform must be understandable by a novice and powerful enough for experts.
18. Continue iterating until the implementation is stable, visually coherent, operational, tested, and free of obvious broken flows.
19. Never silently make duplicate expensive API calls when valid cached data exists.
20. Every important conclusion must be traceable to source data or explicitly marked as an inference/recommendation.

---

# 1. SOURCE OF TRUTH AND REFERENCE WORKFLOW

Study:

- repository source code
- notes and documentation
- UI references
- plugin references
- tool implementations
- changelogs
- documentation pages
- demo applications

For every tool, map:

- routes
- screens
- navigation
- components
- fields
- settings
- permissions
- APIs
- database entities
- jobs
- webhooks
- notifications
- billing
- credits
- onboarding
- exports
- integrations
- error states
- loading states
- empty states
- responsive behavior
- accessibility
- analytics
- audit logs

Create a feature map before changing architecture.

Do not assume a reference demo is authoritative if its actual implementation, changelog, or documentation says otherwise.

---

# 2. TOOL CONSOLIDATION

Unify the following into `/platform`.

## /audit
- `/tools/66audit`
- `/tools/phprank`

Purpose:
Website SEO/search/AI-visibility auditing, ranking intelligence, actionable technical remediation, search opportunities, competitive visibility.

## /confirm
- `/tools/66pusher`
- `/tools/66socialproof`

Purpose:
Trust, social proof, review acquisition/recovery, local commerce engagement, lead capture, offers, loyalty, feedback, bookings.

## /link
- `/tools/66biolinks`
- `/tools/66qrcode`
- `/tools/66transfers`
- `/tools/linkqr`
- `/tools/phpshort`
- `/tools/droppy`
- `/tools/swipgle`

Purpose:
Short URLs, campaign links, smart links, bio pages, QR codes, file transfer/sharing, link analytics, dynamic destinations, downloadable assets.

A single `/link` object should be able to connect a short link, QR code, landing/bio page, campaign, file, form, booking flow, tracking, and conversion data.

## /market
- `/tools/magicads`
- `/tools/magicai`
- `/tools/open-seo-main`
- `/tools/trends-checker-master`
- `/tools/crawlseo-main`
- `/tools/linkinator-main`
- `/tools/stackposts`

Purpose:
AI content, image/video/UGC/ad generation, SEO, search/AI discovery, social media management, trend intelligence, competitor intelligence, blogging, local marketing, campaign planning, ads drafting, campaign management, media optimization.

## /monitor
- `/tools/66uptime`
- `/tools/phpuptime`

Purpose:
Uptime, API monitoring, performance, synthetic checks, incidents, public/private status, alerts.

## /track
- `/tools/66analytics`
- `/tools/phpanalytics`

Purpose:
Website analytics, attribution, clickstream, funnels, audience behavior, privacy-aware telemetry, replay/session intelligence, competitive intelligence where legally and technically supported.

---

# 3. CROSS-TOOL OPERATING MODEL

Every module is independently useful.

Every module must also be a reusable node in a larger system.

Examples:

`/track` detects:
- rising exit rate
- checkout hesitation
- repeated product-view behavior

→ `/market` creates:
- a new ad variant
- a retargeting creative
- a landing-page variant

→ `/link` creates:
- a campaign-specific short link
- QR code
- smart routing

→ `/confirm` adds:
- social proof
- coupon
- review recovery
- booking or lead form

→ `/track` measures:
- conversion
- engagement
- attribution

→ predictive engine updates:
- purchase propensity
- churn risk
- next-best-action probability

→ automation engine decides:
- personalize
- notify
- create offer
- create content
- pause campaign
- escalate to human

The platform should behave as a closed loop:

**COLLECT → UNDERSTAND → PREDICT → DECIDE → CREATE → DISTRIBUTE → MEASURE → LEARN → RE-PREDICT**

---

# 4. EVENT-DRIVEN ARCHITECTURE

Create a shared event bus/domain-event architecture.

Recommended namespace:

`@mamal/bus`

Events should be typed and versioned.

Examples:

- `visitor.session.started`
- `visitor.page.viewed`
- `visitor.link.clicked`
- `visitor.qr.scanned`
- `visitor.form.submitted`
- `visitor.booking.requested`
- `visitor.review.submitted`
- `visitor.purchase.started`
- `visitor.purchase.completed`
- `visitor.checkout.abandoned`
- `campaign.created`
- `campaign.launched`
- `campaign.impression.recorded`
- `campaign.click.recorded`
- `ad.created`
- `ad.variant.generated`
- `ad.performance.changed`
- `keyword.research.completed`
- `audit.completed`
- `monitor.incident.created`
- `monitor.incident.resolved`
- `review.requested`
- `lead.created`
- `coupon.claimed`
- `loyalty.visit.recorded`
- `automation.started`
- `automation.completed`
- `prediction.updated`
- `model.training.completed`

All downstream consumers must be able to subscribe asynchronously.

Use idempotency keys and retry-safe handlers.

---

# 5. UNIFIED DATA FOUNDATION

Use a relational system of record plus analytical storage.

Preferred architecture:

- PostgreSQL 17 for transactional/domain data
- ClickHouse for high-volume analytical telemetry
- object storage for files/assets/raw payloads
- Redis for queues, locks, rate limits, ephemeral state and caching
- a typed job/worker system for long-running operations

Every domain object must have:
- id
- tenant/workspace id
- createdAt
- updatedAt
- source/provider
- provenance metadata where applicable

Major domains:

Tenant  
User  
Role  
Permission  
Team  
Project  
Brand  
Website  
Domain  
Campaign  
Audience  
Visitor  
Session  
Event  
Lead  
Customer  
Order/Conversion  
Link  
QRCode  
BioPage  
Transfer  
Asset  
Form  
Review  
Coupon  
LoyaltyProgram  
Booking  
Monitor  
Incident  
ResearchSession  
ResearchTask  
KeywordDataset  
SERPDataset  
TechnicalDataset  
BacklinkDataset  
CompetitorDataset  
Prediction  
FeatureSnapshot  
ModelVersion  
Automation  
AutomationRun  
Provider  
ProviderConnection  
ProviderPricing  
CreditLedger  
Subscription  
Plan  
Invoice  
UsageRecord  
ApiRequest  
ApiResponse  
Notification  
Webhook  
AuditLog  
Export  
Report

Do not store all research results in one giant JSON document. Keep normalized domain records plus raw/provider payloads.

---

# 6. TENANCY, AUTHORIZATION AND SECURITY

Support SaaS multi-tenancy.

Enforce tenant isolation at application and database boundaries.

Roles should be extensible, with at minimum:
- Super Admin
- Admin
- Member/User
- Analyst
- Content/Marketing
- Developer
- Viewer
- Support
- custom roles

Permissions must be granular and resource-aware.

Implement:
- secure sessions
- passkeys
- MFA/2FA
- OAuth/social login toggles
- Google
- Apple
- GitHub
- X
- Meta
- session/device management
- login history
- audit logs
- API token management
- webhook signing
- rate limiting
- CSRF/appropriate request protections
- secret rotation
- encryption at rest where appropriate
- encryption in transit
- least privilege
- tenant-aware authorization on every server operation

Never expose secrets in:
- localStorage
- source code
- client bundles
- browser logs
- analytics payloads
- raw task logs
- exports
- screenshots
- AI context

---

# 7. PREDICTIVE HUMAN-BEHAVIOR INTELLIGENCE

Build a dedicated intelligence engine.

The engine must ingest historical and real-time signals and produce decision-support predictions.

Core behavioral vectors should include:

1. purchase propensity
2. checkout intent
3. churn risk
4. engagement probability
5. content receptivity
6. offer/coupon receptivity
7. conversion likelihood
8. attention/scroll-stop likelihood
9. campaign response probability
10. next-best-action likelihood
11. price/discount sensitivity where defensible
12. repeat-purchase probability
13. lead qualification probability
14. booking probability
15. review/feedback probability

Do not claim to read or know a person's mind. Predictions are probabilistic and evidence-based.

Use:
- event history
- session behavior
- click paths
- dwell/engagement signals
- transaction history
- campaign exposure
- content interaction
- lead/form events
- review/feedback
- aggregated audience properties
- temporal patterns
- context and device signals when legitimately collected

Do not infer sensitive personal attributes or protected-class traits from behavioral data.

Do not use predictions for high-impact decisions such as employment, credit eligibility, housing, insurance, healthcare, or similar regulated/high-impact decisions.

Provide:
- confidence
- evidence
- model version
- timestamp
- feature freshness
- limitations
- explanation
- opt-out/consent controls where applicable

Use aggregated/anonymous data by default whenever individual-level identity is not required.

Support retention policies, deletion, export, consent, and privacy controls.

---

# 8. PREDICTIVE ML PIPELINE

Pipeline:

**DATA INGESTION  
→ CLEANING / VALIDATION  
→ FEATURE ENGINEERING  
→ BEHAVIORAL STATE  
→ MODEL ENSEMBLE  
→ SCORE  
→ EXPLANATION  
→ ACTION  
→ OUTCOME  
→ FEEDBACK  
→ RETRAIN/EVALUATE**

Recommended concepts:

### BehavioralState
- recency
- frequency
- monetary/value signals where applicable
- engagement decay
- attention profile
- funnel stage
- interaction vector
- source/campaign
- intent state

### IntentVector
- browsing intent
- research intent
- purchase intent
- retention risk
- conversion friction
- content affinity

Use appropriate ML models for each task instead of forcing a single LLM to do all prediction.

Possible stack:
- gradient boosting / XGBoost-class models
- logistic regression
- calibrated classifiers
- time-series models
- neural networks where justified
- anomaly detection
- embeddings/vector retrieval
- LLMs for interpretation, synthesis, and agentic orchestration

LLMs must not replace deterministic statistical calculations when exact numeric prediction is required.

Track:
- precision
- recall
- calibration
- ROC-AUC/PR-AUC where appropriate
- lift
- false positives
- false negatives
- drift
- data leakage
- feature stability
- model performance by cohort where lawful and appropriate

Never deploy an unvalidated model as if it were production truth.

---

# 9. DEFAULT AI PROVIDER STRATEGY

Create a provider registry and adapter system.

Provider abstraction should permit instant configuration changes without changing product code.

Supported providers requested by the source specification include:

- OpenAI
- Claude / Anthropic
- DeepSeek
- Gemini
- Grok
- Kimi
- GLM
- Meta
- MiniMax
- ElevenLabs
- ByteDance
- Qwen
- Audo
- image/video/music providers as required
- DataForSEO for search/SEO data

Requested defaults:
- OpenAI Astra for predictive analytics where the selected/current provider supports the required workload
- Claude for automation orchestration/deep synthesis
- DeepSeek Pro/R1 for reasoning
- Seedance 2.5 for video
- Nano Banana/NanoBanana for images
- ElevenLabs for audio/voice
- DataForSEO for SEO/ranking/search intelligence

Treat model names as configuration, not hard-coded truth. Validate availability and current APIs before activation.

Every provider must have:
- id
- display name
- capability list
- model registry
- pricing
- limits
- auth method
- health
- status
- default roles
- fallback roles

Support:
- provider priority
- automatic failover
- manual override
- per-feature default
- workspace override
- user override where allowed

---

# 10. AI MASTER TOGGLE AND GRANULAR CONTROLS

Every AI feature can be:
- enabled
- disabled
- limited
- provider-specific
- model-specific

There must be:
- master AI enable/disable
- per-tool toggle
- per-feature toggle
- per-provider toggle
- per-user/workspace entitlement

When AI is off:
- the platform must continue functioning wherever deterministic workflows can operate.

Show users clearly when a result is:
- deterministic
- provider-generated
- inferred
- predicted
- cached
- partially complete

---

# 11. AI AGENT ARCHITECTURE

Create a controlled tool-using agent architecture.

Never give an agent unrestricted HTTP/network access.

All agent tools go through typed application services and the endpoint registry.

Example typed tools:

`researchKeyword()`  
`analyzeSERP()`  
`inspectWebsite()`  
`inspectTechnicalSEO()`  
`analyzeBacklinks()`  
`analyzeCompetitors()`  
`inspectAnalytics()`  
`inspectCampaignPerformance()`  
`createContentBrief()`  
`generateAdCreative()`  
`createShortLink()`  
`generateQRCode()`  
`createReviewCampaign()`  
`createCoupon()`  
`createBookingPage()`  
`createAutomation()`  
`runPrediction()`  
`exportReport()`

Before expensive actions the agent must:
1. inspect available data
2. inspect cache
3. determine missing evidence
4. estimate cost
5. check entitlements
6. produce an execution plan
7. request confirmation when required by budget/risk policy
8. execute
9. show tool activity
10. store provenance

The user must be able to stop/cancel the agent.

---

# 12. AUTOMATION ENGINE

Create:
1. one-click templates
2. three-click templates
3. visual canvas builder
4. event-triggered workflows
5. scheduled workflows
6. conditional branches
7. approval steps
8. retries
9. fallbacks
10. human escalation
11. execution history
12. replay/debugging
13. dry-run/simulation

Visual automation nodes should include:
- trigger
- data query
- prediction
- condition
- AI generation
- audience selection
- content creation
- link creation
- QR generation
- social publishing
- ad draft
- campaign action
- email
- push
- webhook
- notification
- human approval
- wait/delay
- retry
- branch
- merge
- export

Examples:
- High purchase propensity → offer coupon → send link → measure conversion
- Churn score above threshold → personalized retention content → human escalation
- New blog opportunity → research keywords → generate brief → write article → create images → optimize SEO → publish
- Negative feedback → private recovery workflow instead of public-review routing
- Website issue → audit → create remediation checklist → monitor fix → verify
- Campaign underperforming → predict decline → create creative variants → pause pending approval

---

# 13. /AUDIT

Purpose:
Find website issues affecting SEO/search visibility and AI visibility and explain how to fix them.

Combine 66Audit + PHPRank functionality.

Capabilities:
- site crawl
- technical SEO
- indexability
- metadata
- headings
- duplicate detection
- canonical analysis
- robots
- sitemap
- links
- broken links
- redirect chains
- performance metrics where available
- ranking data
- keyword opportunities
- competitors
- backlink context
- AI/search visibility
- prioritized remediation
- action plans
- re-crawl verification

Every issue:
- severity
- evidence
- affected URL
- source
- recommendation
- expected impact
- related tasks
- status
- verification

---

# 14. /CONFIRM

Purpose:
Increase trust, conversion, reviews, local engagement, and social proof.

Combine:
- 66SocialProof
- 66Pusher
- FOMO-like interaction patterns
- LocalBoostAI concepts

Include:

### Review Booster
Route happy customers toward appropriate public review channels while routing low-score feedback into private recovery.

### Coupons
- offers
- customer claims
- unique coupon codes
- redemption tracking
- campaign attribution
- expiration
- usage limits

### Loyalty Cards
- QR stamp cards
- visit tracking
- reward unlocks
- staff redemption

### Feedback Forms
- private feedback
- issue routing
- follow-up workflows
- sentiment analysis

### Lead Forms
- quote requests
- callbacks
- waitlists
- consultation requests
- campaign/QR/link attribution

### Booking Pages
- booking landing pages
- appointment requests
- availability
- staff/service mapping
- reminders
- follow-up

Build embeddable widgets with a visual editor.

---

# 15. /LINK

Create one intelligent link platform.

Combine:
- URL shortening
- custom slugs
- dynamic QR codes
- bio links
- mini landing pages
- file links
- file transfer
- campaign tracking
- UTM
- link analytics
- deep links
- destination routing
- expiration
- password access
- download tracking
- device/location routing where lawful
- A/B destination testing where appropriate

A short link should be able to resolve to:
- website
- file
- QR landing page
- bio page
- form
- booking page
- campaign
- social destination
- downloadable asset

For files:
- object storage
- temporary/signed URLs
- access control
- resumable uploads
- virus/malware scanning where appropriate
- expiration
- download logs
- large-file support

---

# 16. /MARKET

This is the broadest execution suite.

## AI CREATIVE STUDIO

Include:
- image generation
- image editing
- video generation
- video editing
- image-to-video
- text-to-video
- avatars
- UGC generation
- product photography
- fashion studio
- voiceover
- dubbing
- captions
- AI music/audio
- background/object removal
- generative fill
- relighting
- style transfer
- reimagine
- smart image editing

## CONTENT

Include:
- AI writing
- blog/article generation
- content briefs
- SEO writing
- rewriting
- humanizer
- editing
- brand voice
- prompt library
- document generation
- content manager

## AUTO BLOGGING

Support:
- Shopify
- WooCommerce
- WordPress
- Ghost

Pipeline:

**trend discovery → research → keywords → brief → article → images → internal links → metadata → review → schedule/publish**

## SOCIAL MEDIA

Support appropriate platforms:
- Facebook
- Instagram
- Threads
- TikTok
- YouTube
- YouTube Shorts
- X/Twitter

Also support:
- calendar
- multi-account
- scheduling
- carousel
- stories where supported
- AI captions
- comments
- mentions
- DM/comment agent
- brand monitoring
- influencer discovery
- analytics
- approval workflow

## ADS

Support drafting/management integrations where APIs and account permissions permit:
- Google
- Meta
- TikTok
- X
- YouTube
- Pinterest
- LinkedIn
- Amazon

Features:
- campaign drafts
- ad creative
- copy
- audience ideas
- budgets
- variants
- performance
- AI analysis
- competitor intelligence
- experiment/test workflow
- approval
- client reporting

Never directly spend money or launch campaigns without explicit authorization and appropriate permissions.

## SEO / AI SEARCH

Build:
- keyword research
- SERP
- technical SEO
- backlinks
- competitors
- content gaps
- AI-search visibility
- LLM citation/mention tracking where data/API support exists
- Google AI Mode where supported
- prompt-level visibility
- recommendation opportunities

---

# 17. /MONITOR

Combine 66Uptime + PHPUptime concepts.

Support:
- website checks
- APIs
- DNS
- SSL
- latency
- performance
- availability
- synthetic browser flows
- private/internal checks
- incident management
- public status pages
- alerts
- maintenance windows
- escalations
- anomaly detection

Include:
- checks
- incidents
- timeline
- status
- uptime percentage
- response time
- regions
- alert rules
- notification channels

---

# 18. /TRACK

Combine 66Analytics + PHPAnalytics concepts.

Provide:
- traffic
- acquisition
- UTM
- referrers
- pages
- funnels
- sessions
- clickstream
- campaign attribution
- events
- conversions
- audience segments
- cohorts
- retention
- behavior paths
- privacy-aware analytics
- session intelligence/replays where consent/legal requirements are met
- trend analysis
- competitor intelligence where legally supported

Track should feed the predictive engine.

---

# 19. RESEARCH PRO MODE

Create an expert research environment inside `/market` or `/track` as appropriate, with shared infrastructure.

Core loop:

**QUESTION → UNDERSTANDING → PLAN → COST → EXECUTION → DATA → INSIGHT → ACTION → SAVE → REVISIT**

The experience must not resemble a generic SaaS dashboard.

Primary launcher:

**"What are we researching?"**

Modes:
- Keyword
- Website
- Content
- Competitor

Simple vs Advanced.

Advanced mode reveals endpoint planning.

---

# 20. RESEARCH EXECUTION MODEL

ResearchSession contains:
- query
- normalized query
- type
- location
- language
- configuration
- execution mode
- status
- estimated cost
- actual cost
- tasks
- datasets
- insights
- raw responses
- execution log
- createdAt
- updatedAt

ResearchTask contains:
- endpoint
- module
- status
- request
- raw response
- normalized response
- cost
- duration
- timestamps
- retry count
- cache status
- error

States:
- queued
- planning
- estimating
- ready
- running
- success
- partial
- failed
- cancelled
- cached

Do not show only “Loading…”.

Show meaningful progress:

✓ Keyword overview  
✓ Search intent  
→ Historical volume  
○ SERP  
○ Competitors

---

# 21. DATAFORSEO INTEGRATION

Create:
- DataForSeoClient
- DataForSeoRequest
- DataForSeoResponse
- DataForSeoError
- DataForSeoEndpoint
- EndpointRegistry

Metadata:
- id
- name
- category
- method
- path
- input schema
- output schema
- cost metadata
- batching support
- polling support
- cacheability
- default status
- simple/advanced availability
- documentation link

Never scatter endpoints across components.

Use typed adapters and server-side credentials.

Implement as relevant:
- Google Keyword Overview
- Google Related Keywords
- Google Search Intent
- Google Bulk Keyword Difficulty
- Google Historical Search Volume
- Google Ranked Keywords
- Google SERP
- OnPage
- backlinks
- referring domains
- competitor analysis
- keyword opportunities

Never fabricate provider fields.

Keep provider payloads and normalized domain models separate.

---

# 22. KEYWORD RESEARCH EXPERIENCE

Top-level:
- research context
- executive summary
- core metrics
- search demand
- search intent
- difficulty
- opportunity map
- SERP landscape
- competitor gap
- related keywords
- AI analysis
- raw data

Every metric is drillable.

Tables support:
- search
- filter
- sort
- pagination/virtualization
- column visibility
- saved views
- row expansion
- bulk select
- export
- density

Bulk actions:
- analyze
- cluster
- content brief
- export
- add to strategy

---

# 23. WEBSITE RESEARCH

Support:
- canonical URL normalization
- crawl
- page analysis
- technical SEO
- ranking
- competitors
- backlinks
- opportunities

Dashboard:
- site context
- health
- issues
- performance
- indexability
- top pages
- keywords
- competitors
- backlinks
- content opportunities
- AI action plan
- raw data

Every issue has:
- severity
- evidence
- URL
- explanation
- recommended action

---

# 24. BACKLINK RESEARCH

Views:
- summary
- history
- timeseries
- backlinks
- referring domains
- competitors

Metrics:
- total backlinks
- referring domains
- new
- lost
- linked pages
- anchor text

Never mislabel third-party metrics as another vendor’s proprietary score.

---

# 25. RESEARCH AI ASSISTANT

Do not build a generic chatbot.

Build a contextual Research Assistant.

It must be bound to:
- selected session
- current filters
- current rows
- current module
- available evidence

Example actions:
- find opportunities
- summarize
- explain
- compare competitors
- cluster keywords
- create content brief
- generate action plan
- explain technical issues

Structured response:
- finding
- summary
- reason
- confidence
- supporting data
- source records
- action

No fabricated metrics.

---

# 26. /SO WHAT?/ INSIGHT SYSTEM

Every important experience should have:

**DATA**  
What happened?

**INSIGHT**  
What does it mean?

**ACTION**  
What should I do?

Example:

Search volume: 12,400  
Difficulty: 38

↓

High demand relative to competitive difficulty.

↓

Build a focused comparison page.

This three-layer system is a core product differentiator.

---

# 27. UNIFIED HOME / COMMAND CENTER

The platform home should answer:

**What is happening?  
What matters?  
What should I do?  
What can I automate?**

Show:
- connected properties/brands
- active campaigns
- incidents
- top opportunities
- predictive scores
- recent research
- automation runs
- leads
- conversions
- revenue signals
- audience shifts
- campaign performance
- AI recommendations

Do not make the home page a wall of cards.

Use information hierarchy and activity-based intelligence.

---

# 28. DUAL-TIER SIDEBAR

Use a dual-tier navigation model.

Primary:
- Home
- Research
- Audit
- Confirm
- Link
- Market
- Monitor
- Track
- Automations
- Intelligence

Secondary contextual navigation changes based on selected tool.

Include:
- search
- command palette
- recent
- favorites
- pinned workspaces

Command palette:
`Cmd/Ctrl + K`

Commands:
- New Research
- New Campaign
- Open History
- Search Keywords
- Search Websites
- Create Link
- Create QR
- Generate Ad
- Run Audit
- View Monitor
- View Analytics
- Open Connections
- Run Automation
- Export

---

# 29. DESIGN SYSTEM

Use:
- Next.js App Router
- React
- TypeScript
- Tailwind CSS
- shadcn/ui
- Radix UI
- Lucide
- Zustand
- TanStack Query
- TanStack Table
- Zod

Visual direction:
- premium
- dark/light
- technical
- calm
- precise
- data-dense but readable
- subtle borders
- subtle surface hierarchy
- restrained color
- professional charts
- excellent typography
- minimal decorative gradients
- no generic template feel

Do not copy reference branding. Learn from the interaction and information architecture.

Use token-driven theme values, including accessible contrast and dark/light states.

---

# 30. COMPONENT LIBRARY

Create reusable primitives:

AppShell  
Sidebar  
TopBar  
ContextHeader  
Section  
Panel  
Drawer  
Modal  
SplitPane  
Tabs  
DataTable  
ColumnManager  
FilterBuilder  
MetricCard  
TrendMetric  
Sparkline  
Chart  
DataSourceBadge  
StatusBadge  
SeverityBadge  
ResearchLauncher  
ResearchPlanner  
ExecutionTimeline  
TaskCard  
ProgressIndicator  
CostEstimator  
KeywordMetric  
OpportunityCard  
CompetitorRow  
AuditIssue  
ResearchAssistant  
InsightCard  
EvidencePanel  
ToolActivity  
RecommendationCard  
RawJsonViewer  
RequestInspector  
ResponseInspector  
EndpointExplorer  
ApiConsole  
AutomationCanvas  
Node  
RunHistory  
NotificationCenter  
CommandPalette  
GlobalSearch  
FileManager  
MediaLibrary

---

# 31. MOTION

Motion must communicate state.

Default durations:
- micro: 100–150ms
- control: 150–200ms
- drawer: 200–300ms
- major transition: 250–400ms

Use:
- opacity
- slight translate
- slight scale
- height transitions
- progressive reveal

Do not use decorative continuous animation.

Refreshing:
- rotate only while actively executing

Copy:
Copy → Check → return

Save:
small confirmation state

---

# 32. RESPONSIVE / MOBILE / PWA

Every screen must work down to the smallest practical width.

Desktop-first but not desktop-only.

At smaller widths:
- collapse sidebar
- use drawers
- prioritize critical metrics
- convert large tables to prioritized cards
- allow horizontal scrolling only when genuinely necessary
- preserve search/filter access
- preserve primary actions

PWA:
- installable
- offline shell
- cached safe assets
- notification support where permitted
- background sync where appropriate
- update prompts
- resilient connectivity state
- mobile-friendly file upload
- touch targets
- safe-area support

---

# 33. ACCESSIBILITY

Support:
- semantic HTML
- keyboard navigation
- focus visibility
- focus management
- ARIA labels
- screen-reader compatibility
- reduced motion
- sufficient contrast
- accessible tables
- accessible charts/data alternatives
- form errors
- validation
- descriptive tooltips
- non-color-only status communication

Target WCAG 2.2 AA where practical.

---

# 34. EMPTY, LOADING AND ERROR STATES

Empty states must teach.

Example:

> No research yet.  
> Start with a keyword or website.

Errors must be actionable.

Bad:
`API Error 401.`

Good:

> DataForSEO authentication failed.  
> Your connection could not be authenticated.  
> [Open Connections]

When one task fails:
- preserve successful tasks
- show partial completion
- allow retry
- allow continue without failed module

---

# 35. COST + CREDITS + BILLING

Support both:
1. subscriptions
2. credits

Subscription:
- grants tool access
- includes credits
- can be tool-specific
- can be unified

Credits:
- usable across tools/features
- purchased separately
- replenishable
- ledger-backed

Pricing:
- per-tool plans
- unified plans
- multiple editable tiers
- lifetime pricing option
- lifetime plans exclude AI features/tools

Admin must be able to:
- create
- edit
- duplicate
- archive
- disable
- delete
- adjust prices
- adjust included credits
- adjust limits
- map features
- set provider margin policies

---

# 36. REAL-TIME COST METERING

Track:
- provider
- model
- endpoint
- request units
- input/output usage
- credits
- provider cost
- platform margin
- user charge
- workspace cost
- tool cost
- date/time

Requested default margin:
**25%**

Implement configurable margin rules rather than hard-code 25% everywhere.

If provider pricing changes:
- pricing registry updates
- future jobs use current pricing
- historical costs remain immutable
- show price-source and effective date

Never claim estimated cost is exact when it is not.

---

# 37. API COST REGISTRY

Each provider has:
- models
- endpoints
- unit type
- price
- effectiveFrom
- effectiveTo
- source
- confidence
- lastChecked

Provide admin UI:
- provider
- model
- unit
- cost
- margin
- effective date
- refresh
- test
- disable

---

# 38. PLUGIN ECOSYSTEM

Create a plugin framework for:
- affiliate
- email signature
- image optimizer
- newsletters
- push notifications
- PWA
- teams
- CRM
- calendar
- payments
- external chat
- support/helpdesk
- storage
- import/export

Each plugin should declare:
- id
- name
- version
- capabilities
- routes
- permissions
- settings
- dependencies
- pricing
- hooks
- events
- uninstall/migration behavior

---

# 39. ONBOARDING

Free onboarding must cost the platform as little as possible.

Goal:
**first meaningful value in under 60 seconds.**

Flow:

**SIGN UP → ONBOARD → CONNECT/IMPORT → CHOOSE GOAL → ADD BRAND/WEBSITE → GET FIRST INSIGHT → RECOMMENDED ACTION → CROSS-TOOL DISCOVERY**

Offer an onboarding checklist.

Examples:
- Add first website
- Run free audit
- Create first link
- Install tracking
- Create review campaign
- Generate first asset
- Build first automation

---

# 40. WORKSPACE / BRAND MODEL

A user may manage:
- companies
- brands
- websites
- personal brands
- campaigns
- teams

Each workspace should have:
- brand profile
- logo
- colors
- domains
- social profiles
- target audience
- positioning
- tone/voice
- business goals
- connected accounts
- consent/privacy configuration

AI features may use this context only according to workspace permissions.

---

# 41. REPORTING

Exports:
- CSV
- JSON
- Markdown
- PDF

PDF reports should be designed as reports, not screenshots.

Include:
- research context
- executive summary
- metrics
- charts
- opportunities
- issues
- competitors
- backlinks
- AI recommendations
- sources
- dates
- methodology
- limitations

Client-friendly reports should hide technical complexity by default, while developer/raw mode remains available.

---

# 42. DATA PROVENANCE

Every material insight should be able to answer:
- what data supports this?
- where did it come from?
- when was it collected?
- which provider/endpoint produced it?
- was it cached?
- was it inferred?
- was AI involved?
- which model/version?
- what confidence/limitations apply?

Use:
`DataSourceBadge`

Example:

> DataForSEO  
> Updated 2m ago

Details:
- Provider
- Endpoint
- Collected
- Cached

---

# 43. RAW DATA / DEVELOPER MODE

Every research module must expose:

**Overview | Data | Raw**

Raw:
- request
- endpoint
- response
- timing
- cost
- status
- headers metadata where safe

Actions:
- Copy
- Download
- Search
- Pretty
- Compact
- Rerun
- Inspect
- compare responses

API Console:
- select endpoint
- fill payload
- validate against schema
- execute
- inspect raw response

Do not expose secrets.

---

# 44. SEARCH HISTORY / LOCAL-FIRST EXPERIENCE

Persist:
- research sessions
- queries
- locations
- normalized data
- raw data where safe
- AI insights
- execution logs
- costs
- UI preferences

History:
- search
- favorite
- rename
- duplicate
- delete
- export
- reopen

When matching cached research exists:

> Existing research found.  
> Updated X minutes ago.  
> [Use Existing] [Refresh]

---

# 45. RESEARCH NAVIGATION

Persistent session tabs:

Overview  
Keywords  
SERP  
Competitors  
Technical SEO  
Backlinks  
AI Research  
Execution  
Raw Data  
Export

Keep filters when navigating.

Use sticky context:

> project management software  
> Overview | Keywords | SERP | Competitors  
> Volume 33K · Difficulty 68 · 1,428 keywords  
> [Export]

---

# 46. PREDICTIVE + EXECUTION UX

Surface predictive recommendations as actions, not merely numbers.

Example:

> Purchase intent 82%

Evidence:
- high-return frequency
- repeated product views
- checkout revisits
- offer engagement

Recommended:
Create limited-time retention/upsell sequence.

Actions:
[Simulate] [Create Automation] [Create Offer] [Dismiss]

Always distinguish model score from observed fact.

---

# 47. CRM AND CUSTOMER LOOP

Add CRM capabilities sufficient for:
- contacts
- leads
- pipeline
- activities
- notes
- segments
- lifecycle stage
- source/attribution
- predictive score
- consent
- interaction timeline

Connect CRM with:
- /confirm
- /link
- /track
- /market
- automations

---

# 48. COMMUNICATIONS

Support:
- email
- push
- in-app notifications
- webhook
- SMS/voice where appropriate via integrations

Notification center:
- grouped
- non-invasive
- actionable
- user-configurable

Batch related events to avoid spam.

---

# 49. FILE / MEDIA INFRASTRUCTURE

Provide a shared media library.

Every asset can have:
- source
- type
- size
- dimensions
- duration
- tags
- project/campaign links
- usage rights metadata
- storage location
- generated/uploaded origin

Support:
- large files
- chunked upload
- resumable upload
- preview
- versioning
- replacement
- deletion
- permissioned sharing

---

# 50. TEAM COLLABORATION

Add:
- teams
- roles
- invitations
- comments
- mentions
- approvals
- tasks
- activity log
- ownership
- client collaboration
- shared assets
- shared reports

---

# 51. CHAT / HUMAN + AI SUPPORT

Provide contextual communication across:
- user
- team
- support
- AI assistant
- human agent

Allow:
- file attachments
- large file storage
- speech-to-text
- text-to-speech
- voice
- private notes
- canned responses
- escalation
- export
- conversation summary
- unread notifications

Use ElevenLabs/OpenAI/Google where configured.

---

# 52. MODEL / MEDIA CAPABILITY MATRIX

Build an admin-maintained capability matrix.

Categories:
- reasoning
- writing
- research
- search
- image generation
- image editing
- video generation
- video editing
- avatar
- voice
- TTS
- STT
- music
- embeddings
- OCR/vision
- moderation

Route by:
- quality
- latency
- cost
- availability
- user policy
- subscription entitlement

---

# 53. OBSERVABILITY

Implement:
- structured logs
- tracing
- metrics
- job metrics
- provider health
- API latency
- error rates
- queue depth
- database health
- cache hit ratio
- model costs
- automation success/failure
- prediction drift
- uptime

Provide internal admin observability screens.

---

# 54. TESTING

For every module:
- unit tests
- integration tests
- API contract tests
- schema validation tests
- authorization tests
- tenant isolation tests
- provider adapter tests
- workflow tests
- job retry tests
- UI/component tests
- end-to-end tests
- mobile/responsive tests
- accessibility tests

Add test fixtures and deterministic mocks for external providers.

Do not call expensive live APIs in normal CI.

---

# 55. DEVELOPMENT LOOP

Do not implement everything in one pass.

Before code:
1. architecture diagram
2. directory tree
3. database schema
4. domain model
5. service boundaries
6. provider abstraction
7. endpoint registry
8. workflow engine
9. state management
10. route map
11. component hierarchy
12. implementation plan

After each phase:
1. type check
2. lint
3. unit tests
4. integration tests
5. route validation
6. UI inspection
7. accessibility check
8. responsive check
9. state-management check
10. security review
11. regression review
12. document implementation

Then continue.

---

# 56. RECOMMENDED BUILD ORDER

PHASE 1 — Repository audit and architecture map.

PHASE 2 — Foundation, shell, design system, auth, tenancy.

PHASE 3 — Provider registry, secrets, API gateway, usage/cost registry.

PHASE 4 — Shared event bus, jobs, notifications, storage.

PHASE 5 — `/track` foundation and event collection.

PHASE 6 — `/audit`.

PHASE 7 — `/link`.

PHASE 8 — `/confirm`.

PHASE 9 — `/monitor`.

PHASE 10 — `/market` core AI/content/creative.

PHASE 11 — SEO/research/AI-search intelligence.

PHASE 12 — Predictive intelligence.

PHASE 13 — Automation canvas.

PHASE 14 — Billing/credits/pricing.

PHASE 15 — CRM/teams/collaboration/support.

PHASE 16 — PWA/mobile.

PHASE 17 — Observability/security/performance.

PHASE 18 — End-to-end hardening and launch.

Never move to the next phase while the current phase is obviously broken.

---

# 57. UI/UX RESEARCH STANDARD

Before implementation of each major tool:
1. produce UX map
2. screen inventory
3. component inventory
4. interaction map
5. motion map
6. state map
7. responsive map
8. accessibility checklist
9. permission map
10. API/data map

Then build.

---

# 58. FINAL PRODUCT CHARACTER

The finished platform must feel:

calm  
fast  
intelligent  
trustworthy  
technical  
premium  
powerful  
transparent  
coherent  
actionable

The user should feel:

> "I am not looking at six dashboards. I am operating an intelligence system that understands my business, tells me what is happening, predicts what may happen next, and helps me do something about it."

---

# 59. REFERENCE APPLICATIONS AND LINKS

## Core repository / notes

https://github.com/assetjockey/mamal  
https://github.com/assetjockey/mamal/tree/main/notes  
https://drive.google.com/drive/folders/17EnXURZyBaEkNW1mlk80o_XdqfCFCn_G?usp=sharing

## AUDIT / RANK

https://66audit.com/  
https://66audit.com/changelog  
https://66audit.com/demo/  
https://lunatio.com/phprank/  
https://lunatio.com/phprank/changelog  
https://phprank.lunatio.com/

## CONFIRM / SOCIAL PROOF / LOCAL

https://66socialproof.com/  
https://66socialproof.com/changelog  
https://66socialproof.com/demo/  
https://usefomo.com/  
https://66pusher.com/  
https://66pusher.com/changelog  
https://66pusher.com/demo/  
https://demo.stackposts.com/localboostai/  
https://demo.stackposts.com/localboostai/login  
https://demo.stackposts.com/localboostai/qr/admin-faker-booking-spa

## LINK / QR / BIO / TRANSFER

https://66biolinks.com/  
https://66biolinks.com/changelog  
https://66biolinks.com/demo  
https://codecanyon.net/item/linkqr-ai-bio-links-dynamic-qr-codes-short-links-campaign-analytics-saas/63265472  
https://demo.stackposts.com/linkqr/  
https://demo.stackposts.com/linkqr/portal/dashboard  
https://lunatio.com/phpshort/  
https://lunatio.com/phpshort/changelog  
https://phpshort.lunatio.com/  
https://66qrcode.com/  
https://66qrcode.com/changelog  
https://66qrcode.com/demo  
https://66transfer.com/  
https://66transfer.com/changelog  
https://66transfer.com/demo  
https://lunatio.com/phptransfer/  
https://lunatio.com/phptransfer/changelog  
https://phptransfer.lunatio.com/  
https://codecanyon.net/item/swipgle-easy-file-transfer-saas/31169348  
https://demo.vironeer.com/swipgle  
https://demo.vironeer.com/swipgle/admin  
https://docs.vironeer.com/swipgle/index.html  
https://codecanyon.net/item/droppy-online-file-sharing/10575317  
https://codecanyon.net/item/amazon-s3-droppy-online-file-sharing/12442659  
https://codecanyon.net/item/droppy-premium-subscription/13556620  
https://codecanyon.net/item/ftp-droppy-online-file-sharing/17702419

## MARKET / MAGICAI / AD GENERATION

https://icon.com/  
https://codecanyon.net/item/magicads-ai-ad-generation-allinone-saas-platform-image-video-ad-copy-generator/63822076  
https://magicads.berkine.dev/  
https://magicads.berkine.dev/app/user/ad-analytics  
https://magicads.berkine.dev/app/user/dashboard  
https://magicads.berkine.dev/app/user/brands  
https://magicads.berkine.dev/app/user/studio/gallery  
https://magicads.berkine.dev/app/user/copy/library  
https://magicads.berkine.dev/app/user/copy  
https://magicads.berkine.dev/app/user/studio/images  
https://magicads.berkine.dev/app/user/studio/videos  
https://magicads.berkine.dev/app/user/fashion-studio  
https://magicads.berkine.dev/app/user/product-photoshoot  
https://magicads.berkine.dev/app/user/avatar-studio  
https://magicads.berkine.dev/app/user/ugc-factory  
https://magicads.berkine.dev/app/user/video-agent  
https://magicads.berkine.dev/app/user/image-agent  
https://magicads.berkine.dev/app/user/image-editor  
https://magicads.berkine.dev/app/user/social-media-studio  
https://magicads.berkine.dev/app/user/channel-broadcast  
https://magicads.berkine.dev/app/user/projects  
https://magicads.berkine.dev/app/user/team  
https://magicads.berkine.dev/app/user/marketplace  
https://magicads.berkine.dev/app/user/support  
https://codecanyon.net/item/magicai-openai-content-text-image-chat-code-generator-as-saas/45408109  
https://demo.magicproject.ai/login  
https://demo.magicproject.ai/  
https://new.magicproject.ai/  
https://codecanyon.net/item/stackposts-social-marketing-tool/21747459  
https://demo.stackposts.com/stackposts/  
https://demo.stackposts.com/stackposts/admin/dashboard

## SEO / CRAWL / LINK ANALYSIS / TRENDS / COMMERCE INTELLIGENCE

https://openseo.so/  
https://github.com/every-app/open-seo  
https://github.com/crawlseo/crawlseo  
https://github.com/JustinBeckwith/linkinator  
https://trendproof.dev/  
https://github.com/akvise/trends-checker  
https://shophunter.io/  
https://www.shopscan.app/tool/shopify-store-revenue-checker  
https://www.zikanalytics.com/shopify/sales-tracker  
https://winninghunter.com/  
https://winninghunter.com/shopify-store-revenue-tracker  
https://brandsearch.co/

## MONITOR

https://66uptime.com/  
https://66uptime.com/changelog  
https://66uptime.com/demo  
https://lunatio.com/phpuptime/  
https://lunatio.com/phpuptime/changelog  
https://phpuptime.lunatio.com/

## TRACK / ANALYTICS

https://66analytics.com/  
https://66analytics.com/changelog  
https://66analytics.com/demo/  
https://lunatio.com/phpanalytics/  
https://lunatio.com/phpanalytics/changelog  
https://phpanalytics.lunatio.com/  
https://lp.similarweb.com/competitive-analysis  
https://www.stalkr.ai/  
https://www.xtrabar.com/

## DESIGN SYSTEM / DASHBOARD REFERENCES

https://thefrontkit.com/apps/kanban-pm-kit  
https://kanban-pm-kit-code.vercel.app/  
https://thefrontkit.com/apps/neuraldesk-ai-ops-dashboard  
https://neuraldesk-ai-ops-dashboard.vercel.app/  
https://neuraldesk-ai-ops-dashboard.vercel.app/dashboard  
https://thefrontkit.com/apps/email-marketing-kit  
https://email-marketing-kit-code.vercel.app/dashboard  
https://thefrontkit.com/apps/saas-metrics-kit  
https://saas-metrics-kit-code.vercel.app/dashboard  
https://thefrontkit.com/apps/sales-dashboard-kit  
https://sales-dashboard-kit-code.vercel.app/  
https://thefrontkit.com/apps/booking-kit  
https://booking-kit-code.vercel.app/bookings  
https://thefrontkit.com/apps/help-desk-kit  
https://help-desk-kit-code.vercel.app/dashboard  
https://thefrontkit.com/apps/finance-dashboard-kit  
https://finance-dashboard-kit-code.vercel.app/  
https://thefrontkit.com/apps/social-media-dashboard-kit  
https://social-media-dashboard-kit-code.vercel.app/  
https://thefrontkit.com/apps/ai-feedback-assistant  
https://ai-feedback-assistant.vercel.app/  
https://thefrontkit.com/apps/a11y-starter-kit  
https://a11y-starter-kit.vercel.app/dashboard  
https://github.com/thefrontkit/a11y-starter-kit-code  
https://thefrontkit.com/apps/crm-dashboard-kit  
https://crm-dashboard-kit-code.vercel.app/

## LANDING PAGE REFERENCES

https://stripe.com/  
https://asset.framer.ai/  
https://aoutive.framer.website/  
https://slate-template.framer.website/  
https://syncrun.framer.website/  
https://movlo.framer.website/  
https://solva-template.framer.website/  
https://nomia-app.framer.website/  
https://verseo.framer.website/  
https://flexfollio.framer.website/  
https://orbital-gr8r.framer.website/  
https://powder.framer.website/  
https://agentory.framer.website/  
https://kodamatemplate.framer.website/  
https://66biolinks.com/plugins  
https://chatgpt.com/share/6a98ea0a-252c-83e9-a021-1477b12dca49

## PREDICTIVE ANALYTICS REFERENCES

https://www.latentview.com/blog/ai-predictive-analytics-use-cases/  
https://www.leewayhertz.com/ai-for-predictive-analytics/  
https://www.vonage.com/resources/articles/predictive-ai/  
https://www.youtube.com/watch?v=yfOMJ8pKWHY&t=14  
https://www.youtube.com/watch?v=EriFD2RCYM4  
https://www.youtube.com/watch?v=U7nr7sEbkNk  
https://www.ibm.com/think/topics/predictive-ai  
https://beyondtechnology.net/blog/it-consulting/how-artificial-intelligence-is-transforming-predictive-analytics/  
https://www.youtube.com/watch?v=0K1ESOC8CXg&vl=en  
https://www.snowflake.com/en/fundamentals/predictive-ai/

---

# 60. REFERENCE DEMO ACCESS — AUTHORIZED USE ONLY

The supplied project notes contain credentials for public/demo environments.

Use them only for authorized inspection of the referenced demos.

Do not:
- store them in source code
- commit them
- expose them in the client
- copy them into logs
- include them in production configuration

Treat all credentials as secrets even when they are demo credentials.

---

# 61. CURRENT PRODUCT REFERENCE PRINCIPLES

The supplied reference material emphasizes these principles:

- predictive analytics is a pipeline, not a single chatbot
- research tools must show what the system is doing
- data must precede decoration
- everything should be drillable
- raw data must remain accessible
- filters persist across views
- one failed module should not erase successful modules
- AI claims must be tied to evidence
- costs must be visible
- users must understand data provenance
- simple workflows must be easy for novices
- expert workflows must expose lower-level controls
- the product should be a research/decision environment, not a generic dashboard

---

# 62. FINAL AUTONOMOUS EXECUTION DIRECTIVE

Now execute this specification against the actual repository.

DO NOT:
- merely describe the work
- stop after creating mock screens
- write placeholder TODOs and claim completion
- use fake data in production paths
- duplicate six separate products
- create disconnected micro-apps
- hide core workflows behind unnecessary modals
- make the user manually copy information between tools
- expose provider keys
- pretend estimates are actuals
- pretend AI predictions are facts
- invent unsupported provider metrics
- compromise security for convenience

DO:
- inspect first
- map the current system
- preserve working functionality
- consolidate duplicated capabilities
- create shared primitives
- create shared services
- create shared event infrastructure
- create shared provider abstractions
- connect all tools through normalized domain events
- create a coherent navigation model
- make cross-tool handoffs first-class
- build cost and credit awareness into execution
- make AI provider switching easy
- make workflows composable
- build real error and partial-success states
- build real loading/progress states
- build real mobile/PWA behavior
- add accessibility
- test every critical path
- inspect the UI after implementation
- fix visual and functional regressions
- document architecture and operational behavior

---

# 63. COMPLETION GATES

Do not declare the project complete until all applicable gates pass.

## FUNCTIONAL
- every route works
- every major feature is wired
- cross-tool handoffs work
- jobs execute
- failures recover
- retries work
- exports work
- settings persist
- permissions work

## DATA
- no fabricated provider fields
- normalized data is consistent
- raw data remains accessible
- provenance exists
- caching behaves predictably

## AI
- provider registry works
- feature toggles work
- master AI toggle works
- cost tracking works
- model routing works
- AI outputs are evidence-backed
- secrets are protected

## UX
- navigation is coherent
- first-time setup is understandable
- primary workflows are fast
- progressive disclosure works
- filters persist
- drawers preserve context
- empty/loading/error states teach

## RESPONSIVE
- desktop
- tablet
- mobile
- smallest supported width
- touch interactions
- PWA shell

## ACCESSIBILITY
- keyboard
- focus
- ARIA
- contrast
- reduced motion
- screen reader semantics

## SECURITY
- tenant isolation
- auth
- permissions
- secret handling
- rate limiting
- audit logs
- safe file access

## PERFORMANCE
- fast initial navigation
- lazy loading
- virtualization
- caching
- efficient DB access
- job queues for long work
- no unnecessary provider calls

## OPERATIONS
- structured logs
- metrics
- traces where appropriate
- provider health
- job health
- cost monitoring
- model monitoring

---

# 64. FINAL PRODUCT NORTH STAR

The ultimate product experience is:

A business connects its website, social channels, analytics, campaigns, commerce systems, and brand.

MAMAL continuously learns from legitimate behavioral signals and business outcomes.

The user asks a question or selects a goal.

MAMAL:
1. understands the context
2. identifies what evidence is needed
3. estimates cost
4. retrieves data
5. predicts likely outcomes where appropriate
6. explains the evidence
7. recommends actions
8. generates assets
9. creates links/campaigns/forms
10. runs approved automations
11. measures results
12. learns from the outcome
13. updates predictions
14. surfaces the next best action

This is not six tools inside one sidebar.

This is one intelligence-and-execution platform.

Build it that way.