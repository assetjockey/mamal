import { sql } from 'drizzle-orm';
import type { WorkspaceScopedDb } from '@mamal/db';
import { AiUnavailable, execute, type ExecuteDeps } from '@mamal/ai';
import { ruleById } from '@mamal/seo-checks';

/**
 * Audit's AI features.
 *
 * Every one is additive: the product works without them. The rule catalogue
 * already ships a `why` and a `howToFix` for all 72 checks, so with AI off — or
 * on a lifetime plan, which excludes it structurally — the user still gets
 * specific guidance. AI adds context these cannot: the page's actual markup,
 * this site's actual issue mix.
 *
 * Nothing here reaches a provider directly. `ai.execute` re-resolves
 * entitlements immediately before the call, and the eslint boundary forbids
 * importing a provider SDK outside packages/ai.
 */

export type AiResult<T> =
  | { ok: true; value: T; creditsCharged: number }
  | { ok: false; reason: string; message: string };

/**
 * An executive summary of a run.
 *
 * Deliberately given the counts and the top rules rather than every finding:
 * a 10,000-issue crawl would blow the context window and cost a fortune to
 * say the same thing.
 */
export async function summariseAudit(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; auditId: string },
  deps: ExecuteDeps,
): Promise<AiResult<string>> {
  const [audit] = await tx.execute<{
    host: string; score: number; pages_crawled: number;
    critical_count: number; warning_count: number; info_count: number;
  }>(sql`
    select s.host, a.score, a.pages_crawled, a.critical_count, a.warning_count, a.info_count
      from audits a
      join audit_sites asite on asite.id = a.audit_site_id
      join sites s on s.id = asite.site_id
     where a.id = ${input.auditId} and a.workspace_id = ${input.workspaceId}`);

  if (!audit) return { ok: false, reason: 'not_found', message: 'That audit does not exist.' };

  const top = await tx.execute<{ rule_id: string; title: string; severity: string; n: number }>(sql`
    select i.rule_id, r.title, i.severity, count(*)::int as n
      from audit_issues i join audit_rules r on r.id = i.rule_id
     where i.audit_id = ${input.auditId} and i.status = 'open'
     group by 1, 2, 3
     order by case i.severity when 'critical' then 0 when 'warning' then 1 else 2 end,
              count(*) desc
     limit 12`);

  const prompt = [
    `Website: ${audit.host}`,
    `Score: ${audit.score}/100 across ${audit.pages_crawled} pages crawled`,
    `Findings: ${audit.critical_count} critical, ${audit.warning_count} warnings, ${audit.info_count} informational`,
    '',
    'Top issues, most impactful first:',
    ...top.map((t) => `- ${t.title} (${t.severity}, ${t.n} page${t.n === 1 ? '' : 's'})`),
  ].join('\n');

  return run(tx, {
    workspaceId: input.workspaceId,
    featureKey: 'audit.ai_summary',
    system:
      'You are a technical SEO advising a small team with limited time. Write three short ' +
      'paragraphs: what is actually wrong, what to fix first and why that one, and what can wait. ' +
      'Be specific and plain. No headings, no bullet lists, no preamble.',
    prompt,
    expectedUnits: 1,
  }, deps);
}

/**
 * A fix brief for one issue, using the offending page's real markup.
 *
 * This is what the static `howToFix` cannot do: name the actual title that is
 * too long, or the specific images missing alt text.
 */
export async function fixBrief(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; issueId: string },
  deps: ExecuteDeps,
): Promise<AiResult<string>> {
  const [issue] = await tx.execute<{
    rule_id: string; severity: string; page_url: string | null;
    evidence: Record<string, unknown>; title: string; how_to_fix: string;
    page_title: string | null; meta_description: string | null; word_count: number | null;
  }>(sql`
    select i.rule_id, i.severity, i.page_url, i.evidence,
           r.title, r.how_to_fix,
           p.title as page_title, p.meta_description, p.word_count
      from audit_issues i
      join audit_rules r on r.id = i.rule_id
      left join audit_pages p on p.audit_id = i.audit_id and p.url = i.page_url
     where i.id = ${input.issueId} and i.workspace_id = ${input.workspaceId}`);

  if (!issue) return { ok: false, reason: 'not_found', message: 'That issue does not exist.' };

  const rule = ruleById(issue.rule_id);
  const prompt = [
    `Issue: ${issue.title} (${issue.severity})`,
    issue.page_url ? `Page: ${issue.page_url}` : 'Scope: the whole site',
    `Evidence: ${JSON.stringify(issue.evidence)}`,
    issue.page_title ? `Current title: ${issue.page_title}` : '',
    issue.meta_description ? `Current description: ${issue.meta_description}` : '',
    issue.word_count !== null ? `Word count: ${issue.word_count}` : '',
    '',
    'Standard guidance already shown to the user:',
    rule?.howToFix ?? issue.how_to_fix,
  ].filter(Boolean).join('\n');

  return run(tx, {
    workspaceId: input.workspaceId,
    featureKey: 'audit.ai_fix_brief',
    system:
      'Write the specific fix for THIS page. The user has already read the generic guidance, so ' +
      'do not repeat it — use the actual values given. Where a replacement is obvious (a title, a ' +
      'description) write it out ready to paste. Two short paragraphs at most.',
    prompt,
    expectedUnits: 1,
  }, deps);
}

/** Alt text for images the crawl found without any. */
export async function altTextFor(
  tx: WorkspaceScopedDb,
  input: { workspaceId: string; issueId: string },
  deps: ExecuteDeps,
): Promise<AiResult<string>> {
  const [issue] = await tx.execute<{ page_url: string | null; evidence: { sample?: string[] } }>(sql`
    select page_url, evidence from audit_issues
     where id = ${input.issueId} and workspace_id = ${input.workspaceId}
       and rule_id = 'images-missing-alt'`);

  if (!issue) return { ok: false, reason: 'not_found', message: 'No alt-text issue with that id.' };

  const images = issue.evidence.sample ?? [];
  if (images.length === 0) {
    return { ok: false, reason: 'no_images', message: 'That finding recorded no image URLs.' };
  }

  return run(tx, {
    workspaceId: input.workspaceId,
    featureKey: 'audit.ai_alt_text',
    system:
      'Suggest alt text from each filename and the page context. One line per image as ' +
      '`filename — alt text`. Describe what the image shows; never start with "image of". ' +
      'If a filename suggests a decorative element, say `decorative — use alt=""`.',
    prompt: [`Page: ${issue.page_url}`, 'Images without alt text:', ...images].join('\n'),
    // Priced per image, so the true count is what gets charged.
    expectedUnits: images.length,
  }, deps);
}

async function run(
  tx: WorkspaceScopedDb,
  input: {
    workspaceId: string; featureKey: string; system: string; prompt: string; expectedUnits: number;
  },
  deps: ExecuteDeps,
): Promise<AiResult<string>> {
  try {
    const result = await execute(
      tx,
      {
        featureKey: input.featureKey,
        prompt: input.prompt,
        system: input.system,
        modality: 'text',
        expectedUnits: input.expectedUnits,
      },
      { workspaceId: input.workspaceId },
      deps,
    );

    if (!result.ok || !result.text) {
      return {
        ok: false,
        reason: 'generation_failed',
        // Charging nothing for a failure is the ledger's job; saying so is ours.
        message: result.error ?? 'The model returned nothing. You have not been charged.',
      };
    }
    return { ok: true, value: result.text, creditsCharged: 0 };
  } catch (err) {
    if (err instanceof AiUnavailable) {
      return { ok: false, reason: err.reason, message: err.message };
    }
    return {
      ok: false,
      reason: 'error',
      message: err instanceof Error ? err.message : 'The request failed.',
    };
  }
}
