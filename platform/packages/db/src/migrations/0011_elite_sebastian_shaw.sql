ALTER TABLE "links" DROP CONSTRAINT "links_alias_key";--> statement-breakpoint
CREATE UNIQUE INDEX "links_alias_domain_key" ON "links" USING btree ("custom_domain_id","alias") WHERE custom_domain_id is not null and deleted_at is null;--> statement-breakpoint
CREATE UNIQUE INDEX "links_alias_platform_key" ON "links" USING btree ("alias") WHERE custom_domain_id is null and deleted_at is null;