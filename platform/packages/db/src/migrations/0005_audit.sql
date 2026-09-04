CREATE TABLE "audit_issues" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"audit_id" uuid NOT NULL,
	"audit_site_id" uuid NOT NULL,
	"page_id" uuid,
	"page_url" text,
	"rule_id" varchar(64) NOT NULL,
	"severity" varchar(16) NOT NULL,
	"evidence" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"status" varchar(16) DEFAULT 'open' NOT NULL,
	"note" text,
	"assigned_to_user_id" uuid,
	"first_seen_audit_id" uuid,
	"resolved_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "audit_lighthouse" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"audit_id" uuid NOT NULL,
	"page_id" uuid,
	"url" text NOT NULL,
	"strategy" varchar(16) DEFAULT 'mobile' NOT NULL,
	"performance" integer,
	"accessibility" integer,
	"best_practices" integer,
	"seo" integer,
	"lcp_ms" integer,
	"cls" real,
	"inp_ms" integer,
	"ttfb_ms" integer,
	"tbt_ms" integer,
	"report_asset_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "audit_links" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"audit_id" uuid NOT NULL,
	"source_url" text NOT NULL,
	"target_url" text NOT NULL,
	"anchor" text,
	"rel" varchar(64),
	"is_internal" boolean DEFAULT true NOT NULL,
	"status_code" integer,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "audit_pages" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"audit_id" uuid NOT NULL,
	"url" text NOT NULL,
	"url_hash" varchar(64) NOT NULL,
	"status_code" integer,
	"fetch_class" varchar(16) DEFAULT 'ok' NOT NULL,
	"redirect_chain" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"title" text,
	"meta_description" text,
	"canonical" text,
	"header_canonical" text,
	"robots_meta" varchar(128),
	"x_robots_tag" varchar(128),
	"og_title" text,
	"og_description" text,
	"og_image" text,
	"h1_count" integer DEFAULT 0 NOT NULL,
	"h2_count" integer DEFAULT 0 NOT NULL,
	"h3_count" integer DEFAULT 0 NOT NULL,
	"headings" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"h1_text" text,
	"word_count" integer DEFAULT 0 NOT NULL,
	"text_ratio" real DEFAULT 0 NOT NULL,
	"images_total" integer DEFAULT 0 NOT NULL,
	"images_missing_alt" integer DEFAULT 0 NOT NULL,
	"links_internal" integer DEFAULT 0 NOT NULL,
	"links_external" integer DEFAULT 0 NOT NULL,
	"has_structured_data" boolean DEFAULT false NOT NULL,
	"schema_types" text[] DEFAULT '{}' NOT NULL,
	"hreflang" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"lang" varchar(16),
	"is_indexable" boolean DEFAULT true NOT NULL,
	"depth" integer DEFAULT 0 NOT NULL,
	"in_sitemap" boolean DEFAULT false NOT NULL,
	"content_hash" varchar(64),
	"response_ms" integer,
	"ttfb_ms" integer,
	"bytes" integer,
	"http_version" varchar(8),
	"compression" varchar(16),
	"is_https" boolean DEFAULT false NOT NULL,
	"headers" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "audit_pages_url_key" UNIQUE("audit_id","url_hash")
);
--> statement-breakpoint
CREATE TABLE "audit_rule_overrides" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"rule_id" varchar(64) NOT NULL,
	"is_enabled" boolean,
	"severity" varchar(16),
	"thresholds" jsonb,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "audit_rule_overrides_key" UNIQUE("workspace_id","rule_id")
);
--> statement-breakpoint
CREATE TABLE "audit_rules" (
	"id" varchar(64) PRIMARY KEY NOT NULL,
	"category" varchar(32) NOT NULL,
	"severity" varchar(16) NOT NULL,
	"weight" integer DEFAULT 5 NOT NULL,
	"title" text NOT NULL,
	"why" text NOT NULL,
	"how_to_fix" text NOT NULL,
	"docs_url" text,
	"applies_to" varchar(8) DEFAULT 'page' NOT NULL,
	"thresholds" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"is_ai_relevant" boolean DEFAULT false NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "audit_sites" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"site_id" uuid NOT NULL,
	"score" integer,
	"previous_score" integer,
	"grade" varchar(2),
	"tests_total" integer DEFAULT 0 NOT NULL,
	"tests_passed" integer DEFAULT 0 NOT NULL,
	"critical_count" integer DEFAULT 0 NOT NULL,
	"warning_count" integer DEFAULT 0 NOT NULL,
	"info_count" integer DEFAULT 0 NOT NULL,
	"schedule" varchar(16) DEFAULT 'manual' NOT NULL,
	"crawl_config" jsonb DEFAULT '{"maxPages":25,"maxDepth":5,"respectRobots":true,"renderJs":false,"lighthouse":"off"}'::jsonb NOT NULL,
	"notification_channel_ids" text[] DEFAULT '{}' NOT NULL,
	"last_audit_at" timestamp with time zone,
	"next_audit_at" timestamp with time zone,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "audit_sites_site_key" UNIQUE("site_id")
);
--> statement-breakpoint
CREATE TABLE "audit_snapshots" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"audit_site_id" uuid NOT NULL,
	"audit_id" uuid,
	"captured_at" timestamp with time zone DEFAULT now() NOT NULL,
	"score" integer NOT NULL,
	"critical_count" integer DEFAULT 0 NOT NULL,
	"warning_count" integer DEFAULT 0 NOT NULL,
	"info_count" integer DEFAULT 0 NOT NULL,
	"pages_crawled" integer DEFAULT 0 NOT NULL
);
--> statement-breakpoint
CREATE TABLE "audit_tool_runs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid,
	"slug" varchar(64) NOT NULL,
	"input" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"output" jsonb,
	"duration_ms" integer,
	"ip_hash" varchar(64),
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "audits" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"audit_site_id" uuid NOT NULL,
	"trigger" varchar(16) DEFAULT 'manual' NOT NULL,
	"status" varchar(16) DEFAULT 'queued' NOT NULL,
	"phase" varchar(16) DEFAULT 'queued' NOT NULL,
	"start_url" text NOT NULL,
	"config" jsonb NOT NULL,
	"pages_crawled" integer DEFAULT 0 NOT NULL,
	"pages_total" integer DEFAULT 0 NOT NULL,
	"pages_blocked" integer DEFAULT 0 NOT NULL,
	"lighthouse_done" integer DEFAULT 0 NOT NULL,
	"score" integer,
	"critical_count" integer DEFAULT 0 NOT NULL,
	"warning_count" integer DEFAULT 0 NOT NULL,
	"info_count" integer DEFAULT 0 NOT NULL,
	"crawl_cursor" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"error_code" varchar(48),
	"error_detail" text,
	"started_at" timestamp with time zone,
	"finished_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
ALTER TABLE "audit_issues" ADD CONSTRAINT "audit_issues_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_issues" ADD CONSTRAINT "audit_issues_audit_id_audits_id_fk" FOREIGN KEY ("audit_id") REFERENCES "public"."audits"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_issues" ADD CONSTRAINT "audit_issues_audit_site_id_audit_sites_id_fk" FOREIGN KEY ("audit_site_id") REFERENCES "public"."audit_sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_issues" ADD CONSTRAINT "audit_issues_page_id_audit_pages_id_fk" FOREIGN KEY ("page_id") REFERENCES "public"."audit_pages"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_lighthouse" ADD CONSTRAINT "audit_lighthouse_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_lighthouse" ADD CONSTRAINT "audit_lighthouse_audit_id_audits_id_fk" FOREIGN KEY ("audit_id") REFERENCES "public"."audits"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_lighthouse" ADD CONSTRAINT "audit_lighthouse_page_id_audit_pages_id_fk" FOREIGN KEY ("page_id") REFERENCES "public"."audit_pages"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_links" ADD CONSTRAINT "audit_links_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_links" ADD CONSTRAINT "audit_links_audit_id_audits_id_fk" FOREIGN KEY ("audit_id") REFERENCES "public"."audits"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_pages" ADD CONSTRAINT "audit_pages_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_pages" ADD CONSTRAINT "audit_pages_audit_id_audits_id_fk" FOREIGN KEY ("audit_id") REFERENCES "public"."audits"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_rule_overrides" ADD CONSTRAINT "audit_rule_overrides_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_rule_overrides" ADD CONSTRAINT "audit_rule_overrides_rule_id_audit_rules_id_fk" FOREIGN KEY ("rule_id") REFERENCES "public"."audit_rules"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_sites" ADD CONSTRAINT "audit_sites_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_sites" ADD CONSTRAINT "audit_sites_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_sites" ADD CONSTRAINT "audit_sites_site_id_sites_id_fk" FOREIGN KEY ("site_id") REFERENCES "public"."sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_snapshots" ADD CONSTRAINT "audit_snapshots_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_snapshots" ADD CONSTRAINT "audit_snapshots_audit_site_id_audit_sites_id_fk" FOREIGN KEY ("audit_site_id") REFERENCES "public"."audit_sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audits" ADD CONSTRAINT "audits_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audits" ADD CONSTRAINT "audits_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audits" ADD CONSTRAINT "audits_audit_site_id_audit_sites_id_fk" FOREIGN KEY ("audit_site_id") REFERENCES "public"."audit_sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "audit_issues_audit_idx" ON "audit_issues" USING btree ("audit_id","severity");--> statement-breakpoint
CREATE INDEX "audit_issues_rule_idx" ON "audit_issues" USING btree ("audit_site_id","rule_id","status");--> statement-breakpoint
CREATE INDEX "audit_issues_page_idx" ON "audit_issues" USING btree ("page_id");--> statement-breakpoint
CREATE INDEX "audit_lighthouse_audit_idx" ON "audit_lighthouse" USING btree ("audit_id");--> statement-breakpoint
CREATE INDEX "audit_links_audit_idx" ON "audit_links" USING btree ("audit_id","is_internal");--> statement-breakpoint
CREATE INDEX "audit_links_target_idx" ON "audit_links" USING btree ("audit_id","target_url");--> statement-breakpoint
CREATE INDEX "audit_pages_audit_idx" ON "audit_pages" USING btree ("audit_id","depth");--> statement-breakpoint
CREATE INDEX "audit_pages_hash_idx" ON "audit_pages" USING btree ("audit_id","content_hash");--> statement-breakpoint
CREATE INDEX "audit_rules_category_idx" ON "audit_rules" USING btree ("category","is_enabled");--> statement-breakpoint
CREATE INDEX "audit_sites_due_idx" ON "audit_sites" USING btree ("next_audit_at","is_enabled");--> statement-breakpoint
CREATE INDEX "audit_snapshots_site_idx" ON "audit_snapshots" USING btree ("audit_site_id","captured_at");--> statement-breakpoint
CREATE INDEX "audit_tool_runs_slug_idx" ON "audit_tool_runs" USING btree ("slug","created_at");--> statement-breakpoint
CREATE INDEX "audits_site_idx" ON "audits" USING btree ("audit_site_id","created_at");--> statement-breakpoint
CREATE INDEX "audits_status_idx" ON "audits" USING btree ("status","phase");