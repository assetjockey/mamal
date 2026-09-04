import { createAuth } from '@mamal/auth';
import { db } from './db';

/** One Better Auth instance for the app, sharing the audited DB handle. */
export const auth = createAuth(db());
