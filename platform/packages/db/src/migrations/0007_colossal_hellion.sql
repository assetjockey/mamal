CREATE TABLE "push_flow_progress" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"flow_id" uuid NOT NULL,
	"subscriber_id" uuid NOT NULL,
	"next_step" integer NOT NULL,
	"due_at" timestamp with time zone NOT NULL,
	"completed_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "push_flow_progress_once" UNIQUE("flow_id","subscriber_id")
);
--> statement-breakpoint
ALTER TABLE "push_flow_progress" ADD CONSTRAINT "push_flow_progress_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_flow_progress" ADD CONSTRAINT "push_flow_progress_flow_id_push_flows_id_fk" FOREIGN KEY ("flow_id") REFERENCES "public"."push_flows"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "push_flow_progress" ADD CONSTRAINT "push_flow_progress_subscriber_id_push_subscribers_id_fk" FOREIGN KEY ("subscriber_id") REFERENCES "public"."push_subscribers"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "push_flow_progress_due_idx" ON "push_flow_progress" USING btree ("due_at","completed_at");