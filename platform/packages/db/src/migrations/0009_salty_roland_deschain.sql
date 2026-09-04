ALTER TABLE "transfer_files" ADD COLUMN "parts" integer[] DEFAULT '{}' NOT NULL;--> statement-breakpoint
ALTER TABLE "transfer_files" ADD COLUMN "uploaded_at" timestamp with time zone;