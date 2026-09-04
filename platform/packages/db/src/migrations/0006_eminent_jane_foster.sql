CREATE TABLE "confirm_campaigns" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"site_id" uuid NOT NULL,
	"name" text NOT NULL,
	"pixel_key" varchar(32) NOT NULL,
	"host_allowlist" text[] DEFAULT '{}' NOT NULL,
	"branding_removed" boolean DEFAULT false NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"impressions" bigint DEFAULT 0 NOT NULL,
	"clicks" bigint DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "confirm_campaigns_pixel_key" UNIQUE("pixel_key")
);
--> statement-breakpoint
CREATE TABLE "confirm_conversions" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"campaign_id" uuid NOT NULL,
	"source" varchar(24) NOT NULL,
	"type" varchar(48) DEFAULT 'conversion' NOT NULL,
	"data" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"path" text,
	"page_title" text,
	"country" varchar(2),
	"city" text,
	"source_urn" text,
	"occurred_at" timestamp with time zone DEFAULT now() NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "confirm_sources" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"campaign_id" uuid NOT NULL,
	"kind" varchar(24) NOT NULL,
	"name" text NOT NULL,
	"config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"secret" text,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"last_received_at" timestamp with time zone,
	"received_count" bigint DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "confirm_widgets" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"campaign_id" uuid NOT NULL,
	"type" varchar(48) NOT NULL,
	"name" text NOT NULL,
	"settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"targeting" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"theme" varchar(32) DEFAULT 'stockholm' NOT NULL,
	"position" varchar(16) DEFAULT 'bottom-left' NOT NULL,
	"translations" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"display_frequency" varchar(24) DEFAULT 'always' NOT NULL,
	"display_limit" integer DEFAULT 0 NOT NULL,
	"delay_seconds" integer DEFAULT 3 NOT NULL,
	"duration_seconds" integer DEFAULT 8 NOT NULL,
	"starts_at" timestamp with time zone,
	"ends_at" timestamp with time zone,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"impressions" bigint DEFAULT 0 NOT NULL,
	"hovers" bigint DEFAULT 0 NOT NULL,
	"clicks" bigint DEFAULT 0 NOT NULL,
	"submissions" bigint DEFAULT 0 NOT NULL,
	"closes" bigint DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "push_campaigns" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"push_website_id" uuid NOT NULL,
	"segment_id" uuid,
	"title" text NOT NULL,
	"body" text NOT NULL,
	"icon_url" text,
	"image_url" text,
	"url" text,
	"actions" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"status" varchar(16) DEFAULT 'draft' NOT NULL,
	"scheduled_at" timestamp with time zone,
	"sent_at" timestamp with time zone,
	"ttl_seconds" integer DEFAULT 86400 NOT NULL,
	"recurrence" jsonb,
	"next_run_at" timestamp with time zone,
	"sent" integer DEFAULT 0 NOT NULL,
	"delivered" integer DEFAULT 0 NOT NULL,
	"clicked" integer DEFAULT 0 NOT NULL,
	"failed" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "push_flow_steps" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"flow_id" uuid NOT NULL,
	"step_order" integer NOT NULL,
	"delay_seconds" integer DEFAULT 0 NOT NULL,
	"title" text NOT NULL,
	"body" text NOT NULL,
	"url" text,
	"branch_on" varchar(16),
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "push_flow_steps_order" UNIQUE("flow_id","step_order")
);
--> statement-breakpoint
CREATE TABLE "push_flows" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"push_website_id" uuid NOT NULL,
	"name" text NOT NULL,
	"trigger" varchar(24) NOT NULL,
	"trigger_config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"is_enabled" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "push_rss_automations" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"push_website_id" uuid NOT NULL,
	"feed_url" text NOT NULL,
	"check_interval_minutes" integer DEFAULT 60 NOT NULL,
	"title_template" text DEFAULT '{{title}}' NOT NULL,
	"body_template" text DEFAULT '{{summary}}' NOT NULL,
	"last_guid" text,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"next_check_at" timestamp with time zone DEFAULT now() NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "push_segments" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"push_website_id" uuid NOT NULL,
	"name" text NOT NULL,
	"filter" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "push_subscribers" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"push_website_id" uuid NOT NULL,
	"endpoint" text NOT NULL,
	"p256dh" text NOT NULL,
	"auth" text NOT NULL,
	"country" varchar(2),
	"browser" varchar(32),
	"os" varchar(32),
	"device" varchar(16),
	"language" varchar(12),
	"tags" text[] DEFAULT '{}' NOT NULL,
	"status" varchar(16) DEFAULT 'active' NOT NULL,
	"last_seen_at" timestamp with time zone DEFAULT now() NOT NULL,
	"subscribed_at" timestamp with time zone DEFAULT now() NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "push_subscribers_endpoint" UNIQUE("push_website_id","endpoint")
);
--> statement-breakpoint
CREATE TABLE "push_websites" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"site_id" uuid NOT NULL,
	"vapid_public_key" text NOT NULL,
	"vapid_private_key_encrypted" text NOT NULL,
	"service_worker_path" text DEFAULT '/mamal-sw.js' NOT NULL,
	"prompt_style" varchar(16) DEFAULT 'widget' NOT NULL,
	"prompt_settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "push_websites_site" UNIQUE("site_id")
);
--> statement-breakpoint
ALTER TABLE "confirm_campaigns" ADD CONSTRAINT "confirm_campaigns_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_campaigns" ADD CONSTRAINT "confirm_campaigns_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_campaigns" ADD CONSTRAINT "confirm_campaigns_site_id_sites_id_fk" FOREIGN KEY ("site_id") REFERENCES "public"."sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_conversions" ADD CONSTRAINT "confirm_conversions_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_conversions" ADD CONSTRAINT "confirm_conversions_campaign_id_confirm_campaigns_id_fk" FOREIGN KEY ("campaign_id") REFERENCES "public"."confirm_campaigns"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_sources" ADD CONSTRAINT "confirm_sources_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_sources" ADD CONSTRAINT "confirm_sources_campaign_id_confirm_campaigns_id_fk" FOREIGN KEY ("campaign_id") REFERENCES "public"."confirm_campaigns"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_widgets" ADD CONSTRAINT "confirm_widgets_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "confirm_widgets" ADD CONSTRAINT "confirm_widgets_campaign_id_confirm_campaigns_id_fk" FOREIGN KEY ("campaign_id") REFERENCES "public"."confirm_campaigns"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_campaigns" ADD CONSTRAINT "push_campaigns_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_campaigns" ADD CONSTRAINT "push_campaigns_push_website_id_push_websites_id_fk" FOREIGN KEY ("push_website_id") REFERENCES "public"."push_websites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_campaigns" ADD CONSTRAINT "push_campaigns_segment_id_push_segments_id_fk" FOREIGN KEY ("segment_id") REFERENCES "public"."push_segments"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_flow_steps" ADD CONSTRAINT "push_flow_steps_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_flow_steps" ADD CONSTRAINT "push_flow_steps_flow_id_push_flows_id_fk" FOREIGN KEY ("flow_id") REFERENCES "public"."push_flows"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_flows" ADD CONSTRAINT "push_flows_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_flows" ADD CONSTRAINT "push_flows_push_website_id_push_websites_id_fk" FOREIGN KEY ("push_website_id") REFERENCES "public"."push_websites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_rss_automations" ADD CONSTRAINT "push_rss_automations_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_rss_automations" ADD CONSTRAINT "push_rss_automations_push_website_id_push_websites_id_fk" FOREIGN KEY ("push_website_id") REFERENCES "public"."push_websites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_segments" ADD CONSTRAINT "push_segments_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_segments" ADD CONSTRAINT "push_segments_push_website_id_push_websites_id_fk" FOREIGN KEY ("push_website_id") REFERENCES "public"."push_websites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_subscribers" ADD CONSTRAINT "push_subscribers_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_subscribers" ADD CONSTRAINT "push_subscribers_push_website_id_push_websites_id_fk" FOREIGN KEY ("push_website_id") REFERENCES "public"."push_websites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_websites" ADD CONSTRAINT "push_websites_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_websites" ADD CONSTRAINT "push_websites_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_websites" ADD CONSTRAINT "push_websites_site_id_sites_id_fk" FOREIGN KEY ("site_id") REFERENCES "public"."sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "confirm_campaigns_workspace_idx" ON "confirm_campaigns" USING btree ("workspace_id","project_id");--> statement-breakpoint
CREATE INDEX "confirm_campaigns_site_idx" ON "confirm_campaigns" USING btree ("site_id");--> statement-breakpoint
CREATE INDEX "confirm_conversions_recent_idx" ON "confirm_conversions" USING btree ("campaign_id","occurred_at");--> statement-breakpoint
CREATE INDEX "confirm_conversions_workspace_idx" ON "confirm_conversions" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "confirm_sources_campaign_idx" ON "confirm_sources" USING btree ("campaign_id");--> statement-breakpoint
CREATE INDEX "confirm_widgets_campaign_idx" ON "confirm_widgets" USING btree ("campaign_id","is_enabled");--> statement-breakpoint
CREATE INDEX "confirm_widgets_workspace_idx" ON "confirm_widgets" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "push_campaigns_site_idx" ON "push_campaigns" USING btree ("push_website_id","status");--> statement-breakpoint
CREATE INDEX "push_campaigns_due_idx" ON "push_campaigns" USING btree ("next_run_at");--> statement-breakpoint
CREATE INDEX "push_flows_site_idx" ON "push_flows" USING btree ("push_website_id","is_enabled");--> statement-breakpoint
CREATE INDEX "push_rss_due_idx" ON "push_rss_automations" USING btree ("next_check_at","is_enabled");--> statement-breakpoint
CREATE INDEX "push_segments_site_idx" ON "push_segments" USING btree ("push_website_id");--> statement-breakpoint
CREATE INDEX "push_subscribers_site_status_idx" ON "push_subscribers" USING btree ("push_website_id","status");--> statement-breakpoint
CREATE INDEX "push_websites_workspace_idx" ON "push_websites" USING btree ("workspace_id");