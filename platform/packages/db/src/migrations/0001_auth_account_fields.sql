ALTER TABLE "accounts" ADD COLUMN "issuer" varchar(255) DEFAULT '' NOT NULL;--> statement-breakpoint
ALTER TABLE "accounts" ADD COLUMN "id_token" text;--> statement-breakpoint
ALTER TABLE "accounts" ADD COLUMN "refresh_token_expires_at" timestamp with time zone;