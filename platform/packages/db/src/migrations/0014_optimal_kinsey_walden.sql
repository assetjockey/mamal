CREATE TABLE "ad_accounts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"connection_id" uuid NOT NULL,
	"platform" varchar(24) NOT NULL,
	"external_id" text NOT NULL,
	"name" text NOT NULL,
	"currency" varchar(3) DEFAULT 'USD' NOT NULL,
	"timezone" varchar(64) DEFAULT 'UTC' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "ad_accounts_key" UNIQUE("workspace_id","platform","external_id")
);
--> statement-breakpoint
CREATE TABLE "ad_campaigns" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"account_id" uuid,
	"name" text NOT NULL,
	"platform" varchar(24) NOT NULL,
	"objective" varchar(32),
	"budget_micros" bigint,
	"budget_kind" varchar(12) DEFAULT 'daily' NOT NULL,
	"audience" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"creative_ids" uuid[] DEFAULT '{}' NOT NULL,
	"copy_ids" uuid[] DEFAULT '{}' NOT NULL,
	"status" varchar(16) DEFAULT 'draft' NOT NULL,
	"external_id" text,
	"sync_error" text,
	"starts_at" timestamp with time zone,
	"ends_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ad_copies" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"brand_id" uuid,
	"platform" varchar(32) NOT NULL,
	"objective" varchar(32),
	"framework" varchar(24),
	"tone" varchar(24),
	"language" varchar(8) DEFAULT 'en' NOT NULL,
	"brief" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"variants" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"word_count" integer DEFAULT 0 NOT NULL,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"vendor_cost_micros" bigint DEFAULT 0 NOT NULL,
	"is_favorite" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ad_creatives" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"brand_id" uuid,
	"type" varchar(12) NOT NULL,
	"status" varchar(16) DEFAULT 'queued' NOT NULL,
	"provider" varchar(32),
	"model_id" varchar(64),
	"prompt" text NOT NULL,
	"preset" varchar(48),
	"width" integer,
	"height" integer,
	"duration_seconds" real,
	"asset_id" uuid,
	"brand_snapshot" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"credit_hold_id" uuid,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"vendor_cost_micros" bigint DEFAULT 0 NOT NULL,
	"provider_job_id" text,
	"poll_count" smallint DEFAULT 0 NOT NULL,
	"next_poll_at" timestamp with time zone,
	"error" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ad_insights" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"account_id" uuid,
	"period_start" date NOT NULL,
	"period_end" date NOT NULL,
	"summary" text NOT NULL,
	"recommendations" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ad_metrics" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"account_id" uuid NOT NULL,
	"level" varchar(12) NOT NULL,
	"entity_id" text NOT NULL,
	"entity_name" text,
	"captured_on" date NOT NULL,
	"impressions" bigint DEFAULT 0 NOT NULL,
	"clicks" integer DEFAULT 0 NOT NULL,
	"spend_micros" bigint DEFAULT 0 NOT NULL,
	"conversions" real DEFAULT 0 NOT NULL,
	"conversion_value_micros" bigint DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "ad_metrics_key" UNIQUE("account_id","level","entity_id","captured_on")
);
--> statement-breakpoint
CREATE TABLE "backlink_snapshots" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"domain" varchar(253) NOT NULL,
	"captured_on" date NOT NULL,
	"rank" integer,
	"backlinks" bigint,
	"referring_domains" integer,
	"new_links" integer,
	"lost_links" integer,
	"broken_links" integer,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "backlink_snapshots_key" UNIQUE("workspace_id","domain","captured_on")
);
--> statement-breakpoint
CREATE TABLE "market_brands" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"description" text,
	"voice" text,
	"audience" text,
	"palette" text[] DEFAULT '{}' NOT NULL,
	"fonts" text[] DEFAULT '{}' NOT NULL,
	"logo_asset_id" uuid,
	"dos" text[] DEFAULT '{}' NOT NULL,
	"donts" text[] DEFAULT '{}' NOT NULL,
	"is_default" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_brands_key" UNIQUE("project_id","name")
);
--> statement-breakpoint
CREATE TABLE "market_connections" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"provider" varchar(32) NOT NULL,
	"external_id" text NOT NULL,
	"display_name" text NOT NULL,
	"avatar_url" text,
	"credentials_encrypted" text,
	"scopes" text[] DEFAULT '{}' NOT NULL,
	"expires_at" timestamp with time zone,
	"status" varchar(16) DEFAULT 'active' NOT NULL,
	"last_error" text,
	"last_synced_at" timestamp with time zone,
	"settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_connections_key" UNIQUE("workspace_id","provider","external_id")
);
--> statement-breakpoint
CREATE TABLE "content_briefs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"doc_id" uuid NOT NULL,
	"serp_analysis" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"entities" text[] DEFAULT '{}' NOT NULL,
	"questions" text[] DEFAULT '{}' NOT NULL,
	"competitor_outlines" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"target_word_count" integer,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "content_briefs_doc" UNIQUE("doc_id")
);
--> statement-breakpoint
CREATE TABLE "content_docs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"title" text NOT NULL,
	"slug" varchar(255),
	"status" varchar(16) DEFAULT 'draft' NOT NULL,
	"body" text DEFAULT '' NOT NULL,
	"outline" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"target_keywords" text[] DEFAULT '{}' NOT NULL,
	"seo_score" smallint,
	"readability" real,
	"word_count" integer DEFAULT 0 NOT NULL,
	"hero_asset_id" uuid,
	"meta" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"published_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "content_docs_slug_key" UNIQUE("project_id","slug")
);
--> statement-breakpoint
CREATE TABLE "content_pipelines" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"source" varchar(24) NOT NULL,
	"source_config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"schedule" varchar(24) DEFAULT 'weekly' NOT NULL,
	"template_id" uuid,
	"destination_id" uuid,
	"auto_publish" boolean DEFAULT false NOT NULL,
	"is_active" boolean DEFAULT false NOT NULL,
	"next_run_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "content_runs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"pipeline_id" uuid NOT NULL,
	"status" varchar(16) DEFAULT 'queued' NOT NULL,
	"doc_id" uuid,
	"trigger" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"error" text,
	"started_at" timestamp with time zone,
	"finished_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "market_influencers" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"provider" varchar(24) NOT NULL,
	"handle" text NOT NULL,
	"display_name" text,
	"followers" integer,
	"engagement_rate" real,
	"topics" text[] DEFAULT '{}' NOT NULL,
	"contact" text,
	"score" real,
	"list_name" text,
	"outreach_state" varchar(16) DEFAULT 'none' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_influencers_key" UNIQUE("project_id","provider","handle")
);
--> statement-breakpoint
CREATE TABLE "market_local_profiles" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"connection_id" uuid NOT NULL,
	"external_id" text NOT NULL,
	"name" text NOT NULL,
	"address" text,
	"latitude" numeric(10, 7),
	"longitude" numeric(10, 7),
	"primary_category" text,
	"categories" text[] DEFAULT '{}' NOT NULL,
	"rating" real,
	"review_count" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_local_profiles_key" UNIQUE("workspace_id","external_id")
);
--> statement-breakpoint
CREATE TABLE "market_local_rank_points" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"profile_id" uuid NOT NULL,
	"keyword" text NOT NULL,
	"captured_on" date NOT NULL,
	"latitude" numeric(10, 7) NOT NULL,
	"longitude" numeric(10, 7) NOT NULL,
	"position" smallint,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "market_local_reviews" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"profile_id" uuid NOT NULL,
	"external_id" text NOT NULL,
	"author" text,
	"rating" smallint,
	"comment" text,
	"reply" text,
	"replied_at" timestamp with time zone,
	"occurred_at" timestamp with time zone NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_local_reviews_key" UNIQUE("profile_id","external_id")
);
--> statement-breakpoint
CREATE TABLE "market_prompt_library" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid,
	"type" varchar(24) NOT NULL,
	"title" text NOT NULL,
	"body" text NOT NULL,
	"is_global" boolean DEFAULT false NOT NULL,
	"favorites" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "publish_destinations" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"kind" varchar(24) NOT NULL,
	"name" text NOT NULL,
	"connection_id" uuid,
	"credentials_encrypted" text,
	"config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"default_status" varchar(16) DEFAULT 'draft' NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "rank_configs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"site_id" uuid,
	"domain" varchar(253) NOT NULL,
	"location_code" integer DEFAULT 2840 NOT NULL,
	"language_code" varchar(8) DEFAULT 'en' NOT NULL,
	"devices" text[] DEFAULT '{"desktop"}' NOT NULL,
	"serp_depth" smallint DEFAULT 100 NOT NULL,
	"schedule" varchar(16) DEFAULT 'weekly' NOT NULL,
	"is_active" boolean DEFAULT true NOT NULL,
	"next_check_at" timestamp with time zone,
	"last_run_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "rank_keywords" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"config_id" uuid NOT NULL,
	"keyword" text NOT NULL,
	"target_url" text,
	"is_active" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "rank_keywords_key" UNIQUE("config_id","keyword")
);
--> statement-breakpoint
CREATE TABLE "rank_runs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"config_id" uuid NOT NULL,
	"status" varchar(16) DEFAULT 'queued' NOT NULL,
	"keywords_total" integer DEFAULT 0 NOT NULL,
	"keywords_done" integer DEFAULT 0 NOT NULL,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"vendor_cost_micros" bigint DEFAULT 0 NOT NULL,
	"error_code" varchar(48),
	"started_at" timestamp with time zone,
	"finished_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "rank_snapshots" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"config_id" uuid NOT NULL,
	"keyword_id" uuid NOT NULL,
	"captured_on" date NOT NULL,
	"device" varchar(12) DEFAULT 'desktop' NOT NULL,
	"position" smallint,
	"previous_position" smallint,
	"url" text,
	"serp_features" text[] DEFAULT '{}' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "rank_snapshots_key" UNIQUE("keyword_id","captured_on","device")
);
--> statement-breakpoint
CREATE TABLE "market_search_performance" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"connection_id" uuid NOT NULL,
	"captured_on" date NOT NULL,
	"query" text NOT NULL,
	"page" text NOT NULL,
	"country" varchar(3),
	"device" varchar(12),
	"clicks" integer DEFAULT 0 NOT NULL,
	"impressions" integer DEFAULT 0 NOT NULL,
	"position" real,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_search_performance_key" UNIQUE("connection_id","captured_on","query","page","device")
);
--> statement-breakpoint
CREATE TABLE "seo_keyword_tag_assignments" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"keyword_id" uuid NOT NULL,
	"tag_id" uuid NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "seo_keyword_tag_assignments_key" UNIQUE("keyword_id","tag_id")
);
--> statement-breakpoint
CREATE TABLE "seo_keyword_tags" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"color" varchar(16),
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "seo_keyword_tags_key" UNIQUE("workspace_id","project_id","name")
);
--> statement-breakpoint
CREATE TABLE "seo_keywords" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"keyword" text NOT NULL,
	"location_code" integer DEFAULT 2840 NOT NULL,
	"language_code" varchar(8) DEFAULT 'en' NOT NULL,
	"volume" integer,
	"cpc_micros" bigint,
	"competition" real,
	"difficulty" smallint,
	"intent" varchar(16),
	"monthly" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"source" varchar(24) DEFAULT 'dataforseo' NOT NULL,
	"fetched_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "seo_keywords_key" UNIQUE("workspace_id","keyword","location_code","language_code")
);
--> statement-breakpoint
CREATE TABLE "seo_opportunities" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"kind" varchar(24) NOT NULL,
	"query" text,
	"page" text,
	"score" real DEFAULT 0 NOT NULL,
	"evidence" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"status" varchar(16) DEFAULT 'open' NOT NULL,
	"detected_on" date NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "seo_opportunities_key" UNIQUE("project_id","kind","query","page")
);
--> statement-breakpoint
CREATE TABLE "social_account_groups" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"account_ids" uuid[] DEFAULT '{}' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "social_account_groups_key" UNIQUE("project_id","name")
);
--> statement-breakpoint
CREATE TABLE "social_accounts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"connection_id" uuid NOT NULL,
	"provider" varchar(24) NOT NULL,
	"external_id" text NOT NULL,
	"handle" text,
	"display_name" text NOT NULL,
	"avatar_url" text,
	"followers" integer,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "social_accounts_key" UNIQUE("workspace_id","provider","external_id")
);
--> statement-breakpoint
CREATE TABLE "social_mentions" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"provider" varchar(24) NOT NULL,
	"external_id" text NOT NULL,
	"author" text,
	"text" text,
	"sentiment" varchar(12),
	"reach" integer,
	"url" text,
	"occurred_at" timestamp with time zone NOT NULL,
	"handled_by" uuid,
	"handled_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "social_mentions_key" UNIQUE("workspace_id","provider","external_id")
);
--> statement-breakpoint
CREATE TABLE "social_posts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"body" text DEFAULT '' NOT NULL,
	"media_asset_ids" uuid[] DEFAULT '{}' NOT NULL,
	"link_id" uuid,
	"status" varchar(16) DEFAULT 'draft' NOT NULL,
	"schedule_type" varchar(16) DEFAULT 'now' NOT NULL,
	"scheduled_at" timestamp with time zone,
	"approval_state" varchar(16) DEFAULT 'none' NOT NULL,
	"approved_by" uuid,
	"campaign" varchar(160),
	"first_comment" text,
	"hashtag_set_id" uuid,
	"batch_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone
);
--> statement-breakpoint
CREATE TABLE "social_queues" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"account_id" uuid NOT NULL,
	"slots" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"timezone" varchar(64) DEFAULT 'UTC' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "social_queues_account" UNIQUE("account_id")
);
--> statement-breakpoint
CREATE TABLE "social_targets" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"post_id" uuid NOT NULL,
	"account_id" uuid NOT NULL,
	"overrides" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"next_run_at" timestamp with time zone,
	"attempts" smallint DEFAULT 0 NOT NULL,
	"remote_id" text,
	"remote_url" text,
	"error" text,
	"published_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "social_targets_key" UNIQUE("post_id","account_id")
);
--> statement-breakpoint
CREATE TABLE "trend_events" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"watch_id" uuid NOT NULL,
	"keyword" text NOT NULL,
	"geo" varchar(8) DEFAULT '' NOT NULL,
	"previous_value" real,
	"current_value" real,
	"delta_pct" real,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "trend_watches" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"keywords" text[] DEFAULT '{}' NOT NULL,
	"geos" text[] DEFAULT '{""}' NOT NULL,
	"timeframe" varchar(16) DEFAULT 'today 3-m' NOT NULL,
	"interval_minutes" integer DEFAULT 1440 NOT NULL,
	"threshold_pct" real DEFAULT 25 NOT NULL,
	"snapshot" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"is_active" boolean DEFAULT true NOT NULL,
	"next_run_at" timestamp with time zone,
	"last_run_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "market_ai_competitors" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"brand" text NOT NULL,
	"domain" varchar(253),
	"is_self" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_ai_competitors_key" UNIQUE("project_id","brand")
);
--> statement-breakpoint
CREATE TABLE "market_ai_prompt_runs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"prompt_id" uuid NOT NULL,
	"model" varchar(32) NOT NULL,
	"answer" text,
	"cited_sources" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"brand_mentioned" boolean DEFAULT false NOT NULL,
	"mention_position" smallint,
	"sentiment" varchar(12),
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"vendor_cost_micros" bigint DEFAULT 0 NOT NULL,
	"status" varchar(12) DEFAULT 'ok' NOT NULL,
	"error" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "market_ai_prompts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"prompt" text NOT NULL,
	"intent" varchar(24),
	"is_tracked" boolean DEFAULT true NOT NULL,
	"schedule" varchar(16) DEFAULT 'weekly' NOT NULL,
	"next_run_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_ai_prompts_key" UNIQUE("project_id","prompt")
);
--> statement-breakpoint
CREATE TABLE "market_ai_visibility_snapshots" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"captured_on" date NOT NULL,
	"model" varchar(32) NOT NULL,
	"share_of_voice" real DEFAULT 0 NOT NULL,
	"mention_rate" real DEFAULT 0 NOT NULL,
	"avg_position" real,
	"citation_count" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "market_ai_visibility_snapshots_key" UNIQUE("project_id","captured_on","model")
);
--> statement-breakpoint
ALTER TABLE "ad_accounts" ADD CONSTRAINT "ad_accounts_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_accounts" ADD CONSTRAINT "ad_accounts_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_accounts" ADD CONSTRAINT "ad_accounts_connection_id_market_connections_id_fk" FOREIGN KEY ("connection_id") REFERENCES "public"."market_connections"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_campaigns" ADD CONSTRAINT "ad_campaigns_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_campaigns" ADD CONSTRAINT "ad_campaigns_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_campaigns" ADD CONSTRAINT "ad_campaigns_account_id_ad_accounts_id_fk" FOREIGN KEY ("account_id") REFERENCES "public"."ad_accounts"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_copies" ADD CONSTRAINT "ad_copies_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_copies" ADD CONSTRAINT "ad_copies_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_copies" ADD CONSTRAINT "ad_copies_brand_id_market_brands_id_fk" FOREIGN KEY ("brand_id") REFERENCES "public"."market_brands"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_creatives" ADD CONSTRAINT "ad_creatives_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_creatives" ADD CONSTRAINT "ad_creatives_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_creatives" ADD CONSTRAINT "ad_creatives_brand_id_market_brands_id_fk" FOREIGN KEY ("brand_id") REFERENCES "public"."market_brands"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_creatives" ADD CONSTRAINT "ad_creatives_asset_id_assets_id_fk" FOREIGN KEY ("asset_id") REFERENCES "public"."assets"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_insights" ADD CONSTRAINT "ad_insights_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_insights" ADD CONSTRAINT "ad_insights_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_insights" ADD CONSTRAINT "ad_insights_account_id_ad_accounts_id_fk" FOREIGN KEY ("account_id") REFERENCES "public"."ad_accounts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_metrics" ADD CONSTRAINT "ad_metrics_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ad_metrics" ADD CONSTRAINT "ad_metrics_account_id_ad_accounts_id_fk" FOREIGN KEY ("account_id") REFERENCES "public"."ad_accounts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "backlink_snapshots" ADD CONSTRAINT "backlink_snapshots_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "backlink_snapshots" ADD CONSTRAINT "backlink_snapshots_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_brands" ADD CONSTRAINT "market_brands_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_brands" ADD CONSTRAINT "market_brands_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_brands" ADD CONSTRAINT "market_brands_logo_asset_id_assets_id_fk" FOREIGN KEY ("logo_asset_id") REFERENCES "public"."assets"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_connections" ADD CONSTRAINT "market_connections_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_connections" ADD CONSTRAINT "market_connections_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_briefs" ADD CONSTRAINT "content_briefs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_briefs" ADD CONSTRAINT "content_briefs_doc_id_content_docs_id_fk" FOREIGN KEY ("doc_id") REFERENCES "public"."content_docs"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_docs" ADD CONSTRAINT "content_docs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_docs" ADD CONSTRAINT "content_docs_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_docs" ADD CONSTRAINT "content_docs_hero_asset_id_assets_id_fk" FOREIGN KEY ("hero_asset_id") REFERENCES "public"."assets"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_pipelines" ADD CONSTRAINT "content_pipelines_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_pipelines" ADD CONSTRAINT "content_pipelines_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_pipelines" ADD CONSTRAINT "content_pipelines_destination_id_publish_destinations_id_fk" FOREIGN KEY ("destination_id") REFERENCES "public"."publish_destinations"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_runs" ADD CONSTRAINT "content_runs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_runs" ADD CONSTRAINT "content_runs_pipeline_id_content_pipelines_id_fk" FOREIGN KEY ("pipeline_id") REFERENCES "public"."content_pipelines"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "content_runs" ADD CONSTRAINT "content_runs_doc_id_content_docs_id_fk" FOREIGN KEY ("doc_id") REFERENCES "public"."content_docs"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_influencers" ADD CONSTRAINT "market_influencers_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_influencers" ADD CONSTRAINT "market_influencers_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_profiles" ADD CONSTRAINT "market_local_profiles_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_profiles" ADD CONSTRAINT "market_local_profiles_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_profiles" ADD CONSTRAINT "market_local_profiles_connection_id_market_connections_id_fk" FOREIGN KEY ("connection_id") REFERENCES "public"."market_connections"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_rank_points" ADD CONSTRAINT "market_local_rank_points_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_rank_points" ADD CONSTRAINT "market_local_rank_points_profile_id_market_local_profiles_id_fk" FOREIGN KEY ("profile_id") REFERENCES "public"."market_local_profiles"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_reviews" ADD CONSTRAINT "market_local_reviews_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_local_reviews" ADD CONSTRAINT "market_local_reviews_profile_id_market_local_profiles_id_fk" FOREIGN KEY ("profile_id") REFERENCES "public"."market_local_profiles"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_prompt_library" ADD CONSTRAINT "market_prompt_library_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "publish_destinations" ADD CONSTRAINT "publish_destinations_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "publish_destinations" ADD CONSTRAINT "publish_destinations_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "publish_destinations" ADD CONSTRAINT "publish_destinations_connection_id_market_connections_id_fk" FOREIGN KEY ("connection_id") REFERENCES "public"."market_connections"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_configs" ADD CONSTRAINT "rank_configs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_configs" ADD CONSTRAINT "rank_configs_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_configs" ADD CONSTRAINT "rank_configs_site_id_sites_id_fk" FOREIGN KEY ("site_id") REFERENCES "public"."sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_keywords" ADD CONSTRAINT "rank_keywords_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_keywords" ADD CONSTRAINT "rank_keywords_config_id_rank_configs_id_fk" FOREIGN KEY ("config_id") REFERENCES "public"."rank_configs"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_runs" ADD CONSTRAINT "rank_runs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_runs" ADD CONSTRAINT "rank_runs_config_id_rank_configs_id_fk" FOREIGN KEY ("config_id") REFERENCES "public"."rank_configs"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_snapshots" ADD CONSTRAINT "rank_snapshots_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_snapshots" ADD CONSTRAINT "rank_snapshots_config_id_rank_configs_id_fk" FOREIGN KEY ("config_id") REFERENCES "public"."rank_configs"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "rank_snapshots" ADD CONSTRAINT "rank_snapshots_keyword_id_rank_keywords_id_fk" FOREIGN KEY ("keyword_id") REFERENCES "public"."rank_keywords"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_search_performance" ADD CONSTRAINT "market_search_performance_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_search_performance" ADD CONSTRAINT "market_search_performance_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_search_performance" ADD CONSTRAINT "market_search_performance_connection_id_market_connections_id_fk" FOREIGN KEY ("connection_id") REFERENCES "public"."market_connections"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keyword_tag_assignments" ADD CONSTRAINT "seo_keyword_tag_assignments_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keyword_tag_assignments" ADD CONSTRAINT "seo_keyword_tag_assignments_keyword_id_seo_keywords_id_fk" FOREIGN KEY ("keyword_id") REFERENCES "public"."seo_keywords"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keyword_tag_assignments" ADD CONSTRAINT "seo_keyword_tag_assignments_tag_id_seo_keyword_tags_id_fk" FOREIGN KEY ("tag_id") REFERENCES "public"."seo_keyword_tags"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keyword_tags" ADD CONSTRAINT "seo_keyword_tags_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keyword_tags" ADD CONSTRAINT "seo_keyword_tags_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keywords" ADD CONSTRAINT "seo_keywords_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_keywords" ADD CONSTRAINT "seo_keywords_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_opportunities" ADD CONSTRAINT "seo_opportunities_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "seo_opportunities" ADD CONSTRAINT "seo_opportunities_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_account_groups" ADD CONSTRAINT "social_account_groups_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_account_groups" ADD CONSTRAINT "social_account_groups_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_accounts" ADD CONSTRAINT "social_accounts_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_accounts" ADD CONSTRAINT "social_accounts_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_accounts" ADD CONSTRAINT "social_accounts_connection_id_market_connections_id_fk" FOREIGN KEY ("connection_id") REFERENCES "public"."market_connections"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_mentions" ADD CONSTRAINT "social_mentions_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_mentions" ADD CONSTRAINT "social_mentions_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_posts" ADD CONSTRAINT "social_posts_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_posts" ADD CONSTRAINT "social_posts_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_posts" ADD CONSTRAINT "social_posts_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_queues" ADD CONSTRAINT "social_queues_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_queues" ADD CONSTRAINT "social_queues_account_id_social_accounts_id_fk" FOREIGN KEY ("account_id") REFERENCES "public"."social_accounts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_targets" ADD CONSTRAINT "social_targets_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_targets" ADD CONSTRAINT "social_targets_post_id_social_posts_id_fk" FOREIGN KEY ("post_id") REFERENCES "public"."social_posts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "social_targets" ADD CONSTRAINT "social_targets_account_id_social_accounts_id_fk" FOREIGN KEY ("account_id") REFERENCES "public"."social_accounts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "trend_events" ADD CONSTRAINT "trend_events_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "trend_events" ADD CONSTRAINT "trend_events_watch_id_trend_watches_id_fk" FOREIGN KEY ("watch_id") REFERENCES "public"."trend_watches"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "trend_watches" ADD CONSTRAINT "trend_watches_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "trend_watches" ADD CONSTRAINT "trend_watches_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_competitors" ADD CONSTRAINT "market_ai_competitors_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_competitors" ADD CONSTRAINT "market_ai_competitors_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_prompt_runs" ADD CONSTRAINT "market_ai_prompt_runs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_prompt_runs" ADD CONSTRAINT "market_ai_prompt_runs_prompt_id_market_ai_prompts_id_fk" FOREIGN KEY ("prompt_id") REFERENCES "public"."market_ai_prompts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_prompts" ADD CONSTRAINT "market_ai_prompts_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_prompts" ADD CONSTRAINT "market_ai_prompts_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_visibility_snapshots" ADD CONSTRAINT "market_ai_visibility_snapshots_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "market_ai_visibility_snapshots" ADD CONSTRAINT "market_ai_visibility_snapshots_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "ad_campaigns_project_idx" ON "ad_campaigns" USING btree ("project_id","status");--> statement-breakpoint
CREATE INDEX "ad_copies_project_idx" ON "ad_copies" USING btree ("project_id","platform");--> statement-breakpoint
CREATE INDEX "ad_creatives_project_idx" ON "ad_creatives" USING btree ("project_id","status");--> statement-breakpoint
CREATE INDEX "ad_creatives_poll_idx" ON "ad_creatives" USING btree ("status","next_poll_at");--> statement-breakpoint
CREATE INDEX "ad_insights_project_idx" ON "ad_insights" USING btree ("project_id","period_end");--> statement-breakpoint
CREATE INDEX "ad_metrics_day_idx" ON "ad_metrics" USING btree ("account_id","captured_on");--> statement-breakpoint
CREATE INDEX "market_connections_project_idx" ON "market_connections" USING btree ("project_id","provider");--> statement-breakpoint
CREATE INDEX "market_connections_status_idx" ON "market_connections" USING btree ("status","expires_at");--> statement-breakpoint
CREATE INDEX "content_docs_project_idx" ON "content_docs" USING btree ("project_id","status");--> statement-breakpoint
CREATE INDEX "content_pipelines_due_idx" ON "content_pipelines" USING btree ("is_active","next_run_at");--> statement-breakpoint
CREATE INDEX "content_runs_pipeline_idx" ON "content_runs" USING btree ("pipeline_id","created_at");--> statement-breakpoint
CREATE INDEX "market_local_rank_points_idx" ON "market_local_rank_points" USING btree ("profile_id","keyword","captured_on");--> statement-breakpoint
CREATE INDEX "market_local_reviews_idx" ON "market_local_reviews" USING btree ("profile_id","occurred_at");--> statement-breakpoint
CREATE INDEX "market_prompt_library_idx" ON "market_prompt_library" USING btree ("type","is_global");--> statement-breakpoint
CREATE INDEX "publish_destinations_project_idx" ON "publish_destinations" USING btree ("project_id","kind");--> statement-breakpoint
CREATE INDEX "rank_configs_due_idx" ON "rank_configs" USING btree ("is_active","next_check_at");--> statement-breakpoint
CREATE INDEX "rank_configs_project_idx" ON "rank_configs" USING btree ("project_id");--> statement-breakpoint
CREATE INDEX "rank_runs_config_idx" ON "rank_runs" USING btree ("config_id","created_at");--> statement-breakpoint
CREATE INDEX "rank_snapshots_trend_idx" ON "rank_snapshots" USING btree ("config_id","captured_on");--> statement-breakpoint
CREATE INDEX "market_search_performance_project_idx" ON "market_search_performance" USING btree ("project_id","captured_on");--> statement-breakpoint
CREATE INDEX "market_search_performance_query_idx" ON "market_search_performance" USING btree ("project_id","query");--> statement-breakpoint
CREATE INDEX "seo_keywords_project_idx" ON "seo_keywords" USING btree ("project_id","volume");--> statement-breakpoint
CREATE INDEX "seo_opportunities_open_idx" ON "seo_opportunities" USING btree ("project_id","status","score");--> statement-breakpoint
CREATE INDEX "social_accounts_project_idx" ON "social_accounts" USING btree ("project_id");--> statement-breakpoint
CREATE INDEX "social_mentions_project_idx" ON "social_mentions" USING btree ("project_id","occurred_at");--> statement-breakpoint
CREATE INDEX "social_posts_project_idx" ON "social_posts" USING btree ("project_id","status","scheduled_at");--> statement-breakpoint
CREATE INDEX "social_posts_batch_idx" ON "social_posts" USING btree ("batch_id");--> statement-breakpoint
CREATE INDEX "social_targets_due_idx" ON "social_targets" USING btree ("status","next_run_at");--> statement-breakpoint
CREATE INDEX "trend_events_watch_idx" ON "trend_events" USING btree ("watch_id","created_at");--> statement-breakpoint
CREATE INDEX "trend_watches_due_idx" ON "trend_watches" USING btree ("is_active","next_run_at");--> statement-breakpoint
CREATE INDEX "market_ai_prompt_runs_idx" ON "market_ai_prompt_runs" USING btree ("prompt_id","created_at");--> statement-breakpoint
CREATE INDEX "market_ai_prompts_due_idx" ON "market_ai_prompts" USING btree ("is_tracked","next_run_at");