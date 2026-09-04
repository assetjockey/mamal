import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';

/**
 * Crawl and findings export.
 *
 * Available on every plan: locking your own data behind a tier is the kind of
 * thing that makes people distrust a tool. What the paid tier buys is branded
 * PDF reporting, not access to the numbers.
 */
export async function GET(
  request: Request,
  { params }: { params: Promise<{ auditId: string }> },
) {
  const session = await getSession();
  if (!session) return new Response('Unauthorized', { status: 401 });

  const { auditId } = await params;
  const format = new URL(request.url).searchParams.get('format') ?? 'csv';
  const ws = session.workspace.id;

  const [audit, pages, issues] = await withWorkspace(
    ws,
    async (tx) => [
      (await tx.execute<{ host: string; score: number; finished_at: string }>(sql`
        select s.host, a.score, a.finished_at
          from audits a
          join audit_sites asite on asite.id = a.audit_site_id
          join sites s on s.id = asite.site_id
         where a.id = ${auditId} and a.workspace_id = ${ws}`))[0],

      await tx.execute<Record<string, unknown>>(sql`
        select url, status_code, fetch_class, title, meta_description, word_count,
               images_total, images_missing_alt, links_internal, links_external,
               depth, is_indexable, in_sitemap, response_ms, bytes
          from audit_pages where audit_id = ${auditId} order by depth, url`),

      await tx.execute<Record<string, unknown>>(sql`
        select i.rule_id, r.title, i.severity, r.category, i.page_url, i.status, i.evidence
          from audit_issues i join audit_rules r on r.id = i.rule_id
         where i.audit_id = ${auditId}
         order by case i.severity when 'critical' then 0 when 'warning' then 1 else 2 end`),
    ] as const,
    { db: db() },
  );

  if (!audit) return new Response('Not found', { status: 404 });

  const stamp = new Date(audit.finished_at ?? Date.now()).toISOString().slice(0, 10);
  const base = `${audit.host.replace(/[^a-z0-9.-]/gi, '_')}-audit-${stamp}`;

  if (format === 'json') {
    return Response.json(
      { site: audit.host, score: audit.score, finishedAt: audit.finished_at, pages, issues },
      { headers: { 'content-disposition': `attachment; filename="${base}.json"` } },
    );
  }

  // Issues are the useful CSV: one row per finding, ready for a spreadsheet.
  const rows = issues.map((i) => ({
    severity: i.severity,
    category: i.category,
    rule: i.rule_id,
    issue: i.title,
    url: i.page_url ?? '(site-wide)',
    status: i.status,
    evidence: JSON.stringify(i.evidence),
  }));

  return new Response(toCsv(rows), {
    headers: {
      'content-type': 'text/csv; charset=utf-8',
      'content-disposition': `attachment; filename="${base}.csv"`,
    },
  });
}

function toCsv(rows: Record<string, unknown>[]): string {
  if (rows.length === 0) return 'no findings\n';
  const headers = Object.keys(rows[0]!);
  const escape = (value: unknown): string => {
    const s = value === null || value === undefined ? '' : String(value);
    // A field containing a comma, quote or newline must be quoted, and inner
    // quotes doubled — otherwise one page title breaks the whole file.
    return /[",\n\r]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
  };
  return [
    headers.join(','),
    ...rows.map((row) => headers.map((h) => escape(row[h])).join(',')),
  ].join('\n') + '\n';
}
