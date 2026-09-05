'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import {
  Button, Card, EmptyState, SectionLabel, StatusBadge, Table, Td, Th, Tr, useToast,
} from '@mamal/ui';
import { runPipelineNow, savePipeline } from '../actions';

export type PipelineView = {
  id: string;
  name: string;
  source: string;
  schedule: string;
  auto_publish: boolean;
  is_active: boolean;
  next_run_at: string | null;
  source_config: Record<string, unknown>;
  destination_name: string | null;
};

export type RunView = {
  id: string;
  pipeline_id: string;
  status: string;
  error: string | null;
  credits_spent: number;
  trigger: { subject?: string; because?: string };
  doc_id: string | null;
  doc_title: string | null;
  created_at: string;
};

const SOURCES: Record<string, string> = {
  trend: 'A term you watch starts rising',
  gsc_opportunity: 'A query you already rank for',
  keyword: 'A list you keep',
};

export function PipelineBoard({
  pipelines,
  runs,
}: {
  pipelines: PipelineView[];
  runs: RunView[];
}) {
  const [pending, start] = useTransition();
  const [name, setName] = useState('');
  const [source, setSource] = useState('gsc_opportunity');
  const [schedule, setSchedule] = useState('weekly');
  const toast = useToast();
  const router = useRouter();

  const create = () => {
    if (!name.trim()) return;
    start(async () => {
      const result = await savePipeline({
        name, source, schedule,
        // Both off. A pipeline that starts live and publishing is a decision
        // nobody made.
        autoPublish: false,
        isActive: false,
      });
      toast(
        result.ok
          ? { kind: 'ok', message: 'Created, paused. Turn it on when you have seen a run.' }
          : { kind: 'error', message: result.error },
      );
      if (result.ok) setName('');
      router.refresh();
    });
  };

  const runNow = (id: string) => {
    start(async () => {
      const result = await runPipelineNow(id);
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      toast({
        kind: result.status === 'failed' ? 'error' : 'ok',
        message:
          result.note + (result.creditsSpent > 0 ? ` (${result.creditsSpent} credits)` : ''),
      });
      router.refresh();
    });
  };

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-3">
        <SectionLabel>New pipeline</SectionLabel>
        <div className="flex flex-wrap items-end gap-2">
          <label className="flex min-w-0 flex-[2] flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Name</span>
            <input
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Weekly from Search Console"
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            />
          </label>
          <label className="flex min-w-0 flex-[2] flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Write when</span>
            <select
              value={source}
              onChange={(e) => setSource(e.target.value)}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              {Object.entries(SOURCES).map(([key, label]) => (
                <option key={key} value={key}>{label}</option>
              ))}
            </select>
          </label>
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">How often</span>
            <select
              value={schedule}
              onChange={(e) => setSchedule(e.target.value)}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </label>
          <Button onClick={create} disabled={pending || !name.trim()}>Create</Button>
        </div>
        <p className="text-[12px] text-[var(--text-secondary)]">
          New pipelines start paused and never publish. Run one by hand, read what it produced,
          then decide.
        </p>
      </section>

      {pipelines.length === 0 ? (
        <EmptyState
          title="No pipelines yet"
          description="A pipeline turns something you already know — a rising term, a query you rank 12th for — into a brief and a draft, on a schedule."
        />
      ) : (
        <section className="flex flex-col gap-3">
          <SectionLabel>Pipelines</SectionLabel>
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
            {pipelines.map((p) => (
              <Card key={p.id}>
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <span className="text-[14px]">{p.name}</span>
                  <StatusBadge status={p.is_active ? 'ok' : 'info'}>
                    {p.is_active ? 'running' : 'paused'}
                  </StatusBadge>
                </div>
                <p className="mt-2 text-[12px] text-[var(--text-secondary)]">
                  {SOURCES[p.source] ?? p.source} · {p.schedule}
                </p>
                <p className="mt-1 text-[12px]">
                  {p.auto_publish && p.destination_name
                    ? `Publishes to ${p.destination_name}.`
                    : 'Leaves drafts for review.'}
                </p>
                {p.next_run_at && (
                  <p className="mt-1 text-[11px] text-[var(--text-secondary)]">
                    Next run {p.next_run_at.slice(0, 16).replace('T', ' ')}
                  </p>
                )}
                <div className="mt-3 flex flex-wrap gap-2">
                  <Button variant="ghost" onClick={() => runNow(p.id)} disabled={pending}>
                    {pending ? 'Running…' : 'Run now'}
                  </Button>
                  <Button
                    variant="ghost"
                    disabled={pending}
                    onClick={() =>
                      start(async () => {
                        const result = await savePipeline({
                          id: p.id, name: p.name, source: p.source, schedule: p.schedule,
                          autoPublish: p.auto_publish, isActive: !p.is_active,
                        });
                        if (!result.ok) toast({ kind: 'error', message: result.error });
                        router.refresh();
                      })
                    }
                  >
                    {p.is_active ? 'Pause' : 'Start'}
                  </Button>
                </div>
              </Card>
            ))}
          </div>
        </section>
      )}

      {runs.length > 0 && (
        <section className="flex flex-col gap-3">
          <SectionLabel>Recent runs</SectionLabel>
          <Table label="Recent pipeline runs">
            <thead>
              <Tr>
                <Th>What it wrote about</Th>
                <Th>Result</Th>
                <Th>Document</Th>
                <Th align="right">Credits</Th>
                <Th align="right">When</Th>
              </Tr>
            </thead>
            <tbody>
              {runs.map((run) => (
                <Tr key={run.id}>
                  <Td>
                    <span className="block max-w-[36ch] truncate">
                      {run.trigger.subject ?? '—'}
                    </span>
                    {run.trigger.because && (
                      <span className="block max-w-[46ch] truncate text-[11px] text-[var(--text-secondary)]">
                        {run.trigger.because}
                      </span>
                    )}
                  </Td>
                  <Td>
                    {/*
                      * A skip is deliberately not an error: a quiet week should
                      * not show red, or people stop reading the column.
                      */}
                    <StatusBadge
                      status={
                        run.status === 'completed' ? 'ok' : run.status === 'failed' ? 'error' : 'info'
                      }
                    >
                      {run.status}
                    </StatusBadge>
                    {run.error && (
                      <span className="block max-w-[40ch] text-[11px] text-[var(--status-error)]">
                        {run.error}
                      </span>
                    )}
                  </Td>
                  <Td>
                    {run.doc_id ? (
                      <NextLink
                        href={`/market/content/${run.doc_id}`}
                        className="block max-w-[30ch] truncate underline-offset-2 hover:underline"
                      >
                        {run.doc_title ?? 'Open'}
                      </NextLink>
                    ) : (
                      <span className="text-[var(--text-secondary)]">—</span>
                    )}
                  </Td>
                  <Td align="right">{run.credits_spent || '—'}</Td>
                  <Td align="right">{run.created_at.slice(0, 10)}</Td>
                </Tr>
              ))}
            </tbody>
          </Table>
        </section>
      )}
    </div>
  );
}
