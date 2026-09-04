'use client';

import { useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import { Button, EmptyState, SectionLabel, StatusBadge, Table, Td, Th, Tr, useToast } from '@mamal/ui';
import type { DocRow } from '@mamal/tool-market';
import { newDoc } from '../actions';

const STATUS_LABEL: Record<string, string> = {
  draft: 'Draft',
  in_review: 'In review',
  approved: 'Approved',
  published: 'Published',
  archived: 'Archived',
};

/** The score's colour is a judgement, so it is stated in words as well. */
function scoreBand(score: number | null): { status: 'ok' | 'warn' | 'error' | 'neutral'; label: string } {
  if (score === null) return { status: 'neutral', label: 'not scored' };
  if (score >= 80) return { status: 'ok', label: 'ready' };
  if (score >= 55) return { status: 'warn', label: 'nearly' };
  return { status: 'error', label: 'needs work' };
}

export function DocList({
  docs,
  counts,
  activeStatus,
}: {
  docs: DocRow[];
  counts: { status: string; n: number }[];
  activeStatus: string | null;
}) {
  const [pending, start] = useTransition();
  const [title, setTitle] = useState('');
  const toast = useToast();
  const router = useRouter();

  const create = () => {
    if (!title.trim()) return;
    start(async () => {
      const result = await newDoc(title);
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      setTitle('');
      router.push(`/market/content/${result.id}`);
    });
  };

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-wrap items-end gap-2">
        <label className="flex min-w-0 flex-[3] flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">New document</span>
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') create();
            }}
            placeholder="How to choose a widget for a small team"
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <Button onClick={create} disabled={pending || !title.trim()}>
          {pending ? 'Creating…' : 'Create'}
        </Button>
      </section>

      {counts.length > 0 && (
        <nav className="flex flex-wrap gap-2" aria-label="Filter by status">
          <FilterPill href="/market/content" active={activeStatus === null}>
            All {docs.length > 0 || activeStatus ? '' : ''}
          </FilterPill>
          {counts.map((c) => (
            <FilterPill
              key={c.status}
              href={`/market/content?status=${c.status}`}
              active={activeStatus === c.status}
            >
              {STATUS_LABEL[c.status] ?? c.status} · {c.n}
            </FilterPill>
          ))}
        </nav>
      )}

      {docs.length === 0 ? (
        <EmptyState
          title={activeStatus ? 'Nothing with that status' : 'No documents yet'}
          description={
            activeStatus
              ? 'Try another status, or clear the filter.'
              : 'Start one above. The editor scores it against a brief as you type — headings, coverage, length, readability — and none of that costs a credit.'
          }
        />
      ) : (
        <section className="flex flex-col gap-3">
          <SectionLabel>Documents</SectionLabel>
          <Table label="Documents">
            <thead>
              <Tr>
                <Th>Title</Th>
                <Th>Status</Th>
                <Th>Score</Th>
                <Th align="right">Words</Th>
                <Th align="right">Updated</Th>
              </Tr>
            </thead>
            <tbody>
              {docs.map((doc) => {
                const band = scoreBand(doc.seoScore);
                return (
                  <Tr key={doc.id}>
                    <Td>
                      <NextLink
                        href={`/market/content/${doc.id}`}
                        className="block max-w-[46ch] truncate underline-offset-2 hover:underline"
                      >
                        {doc.title}
                      </NextLink>
                      {doc.targetKeywords[0] && (
                        <span className="block text-[11px] text-[var(--text-secondary)]">
                          {doc.targetKeywords[0]}
                        </span>
                      )}
                    </Td>
                    <Td>{STATUS_LABEL[doc.status] ?? doc.status}</Td>
                    <Td>
                      {doc.seoScore === null ? (
                        <span className="text-[var(--text-secondary)]">—</span>
                      ) : (
                        <span className="flex items-center gap-2">
                          <span className="tabular-nums">{doc.seoScore}</span>
                          <StatusBadge status={band.status === 'neutral' ? 'info' : band.status}>
                            {band.label}
                          </StatusBadge>
                        </span>
                      )}
                    </Td>
                    <Td align="right">{doc.wordCount.toLocaleString()}</Td>
                    <Td align="right">{doc.updatedAt.slice(0, 10)}</Td>
                  </Tr>
                );
              })}
            </tbody>
          </Table>
        </section>
      )}
    </div>
  );
}

function FilterPill({
  href,
  active,
  children,
}: {
  href: string;
  active: boolean;
  children: React.ReactNode;
}) {
  return (
    <NextLink
      href={href}
      aria-current={active ? 'page' : undefined}
      className={
        'rounded-full border px-3 py-1 text-[12px] ' +
        (active
          ? 'border-[var(--accent)] bg-[var(--accent-wash)] text-[var(--accent)]'
          : 'border-[var(--border)] text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]')
      }
    >
      {children}
    </NextLink>
  );
}
