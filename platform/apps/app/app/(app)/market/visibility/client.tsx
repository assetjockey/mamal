'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Button, Card, EmptyState, SectionLabel, StatusBadge, Table, Td, Th, Tr, useToast,
} from '@mamal/ui';
import type { CompetitorRow, PromptRow, VisibilityOverview } from '@mamal/tool-market';
import {
  addVisibilityPrompt, removeVisibilityBrand, removeVisibilityPrompt, runVisibilityNow,
  saveVisibilityBrand, setVisibilitySelf, toggleVisibilityPrompt,
} from '../actions';

type Source = { url: string; host: string; brand: string | null; citations: number; models: string[] };

/** How each assistant is written for a reader. */
const LABELS: Record<string, string> = {
  claude: 'Claude',
  chatgpt: 'ChatGPT',
  gemini: 'Gemini',
  perplexity: 'Perplexity',
};

const label = (model: string) => LABELS[model] ?? model;
const pct = (n: number) => `${Math.round(n * 100)}%`;

export function VisibilityBoard({
  overview,
  prompts,
  brands,
  sources,
}: {
  overview: VisibilityOverview;
  prompts: PromptRow[];
  brands: CompetitorRow[];
  sources: Source[];
}) {
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const tracked = prompts.filter((p) => p.isTracked).length;
  const self = brands.find((b) => b.isSelf);

  /*
   * The cost is stated before the click.
   *
   * Ten credits per assistant per prompt. A customer with twelve prompts and
   * four assistants is about to spend 480 credits, and finding that out
   * afterwards is the complaint this avoids.
   */
  const askable = 4 - overview.unavailable.length;
  const estimate = tracked * Math.max(askable, 0) * 10;

  const run = () => {
    start(async () => {
      const result = await runVisibilityNow();
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      if (result.problem) {
        toast({ kind: 'error', message: result.problem });
        return;
      }
      toast({
        kind: result.failed > 0 ? 'info' : 'ok',
        message:
          `${result.answered} answer${result.answered === 1 ? '' : 's'} from ${result.probes} prompt${result.probes === 1 ? '' : 's'}` +
          (result.failed > 0 ? `, ${result.failed} model call${result.failed === 1 ? '' : 's'} failed.` : '.'),
      });
      router.refresh();
    });
  };

  return (
    <div className="flex flex-col gap-8">
      {/* ------------------------------------------------------- headline */}
      {overview.current.length === 0 ? (
        <EmptyState
          title="Nothing measured yet"
          description={
            self
              ? 'Add a question a buyer would really type, then run the probes. The answers are stored, so you can read exactly why a number moved.'
              : 'Start by saying which brand is yours — share of voice is a ratio, and without that it has no numerator.'
          }
          action={
            tracked > 0 && self ? (
              <Button onClick={run} disabled={pending}>
                {pending ? 'Asking…' : `Run probes · ~${estimate} credits`}
              </Button>
            ) : undefined
          }
        />
      ) : (
        <section className="flex flex-col gap-3">
          <div className="flex flex-wrap items-baseline justify-between gap-3">
            <SectionLabel>Latest reading</SectionLabel>
            <Button variant="ghost" onClick={run} disabled={pending || tracked === 0 || !self}>
              {pending ? 'Asking…' : `Run probes · ~${estimate} credits`}
            </Button>
          </div>

          {/*
            * Four across only at 2xl. `Card`'s `p-6` is 48px a side with this
            * codebase's 8px `--spacing`, so a 4-up grid inside a 1000px content
            * column leaves each card about 130px to work with and every stat
            * label wraps to three lines.
            */}
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 2xl:grid-cols-4">
            {overview.current.map((row) => (
              <Card key={row.model}>
                <div className="flex items-baseline justify-between gap-2">
                  <span className="text-[14px]">{label(row.model)}</span>
                  <span className="text-[11px] text-[var(--text-secondary)] tabular-nums">
                    {row.capturedOn}
                  </span>
                </div>

                {/*
                  * Mention rate is the headline, not share of voice: it answers
                  * "will they hear about us at all", which is the question
                  * somebody buys this to answer. Share of voice is the finer
                  * grain underneath it.
                  */}
                <div className="mt-3 flex items-baseline gap-2">
                  <span className="text-[32px] leading-none font-light tabular-nums">
                    {pct(row.mentionRate)}
                  </span>
                  <span className="text-[12px] text-[var(--text-secondary)]">
                    of prompts name you
                  </span>
                </div>

                <dl className="mt-4 flex flex-col gap-1 text-[12px]">
                  <Stat
                    term="Share of voice"
                    value={pct(row.shareOfVoice)}
                    delta={row.delta === null ? null : Math.round(row.delta * 100)}
                  />
                  <Stat
                    term="Average position"
                    value={row.avgPosition === null ? 'not named' : row.avgPosition.toFixed(1)}
                  />
                  <Stat term="Prompts that linked you" value={String(row.citationCount)} />
                </dl>
              </Card>
            ))}
          </div>
        </section>
      )}

      {/*
        * Which assistants will not be asked, shown whether or not there is data
        * yet — it matters most *before* the first run, when somebody is
        * deciding whether the comparison is worth paying for.
        */}
      {overview.unavailable.length > 0 && (
        <p className="text-[12px] text-[var(--text-secondary)]">
          Not asked:{' '}
          {overview.unavailable.map((u, i) => (
            <span key={u.assistant}>
              {i > 0 && ', '}
              <span className="text-[var(--text-primary)]">{label(u.assistant)}</span> — {u.reason}
            </span>
          ))}
        </p>
      )}

      {/* --------------------------------------------------------- brands */}
      <Brands brands={brands} pending={pending} start={start} />

      {/* -------------------------------------------------------- prompts */}
      <Prompts prompts={prompts} pending={pending} start={start} />

      {/* -------------------------------------------------------- sources */}
      {sources.length > 0 && (
        <section className="flex flex-col gap-3">
          <SectionLabel>Cited sources</SectionLabel>
          <p className="text-[12px] text-[var(--text-secondary)]">
            The pages the assistants pointed at. One of yours here is worth leaving alone; one of
            theirs, cited by several, is worth matching.
          </p>
          <Table label="Cited sources">
            <thead>
              <Tr>
                <Th>Page</Th>
                <Th>Whose</Th>
                <Th>Named by</Th>
                <Th align="right">Citations</Th>
              </Tr>
            </thead>
            <tbody>
              {sources.map((source) => (
                <Tr key={source.url}>
                  <Td>
                    <a
                      href={source.url}
                      target="_blank"
                      rel="noreferrer noopener"
                      className="block max-w-[42ch] truncate underline-offset-2 hover:underline"
                      title={source.url}
                    >
                      {source.url.replace(/^https?:\/\//, '')}
                    </a>
                  </Td>
                  <Td>
                    {source.brand ? (
                      <StatusBadge status="ok">{source.brand}</StatusBadge>
                    ) : (
                      <span className="text-[var(--text-secondary)]">{source.host || '—'}</span>
                    )}
                  </Td>
                  <Td>{source.models.map(label).join(', ')}</Td>
                  <Td align="right">{source.citations}</Td>
                </Tr>
              ))}
            </tbody>
          </Table>
        </section>
      )}
    </div>
  );
}

function Stat({ term, value, delta }: { term: string; value: string; delta?: number | null }) {
  return (
    <div className="flex items-baseline justify-between gap-2">
      <dt className="min-w-0 text-[var(--text-secondary)]">{term}</dt>
      <dd className="shrink-0 tabular-nums">
        {value}
        {delta !== null && delta !== undefined && delta !== 0 && (
          <span className={delta > 0 ? 'ml-1 text-[var(--status-ok)]' : 'ml-1 text-[var(--status-error)]'}>
            {delta > 0 ? '+' : ''}
            {delta}
          </span>
        )}
      </dd>
    </div>
  );
}

/* ------------------------------------------------------------------ brands */

function Brands({
  brands,
  pending,
  start,
}: {
  brands: CompetitorRow[];
  pending: boolean;
  start: (fn: () => Promise<void>) => void;
}) {
  const toast = useToast();
  const router = useRouter();
  const [brand, setBrand] = useState('');
  const [domain, setDomain] = useState('');

  const hasSelf = brands.some((b) => b.isSelf);

  const add = () => {
    if (!brand.trim()) return;
    start(async () => {
      const result = await saveVisibilityBrand(brand, domain, !hasSelf);
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      setBrand('');
      setDomain('');
      router.refresh();
    });
  };

  const act = (fn: () => Promise<{ ok: boolean; error?: string }>) => {
    start(async () => {
      const result = await fn();
      if (!result.ok) toast({ kind: 'error', message: result.error ?? 'That did not work.' });
      router.refresh();
    });
  };

  return (
    <section className="flex flex-col gap-3">
      <SectionLabel>Brands compared</SectionLabel>
      <p className="text-[12px] text-[var(--text-secondary)]">
        Exactly one is yours — share of voice is your mentions over everybody&rsquo;s. The domain is
        how a citation is recognised as yours, so a link counts as more than a mention.
      </p>

      <div className="flex flex-col gap-2">
        {brands.map((b) => (
          <div
            key={b.id}
            className="flex flex-wrap items-center gap-3 border-b border-[var(--border)] pb-2 last:border-0"
          >
            <span className="text-[14px]">{b.brand}</span>
            {b.domain && (
              <span className="text-[12px] text-[var(--text-secondary)]">{b.domain}</span>
            )}
            {b.isSelf ? (
              <StatusBadge status="ok">Yours</StatusBadge>
            ) : (
              <>
                <Button
                  variant="ghost"
                  onClick={() => act(() => setVisibilitySelf(b.id))}
                  disabled={pending}
                >
                  Mark as yours
                </Button>
                <Button
                  variant="ghost"
                  onClick={() => act(() => removeVisibilityBrand(b.id))}
                  disabled={pending}
                >
                  Remove
                </Button>
              </>
            )}
          </div>
        ))}
      </div>

      <div className="flex flex-wrap items-end gap-2">
        <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Brand</span>
          <input
            value={brand}
            onChange={(e) => setBrand(e.target.value)}
            placeholder={hasSelf ? 'A competitor' : 'Your brand'}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Domain (optional)</span>
          <input
            value={domain}
            onChange={(e) => setDomain(e.target.value)}
            placeholder="example.com"
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <Button onClick={add} disabled={pending || !brand.trim()}>
          Add
        </Button>
      </div>
    </section>
  );
}

/* ----------------------------------------------------------------- prompts */

function Prompts({
  prompts,
  pending,
  start,
}: {
  prompts: PromptRow[];
  pending: boolean;
  start: (fn: () => Promise<void>) => void;
}) {
  const toast = useToast();
  const router = useRouter();
  const [prompt, setPrompt] = useState('');
  const [schedule, setSchedule] = useState<'daily' | 'weekly' | 'monthly'>('weekly');

  const add = () => {
    if (!prompt.trim()) return;
    start(async () => {
      const result = await addVisibilityPrompt(prompt, schedule);
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      setPrompt('');
      router.refresh();
    });
  };

  const act = (fn: () => Promise<{ ok: boolean; error?: string }>) => {
    start(async () => {
      const result = await fn();
      if (!result.ok) toast({ kind: 'error', message: result.error ?? 'That did not work.' });
      router.refresh();
    });
  };

  return (
    <section className="flex flex-col gap-3">
      <SectionLabel>Prompts</SectionLabel>
      <p className="text-[12px] text-[var(--text-secondary)]">
        Questions a buyer would type. Deliberately never naming your brand — asking &ldquo;is Acme
        good?&rdquo; guarantees the answer says Acme and measures nothing.
      </p>

      {prompts.length > 0 && (
        <Table label="Tracked prompts">
          <thead>
            <Tr>
              <Th>Prompt</Th>
              <Th>Last result</Th>
              <Th>Cadence</Th>
              <Th align="right">Actions</Th>
            </Tr>
          </thead>
          <tbody>
            {prompts.map((p) => (
              <Tr key={p.id}>
                <Td>
                  <span className="block max-w-[52ch] [&>*]:min-w-0">{p.prompt}</span>
                </Td>
                <Td>
                  {p.askedBy.length === 0 ? (
                    <span className="text-[var(--text-secondary)]">not yet asked</span>
                  ) : p.mentionedBy.length === 0 ? (
                    <span className="text-[var(--status-error)]">
                      named by none of {p.askedBy.length}
                    </span>
                  ) : (
                    <span>
                      {p.mentionedBy.map(label).join(', ')}
                      <span className="text-[var(--text-secondary)]">
                        {' '}
                        ({p.mentionedBy.length} of {p.askedBy.length})
                      </span>
                    </span>
                  )}
                </Td>
                <Td>{p.isTracked ? p.schedule : 'paused'}</Td>
                <Td align="right">
                  <div className="flex justify-end gap-2">
                    <Button
                      variant="ghost"
                      onClick={() => act(() => toggleVisibilityPrompt(p.id, !p.isTracked))}
                      disabled={pending}
                    >
                      {p.isTracked ? 'Pause' : 'Resume'}
                    </Button>
                    <Button
                      variant="ghost"
                      onClick={() => act(() => removeVisibilityPrompt(p.id))}
                      disabled={pending}
                    >
                      Remove
                    </Button>
                  </div>
                </Td>
              </Tr>
            ))}
          </tbody>
        </Table>
      )}

      <div className="flex flex-wrap items-end gap-2">
        <label className="flex min-w-0 flex-[3] flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Prompt</span>
          <input
            value={prompt}
            onChange={(e) => setPrompt(e.target.value)}
            placeholder="What is the best widget for a small team?"
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Cadence</span>
          <select
            value={schedule}
            onChange={(e) => setSchedule(e.target.value as typeof schedule)}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          >
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </label>
        <Button onClick={add} disabled={pending || !prompt.trim()}>
          Add
        </Button>
      </div>
    </section>
  );
}
