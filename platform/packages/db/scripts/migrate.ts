import { readdir, readFile } from 'node:fs/promises';
import { join } from 'node:path';
import { sql } from 'drizzle-orm';
import { unsafeUnscopedDb, closeDb } from '../src/client.ts';
import { rlsStatements } from '../src/rls.ts';

const MIGRATIONS_DIR = new URL('../src/migrations/', import.meta.url).pathname;

async function main() {
  const db = unsafeUnscopedDb();

  // uuidv7() is used as the default for every primary key.
  await db.execute(sql`create extension if not exists "pgcrypto"`);
  await db.execute(sql.raw(UUIDV7_FN));

  await db.execute(
    sql`create table if not exists _migrations (
          name text primary key,
          applied_at timestamptz not null default now()
        )`,
  );

  let files: string[] = [];
  try {
    files = (await readdir(MIGRATIONS_DIR)).filter((f) => f.endsWith('.sql')).sort();
  } catch {
    console.log('no migrations directory yet — run `pnpm db:generate` first');
  }

  const applied = new Set(
    (await db.execute<{ name: string }>(sql`select name from _migrations`)).map((r) => r.name),
  );

  for (const file of files) {
    if (applied.has(file)) continue;
    const body = await readFile(join(MIGRATIONS_DIR, file), 'utf8');

    /*
     * One transaction per file, including the bookkeeping row.
     *
     * Statements used to run individually, so a failure halfway through left
     * the earlier tables created and the migration unrecorded — and every
     * subsequent attempt then died on "relation already exists", with the only
     * way out being to hand-drop tables in production. Postgres does
     * transactional DDL; there is no reason not to use it.
     */
    try {
      await db.transaction(async (tx) => {
        // drizzle-kit separates statements with this marker
        for (const stmt of body.split('--> statement-breakpoint')) {
          const trimmed = stmt.trim();
          if (trimmed) await tx.execute(sql.raw(trimmed));
        }
        await tx.execute(sql`insert into _migrations (name) values (${file})`);
      });
    } catch (err) {
      console.error(`\n${file} failed and was rolled back — nothing was applied.\n`);
      throw err;
    }
    console.log(`applied ${file}`);
  }

  // RLS is re-applied after every migration, so a new tenant table is
  // protected the moment it exists.
  for (const stmt of rlsStatements()) await db.execute(sql.raw(stmt));
  console.log(`RLS policies applied`);

  // Lifetime plans can never carry an AI entitlement. Enforcement point 1 of 3.
  await db.execute(sql.raw(LIFETIME_AI_TRIGGER));
  console.log('lifetime/AI constraint installed');

  await closeDb();
}

/** Postgres 16 has no built-in uuidv7(); this is the RFC 9562 layout. */
const UUIDV7_FN = `
create or replace function uuidv7() returns uuid as $$
declare
  unix_ts_ms bytea;
  uuid_bytes bytea;
begin
  unix_ts_ms = substring(int8send((extract(epoch from clock_timestamp()) * 1000)::bigint) from 3);
  uuid_bytes = unix_ts_ms || gen_random_bytes(10);
  uuid_bytes = set_byte(uuid_bytes, 6, (b'0111' || get_byte(uuid_bytes, 6)::bit(4))::bit(8)::int);
  uuid_bytes = set_byte(uuid_bytes, 8, (b'10'   || get_byte(uuid_bytes, 8)::bit(6))::bit(8)::int);
  return encode(uuid_bytes, 'hex')::uuid;
end
$$ language plpgsql volatile;
`;

const LIFETIME_AI_TRIGGER = `
create or replace function assert_lifetime_excludes_ai() returns trigger as $$
declare
  plan_kind text;
  feature_is_ai boolean;
begin
  select kind into plan_kind from plans where id = new.plan_id;
  select is_ai into feature_is_ai from features where key = new.feature_key;
  if plan_kind = 'lifetime' and coalesce(feature_is_ai, false) and new.mode <> 'deny' then
    raise exception
      'lifetime plans cannot grant AI feature % (mode=%). Set mode=deny or use a subscription plan.',
      new.feature_key, new.mode;
  end if;
  return new;
end
$$ language plpgsql;

drop trigger if exists trg_lifetime_excludes_ai on plan_entitlements;
create trigger trg_lifetime_excludes_ai
  before insert or update on plan_entitlements
  for each row execute function assert_lifetime_excludes_ai();
`;

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
