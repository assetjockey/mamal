import { headers } from 'next/headers';
import { sql } from 'drizzle-orm';
import { asPlatformAdmin } from '@mamal/db';
import { memberOf, workspacesFor } from '@mamal/auth';
import { auth } from './auth';
import { db } from './db';

export type Session = {
  user: { id: string; email: string; name: string | null; image: string | null };
  workspace: {
    id: string;
    slug: string;
    name: string;
    role: string;
    plan: string;
    credits: number;
    /** Entitlement keys, for server-side nav filtering. */
    allowed: string[];
  };
  workspaces: { id: string; slug: string; name: string; role: string }[];
};

/** Null when signed out — callers redirect. */
export async function getSession(): Promise<Session | null> {
  const result = await auth.api.getSession({ headers: await headers() });
  if (!result?.user) return null;

  const database = db();
  const userId = result.user.id;

  const workspaces = await asPlatformAdmin((tx) => workspacesFor(tx, userId), { db: database });
  const activeId =
    (result.session as { activeWorkspaceId?: string | null }).activeWorkspaceId ??
    workspaces[0]?.id;
  const active = workspaces.find((w) => w.id === activeId) ?? workspaces[0];

  // A signed-in user with no workspace has no RLS scope and would see an empty
  // shell. provisionWorkspace runs on user creation, so this only happens to
  // rows created outside the auth flow.
  if (!active) return null;

  const member = await asPlatformAdmin((tx) => memberOf(tx, active.id, userId), { db: database });

  const [plan] = await asPlatformAdmin(
    (tx) => tx.execute<{ name: string }>(sql`
      select p.name from subscriptions s join plans p on p.id = s.plan_id
       where s.workspace_id = ${active.id} and s.status in ('active','trialing')
       order by p.tier_rank desc limit 1`),
    { db: database },
  );

  const [credits] = await asPlatformAdmin(
    (tx) => tx.execute<{ total: number }>(sql`
      select coalesce(sum(remaining),0)::int as total from credit_buckets
       where workspace_id = ${active.id} and remaining > 0
         and (expires_at is null or expires_at > now())`),
    { db: database },
  );

  const allowedRows = await asPlatformAdmin(
    (tx) => tx.execute<{ feature_key: string }>(sql`
      select distinct pe.feature_key
        from plan_entitlements pe
        join plans p on p.id = pe.plan_id
        left join subscriptions s on s.plan_id = p.id and s.workspace_id = ${active.id}
       where pe.mode <> 'deny'
         and (p.key = 'free' or s.status in ('active','trialing'))`),
    { db: database },
  );

  return {
    user: {
      id: userId,
      email: result.user.email,
      name: result.user.name ?? null,
      image: result.user.image ?? null,
    },
    workspace: {
      id: active.id,
      slug: active.slug,
      name: active.name,
      role: member?.role ?? active.role,
      plan: plan?.name ?? 'Free',
      credits: Number(credits?.total ?? 0),
      allowed: allowedRows.map((r) => r.feature_key),
    },
    workspaces,
  };
}
