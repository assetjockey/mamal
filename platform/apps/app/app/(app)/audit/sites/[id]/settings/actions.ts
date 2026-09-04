'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { loadContext, resolve } from '@mamal/entitlements';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

const SCHEDULE_INTERVAL: Record<string, string | null> = {
  manual: null,
  '6h': '6 hours',
  '12h': '12 hours',
  daily: '1 day',
  '3d': '3 days',
  weekly: '7 days',
  '30d': '30 days',
};

export async function saveSettings(auditSiteId: string, form: FormData) {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const schedule = String(form.get('schedule') ?? 'manual');
  if (!(schedule in SCHEDULE_INTERVAL)) {
    return { error: 'Unknown schedule.' };
  }

  // Scheduling is a paid feature; setting one on a free plan would have the
  // worker pick it up and then fail on quota, which is a worse experience than
  // being told now.
  if (schedule !== 'manual') {
    const allowed = await withWorkspace(
      ws,
      async (tx) => {
        const ctx = await loadContext(tx, ws, 'audit.schedule');
        return ctx ? resolve(ctx) : null;
      },
      { db: db() },
    );
    if (!allowed?.allowed) {
      return { error: allowed?.allowed === false ? allowed.message : 'Scheduling is not available on your plan.' };
    }
  }

  const maxPages = Math.max(1, Math.min(50_000, Number(form.get('maxPages') ?? 25)));
  const maxDepth = Math.max(1, Math.min(20, Number(form.get('maxDepth') ?? 5)));
  const respectRobots = form.get('respectRobots') === 'on';
  const excludeRaw = String(form.get('excludePatterns') ?? '').trim();
  const excludePatterns = excludeRaw ? excludeRaw.split('\n').map((s) => s.trim()).filter(Boolean) : [];

  // A malformed pattern would throw inside the crawler, mid-run.
  for (const pattern of excludePatterns) {
    try {
      new RegExp(pattern);
    } catch {
      return { error: `"${pattern}" is not a valid regular expression.` };
    }
  }

  const interval = SCHEDULE_INTERVAL[schedule];

  await withWorkspace(
    ws,
    (tx) => tx.execute(sql`
      update audit_sites
         set schedule = ${schedule},
             crawl_config = crawl_config || ${JSON.stringify({
               maxPages, maxDepth, respectRobots, excludePatterns,
             })}::jsonb,
             next_audit_at = ${interval === null ? sql`null` : sql`now() + ${interval}::interval`},
             updated_at = now()
       where id = ${auditSiteId} and workspace_id = ${ws}`),
    { db: db() },
  );

  revalidatePath(`/audit/sites/${auditSiteId}/settings`);
  revalidatePath('/audit');
  return { ok: true as const };
}
