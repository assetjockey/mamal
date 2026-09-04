CREATE TABLE "accounts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"user_id" uuid NOT NULL,
	"provider_id" varchar(64) NOT NULL,
	"account_id" varchar(255) NOT NULL,
	"access_token" text,
	"refresh_token" text,
	"access_token_expires_at" timestamp with time zone,
	"scope" text,
	"password" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "accounts_provider_key" UNIQUE("provider_id","account_id")
);
--> statement-breakpoint
CREATE TABLE "api_keys" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"user_id" uuid,
	"name" text NOT NULL,
	"key_hash" text NOT NULL,
	"prefix" varchar(16) NOT NULL,
	"scopes" text[] DEFAULT '{}' NOT NULL,
	"rate_limit_per_min" text DEFAULT '60' NOT NULL,
	"last_used_at" timestamp with time zone,
	"expires_at" timestamp with time zone,
	"revoked_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "api_keys_hash_key" UNIQUE("key_hash")
);
--> statement-breakpoint
CREATE TABLE "audit_logs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"actor_user_id" uuid,
	"actor_api_key_id" uuid,
	"action" varchar(128) NOT NULL,
	"resource_urn" text,
	"before" jsonb,
	"after" jsonb,
	"ip_address" varchar(64),
	"user_agent" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "invitations" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"email" varchar(320) NOT NULL,
	"role" varchar(16) DEFAULT 'member' NOT NULL,
	"tool_grants" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"token" text NOT NULL,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"inviter_user_id" uuid,
	"expires_at" timestamp with time zone NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "invitations_token_key" UNIQUE("token")
);
--> statement-breakpoint
CREATE TABLE "oauth_connections" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"provider" varchar(32) NOT NULL,
	"external_account_id" varchar(255) NOT NULL,
	"label" text,
	"scopes" text[] DEFAULT '{}' NOT NULL,
	"encrypted_tokens" text NOT NULL,
	"expires_at" timestamp with time zone,
	"status" varchar(16) DEFAULT 'active' NOT NULL,
	"last_error" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "oauth_connections_key" UNIQUE("workspace_id","provider","external_account_id")
);
--> statement-breakpoint
CREATE TABLE "project_members" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"user_id" uuid NOT NULL,
	"role" varchar(16) DEFAULT 'member' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "project_members_key" UNIQUE("project_id","user_id")
);
--> statement-breakpoint
CREATE TABLE "projects" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"name" text NOT NULL,
	"slug" varchar(64) NOT NULL,
	"color" varchar(16) DEFAULT 'indigo' NOT NULL,
	"description" text,
	"is_default" boolean DEFAULT false NOT NULL,
	"timezone" varchar(64) DEFAULT 'UTC' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "projects_slug_key" UNIQUE("workspace_id","slug")
);
--> statement-breakpoint
CREATE TABLE "sessions" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"user_id" uuid NOT NULL,
	"token" text NOT NULL,
	"expires_at" timestamp with time zone NOT NULL,
	"active_workspace_id" uuid,
	"impersonated_by" uuid,
	"ip_address" varchar(64),
	"user_agent" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "sessions_token_key" UNIQUE("token")
);
--> statement-breakpoint
CREATE TABLE "users" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"email" varchar(320) NOT NULL,
	"email_verified" boolean DEFAULT false NOT NULL,
	"name" text,
	"image" text,
	"is_platform_admin" boolean DEFAULT false NOT NULL,
	"locale" varchar(12) DEFAULT 'en' NOT NULL,
	"timezone" varchar(64) DEFAULT 'UTC' NOT NULL,
	"two_factor_enabled" boolean DEFAULT false NOT NULL,
	"last_seen_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "users_email_key" UNIQUE("email")
);
--> statement-breakpoint
CREATE TABLE "verifications" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"identifier" varchar(320) NOT NULL,
	"value" text NOT NULL,
	"expires_at" timestamp with time zone NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "workspace_members" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"user_id" uuid NOT NULL,
	"role" varchar(16) DEFAULT 'member' NOT NULL,
	"tool_grants" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"invited_by_user_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "workspace_members_key" UNIQUE("workspace_id","user_id")
);
--> statement-breakpoint
CREATE TABLE "workspaces" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"slug" varchar(64) NOT NULL,
	"name" text NOT NULL,
	"logo_url" text,
	"kind" varchar(16) DEFAULT 'personal' NOT NULL,
	"owner_user_id" uuid NOT NULL,
	"ai_enabled" boolean DEFAULT true NOT NULL,
	"settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "workspaces_slug_key" UNIQUE("slug")
);
--> statement-breakpoint
CREATE TABLE "instance_modules" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"key" varchar(64) NOT NULL,
	"kind" varchar(16) DEFAULT 'tool' NOT NULL,
	"version" varchar(32) DEFAULT '0.0.0' NOT NULL,
	"installed" boolean DEFAULT false NOT NULL,
	"enabled" boolean DEFAULT false NOT NULL,
	"config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "instance_modules_key" UNIQUE("key")
);
--> statement-breakpoint
CREATE TABLE "instance_settings" (
	"id" varchar(16) PRIMARY KEY DEFAULT 'singleton' NOT NULL,
	"ai_master_enabled" boolean DEFAULT true NOT NULL,
	"ai_config_version" integer DEFAULT 1 NOT NULL,
	"lifetime_ai_via_credits" boolean DEFAULT false NOT NULL,
	"credit_cost_anchor_micros" integer DEFAULT 10000 NOT NULL,
	"site_name" text DEFAULT 'Mamal' NOT NULL,
	"site_url" text DEFAULT 'http://localhost:3000' NOT NULL,
	"support_email" text,
	"default_locale" varchar(12) DEFAULT 'en' NOT NULL,
	"signups_enabled" boolean DEFAULT true NOT NULL,
	"config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "resource_links" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"from_resource_id" uuid NOT NULL,
	"to_resource_id" uuid NOT NULL,
	"relation" varchar(32) NOT NULL,
	"created_by" varchar(64) DEFAULT 'system' NOT NULL,
	"metadata" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "resource_links_key" UNIQUE("workspace_id","from_resource_id","to_resource_id","relation")
);
--> statement-breakpoint
CREATE TABLE "resources" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"urn" text NOT NULL,
	"tool" varchar(32) NOT NULL,
	"type" varchar(48) NOT NULL,
	"external_id" text NOT NULL,
	"label" text,
	"status" varchar(24) DEFAULT 'active' NOT NULL,
	"attrs" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "resources_urn_key" UNIQUE("workspace_id","urn"),
	CONSTRAINT "resources_external_key" UNIQUE("workspace_id","tool","type","external_id")
);
--> statement-breakpoint
CREATE TABLE "annotations" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"resource_urn" text,
	"occurred_at" timestamp with time zone NOT NULL,
	"kind" varchar(24) DEFAULT 'note' NOT NULL,
	"text" text NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "assets" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"kind" varchar(32) NOT NULL,
	"storage_key" text NOT NULL,
	"bucket" varchar(64) DEFAULT 'default' NOT NULL,
	"filename" text NOT NULL,
	"mime_type" varchar(128),
	"size_bytes" bigint DEFAULT 0 NOT NULL,
	"checksum_sha256" varchar(64),
	"encryption" varchar(24) DEFAULT 'none' NOT NULL,
	"encrypted_dek" text,
	"width" integer,
	"height" integer,
	"duration_ms" integer,
	"source_urn" text,
	"expires_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "contact_list_members" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"list_id" uuid NOT NULL,
	"contact_id" uuid NOT NULL,
	"status" varchar(16) DEFAULT 'subscribed' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "contact_list_members_key" UNIQUE("list_id","contact_id")
);
--> statement-breakpoint
CREATE TABLE "contact_lists" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"name" text NOT NULL,
	"description" text,
	"double_opt_in" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "contacts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"email" varchar(320),
	"phone" varchar(32),
	"name" text,
	"avatar_url" text,
	"source_urn" text,
	"consent" jsonb DEFAULT '{"marketing":false}'::jsonb NOT NULL,
	"attributes" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"first_seen_at" timestamp with time zone DEFAULT now() NOT NULL,
	"last_seen_at" timestamp with time zone DEFAULT now() NOT NULL,
	"unsubscribed_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "contacts_email_key" UNIQUE("workspace_id","email")
);
--> statement-breakpoint
CREATE TABLE "custom_domains" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"host" varchar(253) NOT NULL,
	"kind" varchar(16) DEFAULT 'link' NOT NULL,
	"verification_token" text NOT NULL,
	"dns_status" varchar(16) DEFAULT 'pending' NOT NULL,
	"ssl_status" varchar(16) DEFAULT 'pending' NOT NULL,
	"cf_hostname_id" text,
	"is_primary" boolean DEFAULT false NOT NULL,
	"homepage_url" text,
	"not_found_url" text,
	"verified_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "custom_domains_host_key" UNIQUE("host")
);
--> statement-breakpoint
CREATE TABLE "domain_names" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"apex" varchar(253) NOT NULL,
	"registrar" text,
	"whois" jsonb,
	"nameservers" text[] DEFAULT '{}' NOT NULL,
	"expires_at" timestamp with time zone,
	"ssl_expires_at" timestamp with time zone,
	"last_checked_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "domain_names_key" UNIQUE("workspace_id","apex")
);
--> statement-breakpoint
CREATE TABLE "goals" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"site_id" uuid,
	"key" varchar(64) NOT NULL,
	"name" text NOT NULL,
	"match_kind" varchar(16) DEFAULT 'pageview' NOT NULL,
	"match" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"value_cents" integer DEFAULT 0 NOT NULL,
	"currency" varchar(3) DEFAULT 'USD' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "goals_key" UNIQUE("workspace_id","site_id","key")
);
--> statement-breakpoint
CREATE TABLE "link_rules" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"link_id" uuid NOT NULL,
	"priority" integer DEFAULT 0 NOT NULL,
	"match" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"action" jsonb NOT NULL,
	"sticky" boolean DEFAULT true NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "links" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"custom_domain_id" uuid,
	"folder_id" uuid,
	"kind" varchar(16) DEFAULT 'short' NOT NULL,
	"alias" varchar(255) NOT NULL,
	"destination_url" text,
	"title" text,
	"description" text,
	"image_url" text,
	"password_hash" text,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"expires_at" timestamp with time zone,
	"expires_url" text,
	"max_clicks" integer,
	"tags" text[] DEFAULT '{}' NOT NULL,
	"campaign" varchar(160),
	"settings" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"clicks_count" bigint DEFAULT 0 NOT NULL,
	"last_clicked_at" timestamp with time zone,
	"moderation_status" varchar(16) DEFAULT 'ok' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "links_alias_key" UNIQUE("custom_domain_id","alias")
);
--> statement-breakpoint
CREATE TABLE "notification_channels" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"transport" varchar(24) NOT NULL,
	"name" text NOT NULL,
	"config" text NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"verified_at" timestamp with time zone,
	"failure_count" integer DEFAULT 0 NOT NULL,
	"last_error" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "notification_deliveries" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"channel_id" uuid NOT NULL,
	"template_key" varchar(64) NOT NULL,
	"dedupe_key" varchar(128),
	"event_id" uuid,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"attempts" integer DEFAULT 0 NOT NULL,
	"error" text,
	"sent_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "notification_deliveries_dedupe_key" UNIQUE("channel_id","dedupe_key")
);
--> statement-breakpoint
CREATE TABLE "pixel_attachments" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"pixel_id" uuid NOT NULL,
	"resource_id" uuid NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "pixel_attachments_key" UNIQUE("pixel_id","resource_id")
);
--> statement-breakpoint
CREATE TABLE "pixels" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"provider" varchar(32) NOT NULL,
	"name" text NOT NULL,
	"external_id" text,
	"script" text,
	"consent_required" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "pixels_name_key" UNIQUE("workspace_id","name")
);
--> statement-breakpoint
CREATE TABLE "sites" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"host" varchar(253) NOT NULL,
	"root_url" text NOT NULL,
	"display_name" text,
	"favicon_url" text,
	"verified_at" timestamp with time zone,
	"verification_method" varchar(24),
	"verification_token" text,
	"timezone" varchar(64) DEFAULT 'UTC' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "sites_host_key" UNIQUE("workspace_id","host")
);
--> statement-breakpoint
CREATE TABLE "taggables" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"tag_id" uuid NOT NULL,
	"resource_id" uuid NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "taggables_key" UNIQUE("tag_id","resource_id")
);
--> statement-breakpoint
CREATE TABLE "tags" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"name" varchar(64) NOT NULL,
	"color" varchar(16) DEFAULT 'slate' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "tags_name_key" UNIQUE("workspace_id","name")
);
--> statement-breakpoint
CREATE TABLE "coupon_redemptions" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"coupon_id" uuid NOT NULL,
	"payment_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "coupon_redemptions_key" UNIQUE("coupon_id","workspace_id")
);
--> statement-breakpoint
CREATE TABLE "coupons" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"code" varchar(64) NOT NULL,
	"name" text,
	"kind" varchar(16) DEFAULT 'percent' NOT NULL,
	"value" integer NOT NULL,
	"plan_ids" text[] DEFAULT '{}' NOT NULL,
	"quantity" integer,
	"redeemed" integer DEFAULT 0 NOT NULL,
	"starts_at" timestamp with time zone,
	"ends_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "coupons_code_key" UNIQUE("code")
);
--> statement-breakpoint
CREATE TABLE "credit_buckets" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"source" varchar(24) NOT NULL,
	"source_ref" text,
	"amount" integer NOT NULL,
	"remaining" integer NOT NULL,
	"granted_at" timestamp with time zone DEFAULT now() NOT NULL,
	"expires_at" timestamp with time zone,
	"metadata" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "credit_entries" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"bucket_id" uuid,
	"hold_id" uuid,
	"delta" integer NOT NULL,
	"balance_after" integer NOT NULL,
	"feature_key" varchar(96),
	"resource_urn" text,
	"quantity" integer,
	"unit_cost" integer,
	"idempotency_key" text NOT NULL,
	"actor" varchar(64) DEFAULT 'system' NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "credit_entries_idempotency_key" UNIQUE("idempotency_key")
);
--> statement-breakpoint
CREATE TABLE "credit_holds" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"amount" integer NOT NULL,
	"feature_key" varchar(96) NOT NULL,
	"job_id" text,
	"status" varchar(16) DEFAULT 'held' NOT NULL,
	"bucket_draws" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"expires_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "credit_packs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"key" varchar(64) NOT NULL,
	"name" text NOT NULL,
	"credits" integer NOT NULL,
	"bonus_credits" integer DEFAULT 0 NOT NULL,
	"price_cents" integer NOT NULL,
	"currency" varchar(3) DEFAULT 'USD' NOT NULL,
	"expires_after_days" integer,
	"is_active" boolean DEFAULT true NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "credit_packs_key" UNIQUE("key")
);
--> statement-breakpoint
CREATE TABLE "features" (
	"key" varchar(96) PRIMARY KEY NOT NULL,
	"tool" varchar(32) NOT NULL,
	"name" text NOT NULL,
	"description" text,
	"category" varchar(48),
	"kind" varchar(16) NOT NULL,
	"is_ai" boolean DEFAULT false NOT NULL,
	"free_tier_allowed" boolean DEFAULT false NOT NULL,
	"default_credit_cost" integer DEFAULT 0 NOT NULL,
	"unit" varchar(24),
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "invoices" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"payment_id" uuid,
	"number" varchar(32) NOT NULL,
	"issued_at" timestamp with time zone DEFAULT now() NOT NULL,
	"lines" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"total_cents" integer DEFAULT 0 NOT NULL,
	"currency" varchar(3) DEFAULT 'USD' NOT NULL,
	"pdf_asset_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "invoices_number_key" UNIQUE("number")
);
--> statement-breakpoint
CREATE TABLE "payments" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"subscription_id" uuid,
	"plan_id" uuid,
	"credit_pack_id" uuid,
	"gateway" varchar(32) NOT NULL,
	"gateway_payment_id" text,
	"kind" varchar(16) DEFAULT 'subscription' NOT NULL,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"base_amount_cents" integer DEFAULT 0 NOT NULL,
	"discount_cents" integer DEFAULT 0 NOT NULL,
	"tax_cents" integer DEFAULT 0 NOT NULL,
	"total_cents" integer DEFAULT 0 NOT NULL,
	"currency" varchar(3) DEFAULT 'USD' NOT NULL,
	"billing_details" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"proof_asset_id" uuid,
	"refunded_cents" integer DEFAULT 0 NOT NULL,
	"paid_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "payments_gateway_key" UNIQUE("gateway","gateway_payment_id")
);
--> statement-breakpoint
CREATE TABLE "plan_credit_grants" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"plan_id" uuid NOT NULL,
	"amount" integer NOT NULL,
	"cadence" varchar(16) DEFAULT 'per_period' NOT NULL,
	"expires_after_days" integer,
	"rollover" boolean DEFAULT false NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "plan_credit_grants_key" UNIQUE("plan_id","cadence")
);
--> statement-breakpoint
CREATE TABLE "plan_entitlements" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"plan_id" uuid NOT NULL,
	"feature_key" varchar(96) NOT NULL,
	"mode" varchar(16) NOT NULL,
	"limit_value" bigint,
	"quota_value" bigint,
	"quota_period" varchar(16) DEFAULT 'month',
	"credit_cost" integer,
	"overage" varchar(16) DEFAULT 'block' NOT NULL,
	"config" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"effective_from" timestamp with time zone DEFAULT now() NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "plan_entitlements_key" UNIQUE("plan_id","feature_key","effective_from")
);
--> statement-breakpoint
CREATE TABLE "plan_prices" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"plan_id" uuid NOT NULL,
	"interval" varchar(16) NOT NULL,
	"currency" varchar(3) DEFAULT 'USD' NOT NULL,
	"amount_cents" integer NOT NULL,
	"gateway_price_ids" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"is_active" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "plan_prices_key" UNIQUE("plan_id","interval","currency")
);
--> statement-breakpoint
CREATE TABLE "plans" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"key" varchar(64) NOT NULL,
	"name" text NOT NULL,
	"description" text,
	"kind" varchar(16) NOT NULL,
	"tool" varchar(32),
	"tier_rank" integer DEFAULT 0 NOT NULL,
	"status" varchar(16) DEFAULT 'active' NOT NULL,
	"is_public" boolean DEFAULT true NOT NULL,
	"is_default_signup" boolean DEFAULT false NOT NULL,
	"trial_days" integer DEFAULT 0 NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"marketing" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	"deleted_at" timestamp with time zone,
	CONSTRAINT "plans_key" UNIQUE("key")
);
--> statement-breakpoint
CREATE TABLE "subscriptions" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"plan_id" uuid NOT NULL,
	"plan_snapshot" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"status" varchar(16) DEFAULT 'active' NOT NULL,
	"interval" varchar(16) DEFAULT 'month' NOT NULL,
	"gateway" varchar(32),
	"gateway_subscription_id" text,
	"current_period_start" timestamp with time zone,
	"current_period_end" timestamp with time zone,
	"trial_ends_at" timestamp with time zone,
	"cancel_at" timestamp with time zone,
	"seats" integer DEFAULT 1 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "subscriptions_gateway_key" UNIQUE("gateway","gateway_subscription_id")
);
--> statement-breakpoint
CREATE TABLE "tax_rates" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"name" text NOT NULL,
	"country_code" varchar(2),
	"region" varchar(64),
	"percent" integer NOT NULL,
	"inclusive" boolean DEFAULT false NOT NULL,
	"is_active" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "usage_counters" (
	"workspace_id" uuid NOT NULL,
	"feature_key" varchar(96) NOT NULL,
	"period_start" timestamp with time zone NOT NULL,
	"used" bigint DEFAULT 0 NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "usage_counters_pk" UNIQUE("workspace_id","feature_key","period_start")
);
--> statement-breakpoint
CREATE TABLE "ai_credentials" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"scope" varchar(16) NOT NULL,
	"scope_id" uuid,
	"provider_key" varchar(32) NOT NULL,
	"encrypted_key" text NOT NULL,
	"key_hint" varchar(16) NOT NULL,
	"is_active" boolean DEFAULT true NOT NULL,
	"verified_at" timestamp with time zone,
	"last_error" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "ai_credentials_key" UNIQUE("scope","scope_id","provider_key")
);
--> statement-breakpoint
CREATE TABLE "ai_feature_state" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"scope" varchar(16) NOT NULL,
	"scope_id" uuid,
	"feature_key" varchar(96) NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"model_id" uuid,
	"credential_id" uuid,
	"monthly_credit_cap" integer,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "ai_feature_state_key" UNIQUE("scope","scope_id","feature_key")
);
--> statement-breakpoint
CREATE TABLE "ai_features" (
	"key" varchar(96) PRIMARY KEY NOT NULL,
	"tool" varchar(32) NOT NULL,
	"name" text NOT NULL,
	"description" text,
	"modality" varchar(16) NOT NULL,
	"default_model_id" uuid,
	"fallback_model_id" uuid,
	"prompt_key" varchar(96),
	"is_enabled_default" boolean DEFAULT true NOT NULL,
	"credit_cost_override" integer,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ai_generations" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"feature_key" varchar(96) NOT NULL,
	"model_id" uuid,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"input" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"output_asset_id" uuid,
	"output_text" text,
	"input_tokens" integer DEFAULT 0 NOT NULL,
	"output_tokens" integer DEFAULT 0 NOT NULL,
	"units" integer DEFAULT 0 NOT NULL,
	"vendor_cost_micros" bigint DEFAULT 0 NOT NULL,
	"credits_charged" integer DEFAULT 0 NOT NULL,
	"hold_id" uuid,
	"byo_key" boolean DEFAULT false NOT NULL,
	"external_task_id" text,
	"poll_count" integer DEFAULT 0 NOT NULL,
	"last_polled_at" timestamp with time zone,
	"latency_ms" integer,
	"error" text,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "ai_models" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"provider_key" varchar(32) NOT NULL,
	"model_id" varchar(128) NOT NULL,
	"label" text NOT NULL,
	"sub_label" text,
	"description" text,
	"modality" varchar(16) NOT NULL,
	"capabilities" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"tier" varchar(16) DEFAULT 'standard' NOT NULL,
	"credit_cost" integer DEFAULT 1 NOT NULL,
	"cost_unit" varchar(24) DEFAULT 'call' NOT NULL,
	"vendor_cost_micros" bigint DEFAULT 0 NOT NULL,
	"icon_svg" text,
	"tint" varchar(16),
	"is_recommended" boolean DEFAULT false NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "ai_models_key" UNIQUE("provider_key","model_id")
);
--> statement-breakpoint
CREATE TABLE "ai_prompts" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"key" varchar(96) NOT NULL,
	"version" integer DEFAULT 1 NOT NULL,
	"system" text,
	"user_template" text NOT NULL,
	"variables" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"model_hint" varchar(128),
	"is_active" boolean DEFAULT true NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "ai_prompts_key" UNIQUE("key","version")
);
--> statement-breakpoint
CREATE TABLE "ai_providers" (
	"key" varchar(32) PRIMARY KEY NOT NULL,
	"label" text NOT NULL,
	"driver" varchar(64) NOT NULL,
	"credential_field" varchar(64) NOT NULL,
	"base_url" text,
	"auth_style" varchar(24) DEFAULT 'bearer' NOT NULL,
	"is_enabled" boolean DEFAULT true NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "bus_dead_letters" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid,
	"handler_key" varchar(128) NOT NULL,
	"event_id" uuid NOT NULL,
	"envelope" jsonb NOT NULL,
	"error" text,
	"attempts" integer DEFAULT 0 NOT NULL,
	"replayed_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "bus_deliveries" (
	"handler_key" varchar(128) NOT NULL,
	"event_id" uuid NOT NULL,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"attempts" integer DEFAULT 0 NOT NULL,
	"first_seen_at" timestamp with time zone DEFAULT now() NOT NULL,
	"completed_at" timestamp with time zone,
	"error" text,
	CONSTRAINT "bus_deliveries_pk" UNIQUE("handler_key","event_id")
);
--> statement-breakpoint
CREATE TABLE "event_outbox" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"name" varchar(96) NOT NULL,
	"envelope" jsonb NOT NULL,
	"status" varchar(16) DEFAULT 'pending' NOT NULL,
	"attempts" integer DEFAULT 0 NOT NULL,
	"published_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "job_dead_letters" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid,
	"queue" varchar(64) NOT NULL,
	"job_name" varchar(96) NOT NULL,
	"payload" jsonb NOT NULL,
	"error" text,
	"attempts" integer DEFAULT 0 NOT NULL,
	"replayed_at" timestamp with time zone,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "automation_runs" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"automation_id" uuid NOT NULL,
	"event_id" uuid,
	"status" varchar(16) DEFAULT 'running' NOT NULL,
	"started_at" timestamp with time zone DEFAULT now() NOT NULL,
	"finished_at" timestamp with time zone,
	"steps" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"credits_spent" integer DEFAULT 0 NOT NULL,
	"error" text
);
--> statement-breakpoint
CREATE TABLE "automation_templates" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"key" varchar(64) NOT NULL,
	"name" text NOT NULL,
	"description" text,
	"category" varchar(32),
	"required_tools" text[] DEFAULT '{}' NOT NULL,
	"definition" jsonb NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL,
	CONSTRAINT "automation_templates_key" UNIQUE("key")
);
--> statement-breakpoint
CREATE TABLE "automations" (
	"id" uuid PRIMARY KEY DEFAULT uuidv7() NOT NULL,
	"workspace_id" uuid NOT NULL,
	"project_id" uuid NOT NULL,
	"template_key" varchar(64),
	"name" text NOT NULL,
	"description" text,
	"enabled" boolean DEFAULT true NOT NULL,
	"trigger_event" varchar(96) NOT NULL,
	"trigger_filter" jsonb DEFAULT '{}'::jsonb NOT NULL,
	"conditions" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"actions" jsonb DEFAULT '[]'::jsonb NOT NULL,
	"run_limit_per_hour" integer DEFAULT 1000 NOT NULL,
	"last_run_at" timestamp with time zone,
	"version" integer DEFAULT 1 NOT NULL,
	"created_by_user_id" uuid,
	"created_at" timestamp with time zone DEFAULT now() NOT NULL,
	"updated_at" timestamp with time zone DEFAULT now() NOT NULL
);
--> statement-breakpoint
ALTER TABLE "accounts" ADD CONSTRAINT "accounts_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "api_keys" ADD CONSTRAINT "api_keys_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "api_keys" ADD CONSTRAINT "api_keys_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_logs" ADD CONSTRAINT "audit_logs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_logs" ADD CONSTRAINT "audit_logs_actor_user_id_users_id_fk" FOREIGN KEY ("actor_user_id") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "audit_logs" ADD CONSTRAINT "audit_logs_actor_api_key_id_api_keys_id_fk" FOREIGN KEY ("actor_api_key_id") REFERENCES "public"."api_keys"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "invitations" ADD CONSTRAINT "invitations_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "invitations" ADD CONSTRAINT "invitations_inviter_user_id_users_id_fk" FOREIGN KEY ("inviter_user_id") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "oauth_connections" ADD CONSTRAINT "oauth_connections_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "project_members" ADD CONSTRAINT "project_members_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "project_members" ADD CONSTRAINT "project_members_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "project_members" ADD CONSTRAINT "project_members_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "projects" ADD CONSTRAINT "projects_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "sessions" ADD CONSTRAINT "sessions_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "sessions" ADD CONSTRAINT "sessions_impersonated_by_users_id_fk" FOREIGN KEY ("impersonated_by") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "workspace_members" ADD CONSTRAINT "workspace_members_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "workspace_members" ADD CONSTRAINT "workspace_members_user_id_users_id_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "workspace_members" ADD CONSTRAINT "workspace_members_invited_by_user_id_users_id_fk" FOREIGN KEY ("invited_by_user_id") REFERENCES "public"."users"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "workspaces" ADD CONSTRAINT "workspaces_owner_user_id_users_id_fk" FOREIGN KEY ("owner_user_id") REFERENCES "public"."users"("id") ON DELETE restrict ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "resource_links" ADD CONSTRAINT "resource_links_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "resource_links" ADD CONSTRAINT "resource_links_from_resource_id_resources_id_fk" FOREIGN KEY ("from_resource_id") REFERENCES "public"."resources"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "resource_links" ADD CONSTRAINT "resource_links_to_resource_id_resources_id_fk" FOREIGN KEY ("to_resource_id") REFERENCES "public"."resources"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "resources" ADD CONSTRAINT "resources_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "resources" ADD CONSTRAINT "resources_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "annotations" ADD CONSTRAINT "annotations_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "annotations" ADD CONSTRAINT "annotations_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "assets" ADD CONSTRAINT "assets_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "assets" ADD CONSTRAINT "assets_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contact_list_members" ADD CONSTRAINT "contact_list_members_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contact_list_members" ADD CONSTRAINT "contact_list_members_list_id_contact_lists_id_fk" FOREIGN KEY ("list_id") REFERENCES "public"."contact_lists"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contact_list_members" ADD CONSTRAINT "contact_list_members_contact_id_contacts_id_fk" FOREIGN KEY ("contact_id") REFERENCES "public"."contacts"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contact_lists" ADD CONSTRAINT "contact_lists_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contact_lists" ADD CONSTRAINT "contact_lists_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contacts" ADD CONSTRAINT "contacts_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "contacts" ADD CONSTRAINT "contacts_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "custom_domains" ADD CONSTRAINT "custom_domains_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "custom_domains" ADD CONSTRAINT "custom_domains_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "domain_names" ADD CONSTRAINT "domain_names_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "domain_names" ADD CONSTRAINT "domain_names_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "goals" ADD CONSTRAINT "goals_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "goals" ADD CONSTRAINT "goals_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "goals" ADD CONSTRAINT "goals_site_id_sites_id_fk" FOREIGN KEY ("site_id") REFERENCES "public"."sites"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_rules" ADD CONSTRAINT "link_rules_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "link_rules" ADD CONSTRAINT "link_rules_link_id_links_id_fk" FOREIGN KEY ("link_id") REFERENCES "public"."links"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "links" ADD CONSTRAINT "links_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "links" ADD CONSTRAINT "links_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "links" ADD CONSTRAINT "links_custom_domain_id_custom_domains_id_fk" FOREIGN KEY ("custom_domain_id") REFERENCES "public"."custom_domains"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "notification_channels" ADD CONSTRAINT "notification_channels_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "notification_channels" ADD CONSTRAINT "notification_channels_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "notification_deliveries" ADD CONSTRAINT "notification_deliveries_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "notification_deliveries" ADD CONSTRAINT "notification_deliveries_channel_id_notification_channels_id_fk" FOREIGN KEY ("channel_id") REFERENCES "public"."notification_channels"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "pixel_attachments" ADD CONSTRAINT "pixel_attachments_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "pixel_attachments" ADD CONSTRAINT "pixel_attachments_pixel_id_pixels_id_fk" FOREIGN KEY ("pixel_id") REFERENCES "public"."pixels"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "pixels" ADD CONSTRAINT "pixels_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "pixels" ADD CONSTRAINT "pixels_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "sites" ADD CONSTRAINT "sites_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "sites" ADD CONSTRAINT "sites_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "taggables" ADD CONSTRAINT "taggables_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "taggables" ADD CONSTRAINT "taggables_tag_id_tags_id_fk" FOREIGN KEY ("tag_id") REFERENCES "public"."tags"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "tags" ADD CONSTRAINT "tags_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "coupon_redemptions" ADD CONSTRAINT "coupon_redemptions_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "coupon_redemptions" ADD CONSTRAINT "coupon_redemptions_coupon_id_coupons_id_fk" FOREIGN KEY ("coupon_id") REFERENCES "public"."coupons"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "coupon_redemptions" ADD CONSTRAINT "coupon_redemptions_payment_id_payments_id_fk" FOREIGN KEY ("payment_id") REFERENCES "public"."payments"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "credit_buckets" ADD CONSTRAINT "credit_buckets_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "credit_entries" ADD CONSTRAINT "credit_entries_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "credit_entries" ADD CONSTRAINT "credit_entries_bucket_id_credit_buckets_id_fk" FOREIGN KEY ("bucket_id") REFERENCES "public"."credit_buckets"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "credit_entries" ADD CONSTRAINT "credit_entries_hold_id_credit_holds_id_fk" FOREIGN KEY ("hold_id") REFERENCES "public"."credit_holds"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "credit_holds" ADD CONSTRAINT "credit_holds_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "invoices" ADD CONSTRAINT "invoices_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "invoices" ADD CONSTRAINT "invoices_payment_id_payments_id_fk" FOREIGN KEY ("payment_id") REFERENCES "public"."payments"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_subscription_id_subscriptions_id_fk" FOREIGN KEY ("subscription_id") REFERENCES "public"."subscriptions"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_plan_id_plans_id_fk" FOREIGN KEY ("plan_id") REFERENCES "public"."plans"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "payments" ADD CONSTRAINT "payments_credit_pack_id_credit_packs_id_fk" FOREIGN KEY ("credit_pack_id") REFERENCES "public"."credit_packs"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "plan_credit_grants" ADD CONSTRAINT "plan_credit_grants_plan_id_plans_id_fk" FOREIGN KEY ("plan_id") REFERENCES "public"."plans"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "plan_entitlements" ADD CONSTRAINT "plan_entitlements_plan_id_plans_id_fk" FOREIGN KEY ("plan_id") REFERENCES "public"."plans"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "plan_entitlements" ADD CONSTRAINT "plan_entitlements_feature_key_features_key_fk" FOREIGN KEY ("feature_key") REFERENCES "public"."features"("key") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "plan_prices" ADD CONSTRAINT "plan_prices_plan_id_plans_id_fk" FOREIGN KEY ("plan_id") REFERENCES "public"."plans"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "subscriptions" ADD CONSTRAINT "subscriptions_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "subscriptions" ADD CONSTRAINT "subscriptions_plan_id_plans_id_fk" FOREIGN KEY ("plan_id") REFERENCES "public"."plans"("id") ON DELETE restrict ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "usage_counters" ADD CONSTRAINT "usage_counters_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_credentials" ADD CONSTRAINT "ai_credentials_provider_key_ai_providers_key_fk" FOREIGN KEY ("provider_key") REFERENCES "public"."ai_providers"("key") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_feature_state" ADD CONSTRAINT "ai_feature_state_feature_key_ai_features_key_fk" FOREIGN KEY ("feature_key") REFERENCES "public"."ai_features"("key") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_feature_state" ADD CONSTRAINT "ai_feature_state_model_id_ai_models_id_fk" FOREIGN KEY ("model_id") REFERENCES "public"."ai_models"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_features" ADD CONSTRAINT "ai_features_default_model_id_ai_models_id_fk" FOREIGN KEY ("default_model_id") REFERENCES "public"."ai_models"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_features" ADD CONSTRAINT "ai_features_fallback_model_id_ai_models_id_fk" FOREIGN KEY ("fallback_model_id") REFERENCES "public"."ai_models"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_generations" ADD CONSTRAINT "ai_generations_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_generations" ADD CONSTRAINT "ai_generations_model_id_ai_models_id_fk" FOREIGN KEY ("model_id") REFERENCES "public"."ai_models"("id") ON DELETE set null ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "ai_models" ADD CONSTRAINT "ai_models_provider_key_ai_providers_key_fk" FOREIGN KEY ("provider_key") REFERENCES "public"."ai_providers"("key") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "event_outbox" ADD CONSTRAINT "event_outbox_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "automation_runs" ADD CONSTRAINT "automation_runs_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "automation_runs" ADD CONSTRAINT "automation_runs_automation_id_automations_id_fk" FOREIGN KEY ("automation_id") REFERENCES "public"."automations"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "automations" ADD CONSTRAINT "automations_workspace_id_workspaces_id_fk" FOREIGN KEY ("workspace_id") REFERENCES "public"."workspaces"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
ALTER TABLE "automations" ADD CONSTRAINT "automations_project_id_projects_id_fk" FOREIGN KEY ("project_id") REFERENCES "public"."projects"("id") ON DELETE cascade ON UPDATE no action;--> statement-breakpoint
CREATE INDEX "api_keys_workspace_idx" ON "api_keys" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "audit_logs_workspace_idx" ON "audit_logs" USING btree ("workspace_id","created_at");--> statement-breakpoint
CREATE INDEX "invitations_workspace_idx" ON "invitations" USING btree ("workspace_id","email");--> statement-breakpoint
CREATE INDEX "oauth_connections_workspace_idx" ON "oauth_connections" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "projects_workspace_idx" ON "projects" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "sessions_user_idx" ON "sessions" USING btree ("user_id");--> statement-breakpoint
CREATE INDEX "verifications_identifier_idx" ON "verifications" USING btree ("identifier");--> statement-breakpoint
CREATE INDEX "workspace_members_user_idx" ON "workspace_members" USING btree ("user_id");--> statement-breakpoint
CREATE INDEX "workspaces_owner_idx" ON "workspaces" USING btree ("owner_user_id");--> statement-breakpoint
CREATE INDEX "resource_links_from_idx" ON "resource_links" USING btree ("from_resource_id","relation");--> statement-breakpoint
CREATE INDEX "resource_links_to_idx" ON "resource_links" USING btree ("to_resource_id","relation");--> statement-breakpoint
CREATE INDEX "resources_type_idx" ON "resources" USING btree ("workspace_id","type","status");--> statement-breakpoint
CREATE INDEX "resources_project_idx" ON "resources" USING btree ("project_id");--> statement-breakpoint
CREATE INDEX "annotations_workspace_idx" ON "annotations" USING btree ("workspace_id","occurred_at");--> statement-breakpoint
CREATE INDEX "assets_workspace_idx" ON "assets" USING btree ("workspace_id","kind");--> statement-breakpoint
CREATE INDEX "assets_checksum_idx" ON "assets" USING btree ("workspace_id","checksum_sha256");--> statement-breakpoint
CREATE INDEX "contact_lists_workspace_idx" ON "contact_lists" USING btree ("workspace_id");--> statement-breakpoint
CREATE INDEX "contacts_project_idx" ON "contacts" USING btree ("project_id");--> statement-breakpoint
CREATE INDEX "custom_domains_workspace_idx" ON "custom_domains" USING btree ("workspace_id","kind");--> statement-breakpoint
CREATE INDEX "link_rules_link_idx" ON "link_rules" USING btree ("link_id","priority");--> statement-breakpoint
CREATE INDEX "links_workspace_idx" ON "links" USING btree ("workspace_id","kind");--> statement-breakpoint
CREATE INDEX "links_project_idx" ON "links" USING btree ("project_id");--> statement-breakpoint
CREATE INDEX "notification_channels_workspace_idx" ON "notification_channels" USING btree ("workspace_id","transport");--> statement-breakpoint
CREATE INDEX "notification_deliveries_workspace_idx" ON "notification_deliveries" USING btree ("workspace_id","created_at");--> statement-breakpoint
CREATE INDEX "sites_project_idx" ON "sites" USING btree ("project_id");--> statement-breakpoint
CREATE INDEX "credit_buckets_spend_idx" ON "credit_buckets" USING btree ("workspace_id","expires_at","granted_at");--> statement-breakpoint
CREATE INDEX "credit_entries_workspace_idx" ON "credit_entries" USING btree ("workspace_id","created_at");--> statement-breakpoint
CREATE INDEX "credit_holds_workspace_idx" ON "credit_holds" USING btree ("workspace_id","status");--> statement-breakpoint
CREATE INDEX "features_tool_idx" ON "features" USING btree ("tool");--> statement-breakpoint
CREATE INDEX "payments_workspace_idx" ON "payments" USING btree ("workspace_id","created_at");--> statement-breakpoint
CREATE INDEX "plan_entitlements_plan_idx" ON "plan_entitlements" USING btree ("plan_id");--> statement-breakpoint
CREATE INDEX "plans_kind_idx" ON "plans" USING btree ("kind","tool");--> statement-breakpoint
CREATE INDEX "subscriptions_workspace_idx" ON "subscriptions" USING btree ("workspace_id","status");--> statement-breakpoint
CREATE INDEX "tax_rates_country_idx" ON "tax_rates" USING btree ("country_code");--> statement-breakpoint
CREATE INDEX "ai_features_tool_idx" ON "ai_features" USING btree ("tool");--> statement-breakpoint
CREATE INDEX "ai_generations_workspace_idx" ON "ai_generations" USING btree ("workspace_id","created_at");--> statement-breakpoint
CREATE INDEX "ai_generations_status_idx" ON "ai_generations" USING btree ("status","last_polled_at");--> statement-breakpoint
CREATE INDEX "ai_models_modality_idx" ON "ai_models" USING btree ("modality","is_enabled");--> statement-breakpoint
CREATE INDEX "ai_providers_enabled_idx" ON "ai_providers" USING btree ("is_enabled");--> statement-breakpoint
CREATE INDEX "bus_dead_letters_handler_idx" ON "bus_dead_letters" USING btree ("handler_key","created_at");--> statement-breakpoint
CREATE INDEX "bus_deliveries_status_idx" ON "bus_deliveries" USING btree ("status","first_seen_at");--> statement-breakpoint
CREATE INDEX "event_outbox_pending_idx" ON "event_outbox" USING btree ("status","created_at");--> statement-breakpoint
CREATE INDEX "event_outbox_workspace_idx" ON "event_outbox" USING btree ("workspace_id","created_at");--> statement-breakpoint
CREATE INDEX "job_dead_letters_queue_idx" ON "job_dead_letters" USING btree ("queue","created_at");--> statement-breakpoint
CREATE INDEX "automation_runs_automation_idx" ON "automation_runs" USING btree ("automation_id","started_at");--> statement-breakpoint
CREATE INDEX "automations_trigger_idx" ON "automations" USING btree ("workspace_id","trigger_event","enabled");