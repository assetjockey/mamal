ALTER TABLE "transfer_files" ADD COLUMN "upload_id" text;--> statement-breakpoint
ALTER TABLE "transfer_files" ADD COLUMN "part_etags" jsonb DEFAULT '{}'::jsonb NOT NULL;--> statement-breakpoint
ALTER TABLE "transfers" ADD COLUMN "data_key_wrapped" text;