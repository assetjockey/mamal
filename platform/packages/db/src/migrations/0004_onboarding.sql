CREATE TABLE "onboarding" (
	"workspace_id" uuid PRIMARY KEY NOT NULL,
	"interests" text[] DEFAULT '{}' NOT NULL,
	"role" varchar(64),
	"completed_steps" text[] DEFAULT '{}' NOT NULL,
	"first_resource_url" text,
	"dismissed_at" timestamp with time zone,
	"completed_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
ALTER TABLE "onboarding" ADD CONSTRAINT "onboarding_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "onboarding_workspace_idx" ON "onboarding" USING btree ("workspace_id");