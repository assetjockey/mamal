ALTER TABLE "custom_domains" ADD COLUMN "dns_checked_at" timestamp with time zone;--> statement-breakpoint
ALTER TABLE "custom_domains" ADD COLUMN "last_check" jsonb DEFAULT '{}'::jsonb NOT NULL;