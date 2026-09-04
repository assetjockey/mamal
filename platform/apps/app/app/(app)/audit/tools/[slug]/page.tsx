import Link from 'next/link';
import { notFound } from 'next/navigation';
import { toolBySlug, ALL_TOOLS } from '@mamal/seo-tools';
import { Card, PageHeader, SectionLabel } from '@mamal/ui';
import { ToolRunner } from './runner';

export const dynamic = 'force-dynamic';

export default async function ToolPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const tool = toolBySlug(slug);
  if (!tool) notFound();

  const related = ALL_TOOLS.filter((t) => t.category === tool.category && t.slug !== tool.slug).slice(0, 4);

  return (
    <div className="max-w-3xl">
      <PageHeader title={tool.name} description={tool.description} />

      <Card className="mb-6 border-none bg-[var(--surface-band)]">
        <p className="text-[14px] leading-[1.4] text-[var(--text-secondary)]">{tool.why}</p>
      </Card>

      <ToolRunner
        slug={tool.slug}
        fields={tool.fields}
        fetches={Boolean(tool.fetches)}
      />

      {related.length > 0 ? (
        <div className="mt-12">
          <SectionLabel>Related</SectionLabel>
          <ul className="flex flex-wrap gap-2">
            {related.map((t) => (
              <li key={t.slug}>
                <Link
                  href={`/audit/tools/${t.slug}`}
                  className="inline-flex rounded-[4px] border border-[var(--border-hairline)] px-3 py-1.5 text-[13px] text-[var(--text-secondary)] transition-colors duration-[120ms] hover:bg-[var(--surface-hover)]"
                >
                  {t.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      ) : null}
    </div>
  );
}
