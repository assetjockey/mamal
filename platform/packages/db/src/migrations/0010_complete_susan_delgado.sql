CREATE TABLE "link_suggestions" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"kind" varchar(32) NOT NULL,
	"target_url" text NOT NULL,
	"context_url" text,
	"source_urn" text,
	"status" varchar(16) DEFAULT 'open' NOT NULL,
	"created_link_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "link_suggestions_key" UNIQUE("workspace_id","kind","target_url")
);
--> statement-breakpoint
ALTER TABLE "link_suggestions" ADD CONSTRAINT "link_suggestions_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_suggestions" ADD CONSTRAINT "link_suggestions_created_link_id_links_id_fk" FOREIGN KEY ("created_link_id") REFERENCES "public"."links"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "link_suggestions_status_idx" ON "link_suggestions" USING btree ("workspace_id","status");