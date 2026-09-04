CREATE TABLE "events_daily" (
	"workspace_id" uuid NOT NULL,
	"subject_id" uuid NOT NULL,
	"kind" varchar(24) NOT NULL,
	"date" timestamp with time zone NOT NULL,
	"dimension" varchar(32) NOT NULL,
	"dimension_value" varchar(255) NOT NULL,
	"count" bigint DEFAULT 0 NOT NULL,
	"uniques" bigint DEFAULT 0 NOT NULL,
	"value" double precision DEFAULT 0 NOT NULL
);
--> statement-breakpoint
CREATE TABLE "events_raw" (
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"kind" varchar(24) NOT NULL,
	"tool" varchar(32) NOT NULL,
	"subject_id" uuid NOT NULL,
	"subject_type" varchar(32) NOT NULL,
	"ts" timestamp with time zone DEFAULT now() NOT NULL,
	"event_id" uuid NOT NULL,
	"visitor_id" varchar(32),
	"session_id" uuid,
	"click_id" uuid,
	"is_unique" boolean DEFAULT false NOT NULL,
	"is_bot" boolean DEFAULT false NOT NULL,
	"url" text,
	"path" text,
	"host" varchar(253),
	"referrer_host" varchar(253),
	"referrer_url" text,
	"utm" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"country" varchar(2),
	"region" varchar(64),
	"city" varchar(128),
	"browser" varchar(64),
	"os" varchar(64),
	"device" varchar(32),
	"language" varchar(16),
	"screen" varchar(16),
	"name" varchar(128),
	"value" double precision DEFAULT 0 NOT NULL,
	"status_code" integer,
	"duration_ms" integer,
	"props" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"related_urns" text[] DEFAULT '{}' NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX "events_daily_pk" ON "events_daily" USING btree ("workspace_id","subject_id","kind","date","dimension","dimension_value");--> statement-breakpoint
CREATE INDEX "events_daily_read_idx" ON "events_daily" USING btree ("workspace_id","kind","date");--> statement-breakpoint
CREATE UNIQUE INDEX "events_raw_event_id_key" ON "events_raw" USING btree ("event_id");--> statement-breakpoint
CREATE INDEX "events_raw_subject_idx" ON "events_raw" USING btree ("workspace_id","subject_id","kind","ts");--> statement-breakpoint
CREATE INDEX "events_raw_workspace_ts_idx" ON "events_raw" USING btree ("workspace_id","ts");--> statement-breakpoint
CREATE INDEX "events_raw_click_idx" ON "events_raw" USING btree ("workspace_id","click_id");