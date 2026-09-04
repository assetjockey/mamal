-- Postgres will not auto-cast text[] to jsonb; to_jsonb does it losslessly.
ALTER TABLE "events_raw" ALTER COLUMN "related_urns" DROP DEFAULT;
--> statement-breakpoint
ALTER TABLE "events_raw" ALTER COLUMN "related_urns" SET DATA TYPE jsonb USING to_jsonb("related_urns");
--> statement-breakpoint
ALTER TABLE "events_raw" ALTER COLUMN "related_urns" SET DEFAULT '[]'::jsonb;--> statement-breakpoint
ALTER TABLE "events_raw" ALTER COLUMN "related_urns" SET DEFAULT '[]'::jsonb;