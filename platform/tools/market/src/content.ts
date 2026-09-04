/**
 * Documents, briefs and the destinations they publish to.
 *
 * The scoring lives in `content-score.ts` and is pure; this is the part that
 * touches the database. Two rules run through it:
 *
 * **The score is recomputed on save, never trusted from the client.** The
 * editor renders a live score as you type for responsiveness, but the number
 * stored — the one that drives the list, the filters and any automation — is
 * computed here from the body that was actually saved. Otherwise a stale or
 * crafted client value decides what "ready to publish" means.
 *
 * **A slug is a promise.** Once a doc is published its slug is its URL and
 * somebody has linked to it, so changing it silently is a broken link. The
 * change is allowed but the caller is told what it costs.
 */
import { sql } from 'drizzle-orm';
import { textArray, type WorkspaceScopedDb } from '@mamal/db';
import { loadContext, resolve as resolveEntitlement } from '@mamal/entitlements';
import { mint } from '@mamal/resources';
import { scoreContent, type Brief, type ContentScore } from './content-score.ts';
import { MarketNotAllowed } from './service.ts';

export type DocRow = {
  id: string;
  title: string;
  slug: string | null;
  status: string;
  body: string;
  targetKeywords: string[];
  seoScore: number | null;
  readability: number | null;
  wordCount: number;
  meta: Record<string, unknown>;
  publishedAt: string | null;
  updatedAt: string;
};

/* -------------------------------------------------------------- documents */

export async function createDoc(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    title: string;
    targetKeywords?: string[];
    body?: string;
  },
): Promise<string> {
  const title = opts.title.trim();
  if (title.length === 0) throw new MarketNotAllowed('invalid', 'A document needs a title.');

  await requireHeadroom(
    tx,
    opts.workspaceId,
    'market.content_docs',
    sql`select count(*)::int as count from content_docs
         where project_id = ${opts.projectId} and deleted_at is null`,
  );

  const body = opts.body ?? '';
  const scored = scoreContent(
    { title, body, targetKeywords: opts.targetKeywords ?? [] },
  );

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into content_docs
      (workspace_id, project_id, title, slug, body, target_keywords,
       seo_score, readability, word_count)
    values (${opts.workspaceId}, ${opts.projectId}, ${title}, ${slugify(title)},
            ${body}, ${textArray(opts.targetKeywords ?? [])}::text[],
            ${scored.score}, ${scored.readability}, ${scored.wordCount})
    -- Two drafts can share a title; the slug is what must be unique, and a
    -- collision gets a suffix rather than a refusal the writer cannot act on.
    on conflict on constraint content_docs_slug_key do update
       set slug = ${slugify(title)} || '-' || substr(md5(random()::text), 1, 6)
    returning id`);

  await mint(tx, {
    workspaceId: opts.workspaceId,
    projectId: opts.projectId,
    tool: 'market',
    type: 'content_doc',
    externalId: row!.id,
    label: title,
  });

  return row!.id;
}

export type SaveResult = {
  score: ContentScore;
  /** Set when the change breaks an address somebody may already have. */
  warning: string | null;
};

export async function saveDoc(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    docId: string;
    title?: string;
    body?: string;
    slug?: string;
    metaDescription?: string;
    targetKeywords?: string[];
    status?: 'draft' | 'in_review' | 'approved' | 'published' | 'archived';
  },
): Promise<SaveResult> {
  const [current] = await tx.execute<{
    title: string; body: string; slug: string | null; status: string;
    target_keywords: string[]; meta: Record<string, unknown>;
  }>(sql`
    select title, body, slug, status, target_keywords, meta from content_docs
     where id = ${opts.docId} and project_id = ${opts.projectId} and deleted_at is null`);

  if (!current) throw new MarketNotAllowed('not_found', 'That document no longer exists.');

  const title = opts.title ?? current.title;
  const body = opts.body ?? current.body;
  const keywords = opts.targetKeywords ?? current.target_keywords;
  const meta = { ...current.meta };
  if (opts.metaDescription !== undefined) meta.description = opts.metaDescription;

  const [brief] = await tx.execute<{
    entities: string[]; questions: string[]; target_word_count: number | null;
  }>(sql`
    select entities, questions, target_word_count from content_briefs
     where doc_id = ${opts.docId}`);

  /*
   * Scored here rather than taken from the request. The editor computes the
   * same number locally so typing stays instant, but the stored score decides
   * what appears "ready" in the list and what an automation acts on — that
   * cannot come from the client.
   */
  const score = scoreContent(
    {
      title,
      body,
      targetKeywords: keywords,
      metaDescription: typeof meta.description === 'string' ? meta.description : undefined,
    },
    brief
      ? {
          entities: brief.entities,
          questions: brief.questions,
          targetWordCount: brief.target_word_count,
        }
      : {},
  );

  let warning: string | null = null;
  const slug = opts.slug === undefined ? current.slug : slugify(opts.slug);
  if (slug !== current.slug && current.status === 'published') {
    // Allowed, because sometimes it is genuinely the right call — but never
    // silently, because the old address is what people have.
    warning =
      `This page is published at /${current.slug}. Changing the slug breaks that ` +
      'address for anyone who has linked to or bookmarked it — set up a redirect.';
  }

  const publishing = opts.status === 'published' && current.status !== 'published';

  await tx.execute(sql`
    update content_docs
       set title = ${title},
           body = ${body},
           slug = ${slug},
           meta = ${JSON.stringify(meta)}::jsonb,
           target_keywords = ${textArray(keywords)}::text[],
           status = ${opts.status ?? current.status},
           seo_score = ${score.score},
           readability = ${score.readability},
           word_count = ${score.wordCount},
           published_at = ${publishing ? sql`now()` : sql`published_at`},
           updated_at = now()
     where id = ${opts.docId} and project_id = ${opts.projectId}`);

  return { score, warning };
}

export async function deleteDoc(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; docId: string },
): Promise<void> {
  // Soft, so a published URL's history and any pipeline run that produced it
  // still resolve to something.
  await tx.execute(sql`
    update content_docs set deleted_at = now(), status = 'archived', updated_at = now()
     where id = ${opts.docId} and project_id = ${opts.projectId}`);
}

export async function listDocs(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; status?: string; limit?: number },
): Promise<DocRow[]> {
  const rows = await tx.execute<{
    id: string; title: string; slug: string | null; status: string; body: string;
    target_keywords: string[]; seo_score: number | null; readability: number | null;
    word_count: number; meta: Record<string, unknown>;
    published_at: string | null; updated_at: string;
  }>(sql`
    select id, title, slug, status, body, target_keywords, seo_score, readability,
           word_count, meta, published_at::text, updated_at::text
      from content_docs
     where project_id = ${opts.projectId} and deleted_at is null
       ${opts.status ? sql`and status = ${opts.status}` : sql``}
     order by updated_at desc
     limit ${opts.limit ?? 100}`);

  return rows.map(toDocRow);
}

export async function getDoc(
  tx: WorkspaceScopedDb,
  opts: { projectId: string; docId: string },
): Promise<{ doc: DocRow; brief: Brief | null; score: ContentScore } | null> {
  const [row] = await tx.execute<{
    id: string; title: string; slug: string | null; status: string; body: string;
    target_keywords: string[]; seo_score: number | null; readability: number | null;
    word_count: number; meta: Record<string, unknown>;
    published_at: string | null; updated_at: string;
  }>(sql`
    select id, title, slug, status, body, target_keywords, seo_score, readability,
           word_count, meta, published_at::text, updated_at::text
      from content_docs
     where id = ${opts.docId} and project_id = ${opts.projectId} and deleted_at is null`);

  if (!row) return null;

  const [briefRow] = await tx.execute<{
    entities: string[]; questions: string[]; target_word_count: number | null;
  }>(sql`
    select entities, questions, target_word_count from content_briefs where doc_id = ${row.id}`);

  const brief: Brief | null = briefRow
    ? {
        entities: briefRow.entities,
        questions: briefRow.questions,
        targetWordCount: briefRow.target_word_count,
      }
    : null;

  const doc = toDocRow(row);
  return {
    doc,
    brief,
    score: scoreContent(
      {
        title: doc.title,
        body: doc.body,
        targetKeywords: doc.targetKeywords,
        metaDescription:
          typeof doc.meta.description === 'string' ? doc.meta.description : undefined,
      },
      brief ?? {},
    ),
  };
}

/* ----------------------------------------------------------------- briefs */

/**
 * The brief a draft is written against.
 *
 * Built from data the customer already has wherever possible — the questions
 * are the "people also ask" style queries their own Search Console shows them
 * ranking for, which costs nothing and works on the free plan. A paid brief
 * adds SERP analysis through DataForSEO, and that is the only part that spends.
 */
export async function saveBrief(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    docId: string;
    entities?: string[];
    questions?: string[];
    targetWordCount?: number | null;
    serpAnalysis?: Record<string, unknown>;
    creditsSpent?: number;
  },
): Promise<void> {
  await tx.execute(sql`
    insert into content_briefs
      (workspace_id, doc_id, entities, questions, target_word_count, serp_analysis, credits_spent)
    values (${opts.workspaceId}, ${opts.docId},
            ${textArray(opts.entities ?? [])}::text[],
            ${textArray(opts.questions ?? [])}::text[],
            ${opts.targetWordCount ?? null},
            ${JSON.stringify(opts.serpAnalysis ?? {})}::jsonb,
            ${opts.creditsSpent ?? 0})
    on conflict on constraint content_briefs_doc do update
       set entities = excluded.entities,
           questions = excluded.questions,
           target_word_count = excluded.target_word_count,
           serp_analysis = excluded.serp_analysis,
           -- Accumulated, not replaced: a brief refreshed twice cost twice, and
           -- the margin report needs the total.
           credits_spent = content_briefs.credits_spent + excluded.credits_spent,
           updated_at = now()`);
}

/**
 * A free brief, from the customer's own Search Console rows.
 *
 * Queries the page (or the keyword) already earns impressions for become the
 * entities to cover and the questions to answer, and the target length is the
 * median of what is already ranking. No vendor call, so this is what a free or
 * lifetime workspace gets — and it is genuinely useful rather than a teaser.
 */
export async function briefFromSearchConsole(
  tx: WorkspaceScopedDb,
  opts: { workspaceId: string; projectId: string; docId: string; keyword: string },
): Promise<{ entities: string[]; questions: string[]; rows: number }> {
  const rows = await tx.execute<{ query: string; impressions: number }>(sql`
    select query, sum(impressions)::int as impressions
      from market_search_performance
     where workspace_id = ${opts.workspaceId}
       and captured_on > current_date - interval '90 days'
       and query ilike ${'%' + opts.keyword.trim() + '%'}
     group by query
     order by impressions desc
     limit 60`);

  // A question is a query somebody phrased as one. Everything else is a topic.
  const questions = rows
    .map((r) => r.query)
    .filter((q) => /^(how|what|why|when|where|which|who|can|does|is|are|should)\b/i.test(q))
    .slice(0, 12);

  const entities = rows
    .map((r) => r.query)
    .filter((q) => !questions.includes(q))
    .slice(0, 20);

  await saveBrief(tx, {
    workspaceId: opts.workspaceId,
    docId: opts.docId,
    entities,
    questions,
    // Nothing to measure against without SERP data, and a made-up target is
    // worse than none: it would fail a good draft for being the wrong length.
    targetWordCount: null,
  });

  return { entities, questions, rows: rows.length };
}

/* ----------------------------------------------------------- destinations */

export const DESTINATION_KINDS = [
  'wordpress', 'shopify', 'woocommerce', 'ghost', 'webhook',
] as const;

export async function saveDestination(
  tx: WorkspaceScopedDb,
  opts: {
    workspaceId: string;
    projectId: string;
    id?: string;
    kind: string;
    name: string;
    config: Record<string, unknown>;
    credentialsEncrypted?: string;
    defaultStatus?: 'draft' | 'publish';
  },
): Promise<string> {
  if (!DESTINATION_KINDS.includes(opts.kind as (typeof DESTINATION_KINDS)[number])) {
    throw new MarketNotAllowed('invalid', `${opts.kind} is not a destination we can publish to.`);
  }

  if (opts.id) {
    await tx.execute(sql`
      update publish_destinations
         set name = ${opts.name},
             config = ${JSON.stringify(opts.config)}::jsonb,
             credentials_encrypted = coalesce(${opts.credentialsEncrypted ?? null},
                                              credentials_encrypted),
             default_status = ${opts.defaultStatus ?? 'draft'},
             updated_at = now()
       where id = ${opts.id} and project_id = ${opts.projectId}`);
    return opts.id;
  }

  await requireHeadroom(
    tx,
    opts.workspaceId,
    'market.publish_destinations',
    sql`select count(*)::int as count from publish_destinations
         where project_id = ${opts.projectId}`,
  );

  const [row] = await tx.execute<{ id: string }>(sql`
    insert into publish_destinations
      (workspace_id, project_id, kind, name, config, credentials_encrypted, default_status)
    values (${opts.workspaceId}, ${opts.projectId}, ${opts.kind}, ${opts.name},
            ${JSON.stringify(opts.config)}::jsonb, ${opts.credentialsEncrypted ?? null},
            ${opts.defaultStatus ?? 'draft'})
    returning id`);

  return row!.id;
}

/* ----------------------------------------------------------------- shared */

/**
 * A URL-safe slug.
 *
 * Accented Latin is folded rather than dropped — "Café ouvert" becoming
 * "caf-ouvert" is the kind of thing nobody notices until the page is live.
 * Non-Latin scripts are left alone: a Japanese title should keep its
 * characters, which URLs have handled for twenty years, rather than becoming an
 * empty string.
 */
export function slugify(input: string): string {
  const folded = input
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .trim();

  const slug = folded
    .replace(/[^\p{L}\p{N}]+/gu, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 120)
    .replace(/-+$/, '');

  return slug.length > 0 ? slug : 'untitled';
}

function toDocRow(row: {
  id: string; title: string; slug: string | null; status: string; body: string;
  target_keywords: string[]; seo_score: number | null; readability: number | null;
  word_count: number; meta: Record<string, unknown>;
  published_at: string | null; updated_at: string;
}): DocRow {
  return {
    id: row.id,
    title: row.title,
    slug: row.slug,
    status: row.status,
    body: row.body,
    targetKeywords: row.target_keywords,
    seoScore: row.seo_score,
    readability: row.readability,
    wordCount: row.word_count,
    meta: row.meta,
    publishedAt: row.published_at,
    updatedAt: row.updated_at,
  };
}

async function requireHeadroom(
  tx: WorkspaceScopedDb,
  workspaceId: string,
  featureKey: string,
  countSql: ReturnType<typeof sql>,
): Promise<void> {
  const ctx = await loadContext(tx, workspaceId, featureKey);
  if (!ctx) throw new Error(`${featureKey} is not a known feature`);
  const [counted] = await tx.execute<{ count: number }>(countSql);
  const decision = resolveEntitlement({ ...ctx, used: counted?.count ?? 0 }, 1);
  if (!decision.allowed) throw new MarketNotAllowed(decision.reason, decision.message);
}
