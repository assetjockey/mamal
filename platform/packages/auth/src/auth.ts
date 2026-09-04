import { betterAuth } from 'better-auth';
import { drizzleAdapter } from 'better-auth/adapters/drizzle';
import { sql } from 'drizzle-orm';
import { asPlatformAdmin, schema, unsafeUnscopedDb, type Database } from '@mamal/db';
import { provisionWorkspace } from './provision.ts';

/**
 * Better Auth, wired to our schema.
 *
 * The one non-default behaviour that matters: a user is provisioned a
 * workspace at creation. A user without one has no RLS scope, so they would
 * sign in successfully and then see nothing at all.
 */
export function createAuth(db: Database = unsafeUnscopedDb()) {
  return betterAuth({
    database: drizzleAdapter(db, {
      provider: 'pg',
      schema: {
        user: schema.users,
        session: schema.sessions,
        account: schema.accounts,
        verification: schema.verifications,
      },
      usePlural: false,
    }),

    secret: process.env.BETTER_AUTH_SECRET ?? 'dev-only-secret-change-me',
    baseURL: process.env.APP_URL ?? 'http://localhost:3000',

    emailAndPassword: {
      enabled: true,
      minPasswordLength: 10,
      // Off in dev so the flow is walkable without an SMTP server; the
      // transactional mailer turns this on.
      requireEmailVerification: false,
    },

    session: {
      expiresIn: 60 * 60 * 24 * 30,
      updateAge: 60 * 60 * 24,
      cookieCache: { enabled: true, maxAge: 60 * 5 },
    },

    user: {
      additionalFields: {
        locale: { type: 'string', defaultValue: 'en', input: false },
        timezone: { type: 'string', defaultValue: 'UTC', input: false },
      },
    },

    advanced: {
      // Our schema defaults every id to uuidv7(); letting Better Auth generate
      // its own would break the time-ordering the rest of the platform relies on.
      database: { generateId: false },
    },

    databaseHooks: {
      user: {
        create: {
          after: async (user) => {
            await asPlatformAdmin(
              (tx) => provisionWorkspace(tx, { id: user.id, email: user.email, name: user.name }),
              { db },
            );
          },
        },
      },
      session: {
        create: {
          before: async (session) => {
            // Land the session in a workspace so the first render has a scope.
            const [row] = await asPlatformAdmin(
              (tx) =>
                tx.execute<{ workspace_id: string }>(sql`
                  select workspace_id from workspace_members
                   where user_id = ${session.userId}
                   order by created_at asc limit 1`),
              { db },
            );
            return {
              data: { ...session, activeWorkspaceId: row?.workspace_id ?? null },
            };
          },
        },
      },
    },
  });
}

export type Auth = ReturnType<typeof createAuth>;
