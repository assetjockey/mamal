CREATE TABLE "abuse_reports" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"link_id" uuid NOT NULL,
	"reporter_email" text,
	"reason" varchar(32) NOT NULL,
	"detail" text,
	"status" varchar(16) DEFAULT 'open' NOT NULL,
	"reviewed_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "barcodes" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"symbology" varchar(24) NOT NULL,
	"value" text NOT NULL,
	"style" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "bio_blocks" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"page_id" uuid NOT NULL,
	"type" varchar(48) NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"starts_at" timestamp with time zone,
	"ends_at" timestamp with time zone,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"clicks" bigint DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "bio_pages" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"link_id" uuid NOT NULL,
	"template" varchar(48) DEFAULT 'plain' NOT NULL,
	"theme" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"seo" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"custom_css" text,
	"is_published" boolean DEFAULT false NOT NULL,
	"ab_variant_of" uuid,
	"views" bigint DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "bio_pages_link" UNIQUE("link_id")
);
--> statement-breakpoint
CREATE TABLE "link_assignments" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"link_id" uuid NOT NULL,
	"rule_id" uuid NOT NULL,
	"visitor_hash" varchar(32) NOT NULL,
	"variant_index" integer NOT NULL,
	"expires_at" timestamp with time zone NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "link_assignments_visitor" UNIQUE("rule_id","visitor_hash")
);
--> statement-breakpoint
CREATE TABLE "link_folders" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"color" varchar(16),
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "qr_batches" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"count" integer DEFAULT 0 NOT NULL,
	"slug_prefix" varchar(32),
	"pre_printed" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "qr_codes" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"link_id" uuid,
	"type" varchar(32) NOT NULL,
	"name" text NOT NULL,
	"payload" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"style" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"batch_id" uuid,
	"scans" bigint DEFAULT 0 NOT NULL,
	"last_scanned_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone
);
--> statement-breakpoint
CREATE TABLE "splash_pages" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"delay_seconds" integer DEFAULT 5 NOT NULL,
	"is_skippable" boolean DEFAULT true NOT NULL,
	"auto_redirect" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "storage_providers" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid,
	"name" text NOT NULL,
	"handler" varchar(24) NOT NULL,
	"credentials_encrypted" text,
	"config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"is_default" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "transfer_files" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"transfer_id" uuid NOT NULL,
	"name" text NOT NULL,
	"size_bytes" bigint NOT NULL,
	"mime_type" varchar(128),
	"storage_key" text NOT NULL,
	"checksum_sha256" varchar(64),
	"sort_order" integer DEFAULT 0 NOT NULL,
	"downloads" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "transfers" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"link_id" uuid,
	"kind" varchar(16) DEFAULT 'send' NOT NULL,
	"delivery" varchar(16) DEFAULT 'link' NOT NULL,
	"sender_name" text,
	"sender_email" text,
	"recipients" text[] DEFAULT '{}' NOT NULL,
	"subject" text,
	"message" text,
	"password_hash" text,
	"is_encrypted" boolean DEFAULT false NOT NULL,
	"expires_at" timestamp with time zone,
	"download_limit" integer DEFAULT 0 NOT NULL,
	"downloads" integer DEFAULT 0 NOT NULL,
	"total_files" integer DEFAULT 0 NOT NULL,
	"total_bytes" bigint DEFAULT 0 NOT NULL,
	"branding" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"notify_on_download" boolean DEFAULT true NOT NULL,
	"notify_on_expiry" boolean DEFAULT false NOT NULL,
	"cancelled_at" timestamp with time zone,
	"cancel_reason" text,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "utm_presets" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"values" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"auto_apply" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
ALTER TABLE "abuse_reports" ADD CONSTRAINT "abuse_reports_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "abuse_reports" ADD CONSTRAINT "abuse_reports_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "barcodes" ADD CONSTRAINT "barcodes_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "barcodes" ADD CONSTRAINT "barcodes_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "bio_blocks" ADD CONSTRAINT "bio_blocks_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "bio_blocks" ADD CONSTRAINT "bio_blocks_page_id_bio_pages_id_fk" FOREIGN KEY ("page_id") REFERENCES "public"."bio_pages"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "bio_pages" ADD CONSTRAINT "bio_pages_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "bio_pages" ADD CONSTRAINT "bio_pages_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_assignments" ADD CONSTRAINT "link_assignments_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_assignments" ADD CONSTRAINT "link_assignments_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_assignments" ADD CONSTRAINT "link_assignments_rule_id_link_rules_id_fk" FOREIGN KEY ("rule_id") REFERENCES "public"."link_rules"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_folders" ADD CONSTRAINT "link_folders_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_folders" ADD CONSTRAINT "link_folders_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "qr_batches" ADD CONSTRAINT "qr_batches_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "qr_batches" ADD CONSTRAINT "qr_batches_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "qr_codes" ADD CONSTRAINT "qr_codes_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "qr_codes" ADD CONSTRAINT "qr_codes_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "qr_codes" ADD CONSTRAINT "qr_codes_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "splash_pages" ADD CONSTRAINT "splash_pages_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "splash_pages" ADD CONSTRAINT "splash_pages_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "storage_providers" ADD CONSTRAINT "storage_providers_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "transfer_files" ADD CONSTRAINT "transfer_files_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "transfer_files" ADD CONSTRAINT "transfer_files_transfer_id_transfers_id_fk" FOREIGN KEY ("transfer_id") REFERENCES "public"."transfers"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "transfers" ADD CONSTRAINT "transfers_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "transfers" ADD CONSTRAINT "transfers_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "transfers" ADD CONSTRAINT "transfers_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "utm_presets" ADD CONSTRAINT "utm_presets_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "utm_presets" ADD CONSTRAINT "utm_presets_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "abuse_reports_status_idx" ON "abuse_reports" USING btree ("workspace_id","status","created_at");--> statement-breakpoint
CREATE INDEX "barcodes_workspace_idx" ON "barcodes" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "bio_blocks_page_idx" ON "bio_blocks" USING btree ("page_id","sort_order");--> statement-breakpoint
CREATE INDEX "bio_pages_workspace_idx" ON "bio_pages" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "link_assignments_expiry_idx" ON "link_assignments" USING btree ("expires_at");--> statement-breakpoint
CREATE INDEX "link_folders_workspace_idx" ON "link_folders" USING btree ("workspace_id","project_id");--> statement-breakpoint
CREATE INDEX "qr_batches_workspace_idx" ON "qr_batches" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "qr_codes_workspace_idx" ON "qr_codes" USING btree ("workspace_id","project_id");--> statement-breakpoint
CREATE INDEX "qr_codes_batch_idx" ON "qr_codes" USING btree ("batch_id");--> statement-breakpoint
CREATE INDEX "splash_pages_workspace_idx" ON "splash_pages" USING btree ("workspace_id","project_id");--> statement-breakpoint
CREATE INDEX "storage_providers_workspace_idx" ON "storage_providers" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "transfer_files_transfer_idx" ON "transfer_files" USING btree ("transfer_id","sort_order");--> statement-breakpoint
CREATE INDEX "transfers_workspace_idx" ON "transfers" USING btree ("workspace_id","status");--> statement-breakpoint
CREATE INDEX "transfers_expiry_idx" ON "transfers" USING btree ("expires_at");--> statement-breakpoint
CREATE INDEX "utm_presets_workspace_idx" ON "utm_presets" USING btree ("workspace_id","project_id");