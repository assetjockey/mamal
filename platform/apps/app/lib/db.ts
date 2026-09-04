/* eslint-disable no-restricted-syntax -- the single audited entry point; see below */
import { unsafeUnscopedDb, type Database } from '@mamal/db';

/**
 * The app's one connection.
 *
 * `unsafeUnscopedDb` is imported here and nowhere else. Callers get a handle
 * they must pass to `withWorkspace()` or `asPlatformAdmin()`, both of which set
 * the RLS GUC — so the lint rule that bans this import everywhere else stays
 * meaningful, and there is exactly one place to audit.
 */
let handle: Database | undefined;

export function db(): Database {
  handle ??= unsafeUnscopedDb();
  return handle;
}
