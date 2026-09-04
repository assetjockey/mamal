'use server';

import { revalidatePath } from 'next/cache';
import { sql } from 'drizzle-orm';
import { db } from '@/lib/db';
import { asPlatformAdmin } from '@mamal/db';
import { cancelInFlight } from '@mamal/ai';
import { getSession } from '@/lib/session';

/**
 * Dev-only controls so the resolver can be exercised from the UI.
 *
 * Real subscriptions arrive through the billing webhook; these write the same
 * rows that webhook will, which is why the page below is a genuine test of the
 * resolver rather than a mock.
 */

export async function toggleSubscription(planKey: string) {
  const session = await getSession();
  if (!session) throw new Error('not signed in');
  const ws = session.workspace;
  const database = db();

  await asPlatformAdmin(async (tx) => {
    const [existing] = await tx.execute<{ id: string }>(sql`
      select s.id from subscriptions s
        join plans p on p.id = s.plan_id
       where s.workspace_id = ${ws.id} and p.key = ${planKey} and s.status = 'active'`);

    if (existing) {
      await tx.execute(sql`delete from subscriptions where id = ${existing.id}`);
    } else {
      await tx.execute(sql`
        insert into subscriptions (workspace_id, plan_id, status, interval)
        select ${ws.id}, id, 'active', 'month' from plans where key = ${planKey}`);
    }
  }, { db: database });

  revalidatePath('/settings/entitlements');
  revalidatePath('/');
}

export async function grantCredits(amount: number) {
  const session = await getSession();
  if (!session) throw new Error('not signed in');
  const ws = session.workspace;
  const database = db();
  await asPlatformAdmin(
    (tx) =>
      tx.execute(sql`
        insert into credit_buckets (workspace_id, source, amount, remaining)
        values (${ws.id}, 'admin', ${amount}, ${amount})`),
    { db: database },
  );
  revalidatePath('/settings/entitlements');
  revalidatePath('/');
}

export async function resetWorkspace() {
  const session = await getSession();
  if (!session) throw new Error('not signed in');
  const ws = session.workspace;
  const database = db();
  await asPlatformAdmin(async (tx) => {
    await tx.execute(sql`delete from subscriptions where workspace_id = ${ws.id}`);
    await tx.execute(sql`delete from credit_buckets where workspace_id = ${ws.id}`);
    await tx.execute(sql`update instance_settings set ai_master_enabled = true`);
    await tx.execute(sql`update workspaces set ai_enabled = true where id = ${ws.id}`);
  }, { db: database });
  revalidatePath('/settings/entitlements');
  revalidatePath('/');
}

/**
 * The instance-wide AI kill switch.
 *
 * It is a kill, not a hide: turning AI off cancels every in-flight generation
 * and releases its credit hold, so nobody is billed for work that will never
 * finish. Bumping ai_config_version makes the change take effect in under five
 * seconds rather than after a cache TTL.
 */
export async function toggleAiMaster() {
  const database = db();
  const [row] = await asPlatformAdmin(
    (tx) =>
      tx.execute<{ ai_master_enabled: boolean }>(sql`
        update instance_settings
           set ai_master_enabled = not ai_master_enabled,
               ai_config_version = ai_config_version + 1
        returning ai_master_enabled`),
    { db: database },
  );

  if (row && !row.ai_master_enabled) {
    const cancelled: number = await asPlatformAdmin((tx) => cancelInFlight(tx), { db: database });
    if (cancelled > 0) console.info(`AI disabled: cancelled ${cancelled} in-flight generation(s)`);
  }
  revalidatePath('/settings/entitlements');
}

/** The tenant's own opt-out, distinct from the instance switch. */
export async function toggleAiTenant() {
  const session = await getSession();
  if (!session) throw new Error('not signed in');
  const ws = session.workspace;
  const database = db();
  await asPlatformAdmin(
    (tx) => tx.execute(sql`update workspaces set ai_enabled = not ai_enabled where id = ${ws.id}`),
    { db: database },
  );
  revalidatePath('/settings/entitlements');
}
