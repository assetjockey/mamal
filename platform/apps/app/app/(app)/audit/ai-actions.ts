'use server';

import { redirect } from 'next/navigation';
import { withWorkspace } from '@mamal/db';
import { decryptCredential, driverFor } from '@mamal/ai';
import { fixBrief, summariseAudit, type AiResult } from '@mamal/tool-audit';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/**
 * Provider resolution and credential decryption live here, in one place.
 *
 * The features themselves take these as dependencies, so the tool package has
 * no opinion about how a key is stored — and the tests inject a driver that
 * never touches the network.
 */
const deps = { driverFor, decrypt: decryptCredential };

export async function generateSummary(auditId: string): Promise<AiResult<string>> {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  return withWorkspace(
    session.workspace.id,
    (tx) => summariseAudit(tx, { workspaceId: session.workspace.id, auditId }, deps),
    { db: db() },
  );
}

export async function generateFixBrief(issueId: string): Promise<AiResult<string>> {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  return withWorkspace(
    session.workspace.id,
    (tx) => fixBrief(tx, { workspaceId: session.workspace.id, issueId }, deps),
    { db: db() },
  );
}
