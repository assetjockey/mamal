import Link from 'next/link';
import { ALL_TOOLS, TOOL_CATEGORIES } from '@mamal/seo-tools';
import { Card, PageHeader, SectionLabel } from '@mamal/ui';

export const dynamic = 'force-static';

const CATEGORY_COPY: Record<string, string> = {
  research: 'Look at a live page or site.',
  development: 'Build, decode and check things without leaving the tab.',
  content: 'Work on the words themselves.',
};

export default function ToolsIndex() {
  return (
    <>
      <PageHeader
        title="Tools"
        description={`${ALL_TOOLS.length} instant tools. No sign-up, no credits, no limits on the ones that run locally — they cost us nothing, so charging for them would be silly.`}
      />

      {TOOL_CATEGORIES.map((category) => {
        const tools = ALL_TOOLS.filter((t) => t.category === category);
        if (tools.length === 0) return null;
        return (
          <section key={category} className="mb-10">
            <SectionLabel>{category}</SectionLabel>
            <p className="mb-4 text-[14px] text-[var(--text-secondary)]">
              {CATEGORY_COPY[category]}
            </p>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 [&>*]:min-w-0">
              {tools.map((tool) => (
                <Link key={tool.slug} href={`/audit/tools/${tool.slug}`} className="block">
                  <Card className="h-full transition-colors duration-[120ms] hover:bg-[var(--surface-hover)]">
                    <h3 className="text-[16px] text-[var(--text-primary)]">{tool.name}</h3>
                    <p className="mt-1.5 text-[13px] leading-[1.4] text-[var(--text-secondary)]">
                      {tool.description}
                    </p>
                    {tool.fetches ? (
                      <p className="mt-3 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
                        Fetches a live page
                      </p>
                    ) : null}
                  </Card>
                </Link>
              ))}
            </div>
          </section>
        );
      })}
    </>
  );
}
