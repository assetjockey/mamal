'use server';

import { createHash } from 'node:crypto';
import { headers } from 'next/headers';
import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { isRateLimited, toolBySlug, type ToolOutput } from '@mamal/seo-tools';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/** Fetching tools are limited per IP per hour; pure ones cost nothing to run. */
const HOURLY_LIMIT = 30;

export async function runTool(slug: string, input: Record<string, string>): Promise<ToolOutput> {
  const tool = toolBySlug(slug);
  if (!tool) return { kind: 'error', message: 'Unknown tool.' };

  const session = await getSession();
  const headerList = await headers();
  // Hashed, never stored raw — the limiter needs to count, not to identify.
  const ipHash = createHash('sha256')
    .update(headerList.get('x-forwarded-for') ?? headerList.get('x-real-ip') ?? 'local')
    .digest('hex');

  if (isRateLimited(slug)) {
    const [recent] = await asPlatformAdmin(
      (tx) => tx.execute<{ n: number }>(sql`
        select count(*)::int as n from audit_tool_runs
         where ip_hash = ${ipHash} and created_at > now() - interval '1 hour'`),
      { db: db() },
    );
    if (Number(recent?.n ?? 0) >= HOURLY_LIMIT) {
      return {
        kind: 'error',
        message: `These tools fetch a live page, so they are limited to ${HOURLY_LIMIT} an hour. Try again shortly.`,
      };
    }
  }

  const started = Date.now();
  let output: ToolOutput;
  try {
    output = await tool.run(input);
  } catch (err) {
    output = {
      kind: 'error',
      message: err instanceof Error ? err.message : 'Something went wrong.',
    };
  }

  // Only fetching tools are recorded — the limiter counts these, and a usage
  // row per keystroke-free local computation would be pure noise.
  if (isRateLimited(slug)) {
    await asPlatformAdmin(
      (tx) => tx.execute(sql`
        insert into audit_tool_runs (workspace_id, slug, input, duration_ms, ip_hash)
        values (${session?.workspace.id ?? null}, ${slug},
                ${JSON.stringify(input)}::jsonb, ${Date.now() - started}, ${ipHash})`),
      { db: db() },
    );
  }

  return output;
}
