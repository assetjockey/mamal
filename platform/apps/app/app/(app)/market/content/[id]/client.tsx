'use client';

import { useMemo, useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { scoreContent, type Brief, type ContentScore, type DocRow } from '@mamal/tool-market';
import { saveDocument } from '../../actions';

/**
 * The editor.
 *
 * The score is computed **here, on every keystroke**, from the same pure
 * function the server uses — so the feedback is instant and does not cost a
 * round trip per character. The server recomputes on save and that number is
 * the one that is stored; this one is a preview, and the two agree because they
 * are literally the same code.
 */
export function Editor({
  doc,
  brief,
  initialScore,
}: {
  doc: DocRow;
  brief: Brief | null;
  initialScore: ContentScore;
}) {
  const [title, setTitle] = useState(doc.title);
  const [body, setBody] = useState(doc.body);
  const [keywords, setKeywords] = useState(doc.targetKeywords.join(', '));
  const [meta, setMeta] = useState(
    typeof doc.meta.description === 'string' ? doc.meta.description : '',
  );
  const [status, setStatus] = useState(doc.status);
  const [saved, setSaved] = useState<string | null>(null);

  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const targetKeywords = useMemo(
    () => keywords.split(',').map((k) => k.trim()).filter(Boolean),
    [keywords],
  );

  const score = useMemo(
    () =>
      scoreContent({ title, body, targetKeywords, metaDescription: meta }, brief ?? {}),
    [title, body, targetKeywords, meta, brief],
  );

  const dirty =
    title !== doc.title ||
    body !== doc.body ||
    meta !== (typeof doc.meta.description === 'string' ? doc.meta.description : '') ||
    keywords !== doc.targetKeywords.join(', ') ||
    status !== doc.status;

  const save = () => {
    start(async () => {
      const result = await saveDocument({
        docId: doc.id,
        title,
        body,
        metaDescription: meta,
        targetKeywords,
        status: status as 'draft' | 'in_review' | 'approved' | 'published',
      });
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      // A slug change on a published page breaks an address somebody has.
      if (result.warning) toast({ kind: 'info', message: result.warning });
      setSaved(new Date().toLocaleTimeString());
      router.refresh();
    });
  };

  return (
    <div className="flex flex-col gap-6 xl:flex-row xl:items-start">
      {/* -------------------------------------------------------- the draft */}
      <div className="flex min-w-0 flex-1 flex-col gap-4">
        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Title</span>
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="h-10 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[16px]"
          />
        </label>

        <div className="flex flex-wrap gap-3">
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Target keyword</span>
            <input
              value={keywords}
              onChange={(e) => setKeywords(e.target.value)}
              placeholder="best widget"
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            />
          </label>
          <label className="flex min-w-0 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Status</span>
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value)}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              <option value="draft">Draft</option>
              <option value="in_review">In review</option>
              <option value="approved">Approved</option>
              <option value="published">Published</option>
            </select>
          </label>
        </div>

        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">
            Meta description{' '}
            <span className="tabular-nums">{meta.length}/158</span>
          </span>
          <textarea
            value={meta}
            onChange={(e) => setMeta(e.target.value)}
            rows={2}
            className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-2 text-[14px]"
          />
        </label>

        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Body — Markdown</span>
          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            rows={24}
            spellCheck
            className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-3 font-mono text-[13px] leading-[1.6]"
          />
        </label>

        <div className="flex flex-wrap items-center gap-3">
          <Button onClick={save} disabled={pending || !dirty}>
            {pending ? 'Saving…' : 'Save'}
          </Button>
          <span className="text-[12px] text-[var(--text-secondary)]" aria-live="polite">
            {dirty ? 'Unsaved changes' : saved ? `Saved at ${saved}` : 'Up to date'}
          </span>
        </div>
      </div>

      {/* --------------------------------------------------------- the score */}
      <aside className="flex w-full min-w-0 flex-col gap-3 xl:w-[380px] xl:shrink-0">
        <Card>
          <div className="flex items-baseline justify-between gap-2">
            <SectionLabel>Score</SectionLabel>
            <span className="text-[11px] text-[var(--text-secondary)]">
              {score.wordCount.toLocaleString()} words
            </span>
          </div>
          <div className="mt-2 flex items-baseline gap-3">
            <span className="text-[40px] leading-none font-light tabular-nums">{score.score}</span>
            <span className="text-[12px] text-[var(--text-secondary)]">
              out of 100 across {score.checks.length} checks
            </span>
          </div>

          {/*
            * Readability is withheld rather than guessed for text the formula
            * was not built for — the note says which, so a blank is never read
            * as "unreadable".
            */}
          <p className="mt-3 text-[12px] text-[var(--text-secondary)]">
            {score.readability !== null
              ? `Flesch reading ease ${score.readability}.`
              : score.readabilityNote}
          </p>
        </Card>

        <ul className="flex flex-col gap-2">
          {score.checks.map((check) => (
            <li key={check.key}>
              <Card>
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <span className="text-[14px]">{check.label}</span>
                  <StatusBadge
                    status={
                      check.state === 'pass' ? 'ok' : check.state === 'partial' ? 'warn' : 'error'
                    }
                  >
                    {check.state === 'pass' ? 'good' : check.state === 'partial' ? 'close' : 'fix'}
                  </StatusBadge>
                </div>
                <p className="mt-1 text-[12px] text-[var(--text-secondary)]">{check.detail}</p>
                {check.fix && <p className="mt-1 text-[12px]">{check.fix}</p>}
              </Card>
            </li>
          ))}
        </ul>

        {brief && (brief.entities?.length || brief.questions?.length) ? (
          <Card>
            <SectionLabel>Brief</SectionLabel>
            {score.entitiesMissing.length > 0 && (
              <p className="mt-2 text-[12px]">
                <span className="text-[var(--text-secondary)]">Not yet covered: </span>
                {score.entitiesMissing.join(', ')}
              </p>
            )}
            {score.questionsMissing.length > 0 && (
              <div className="mt-2 text-[12px]">
                <span className="text-[var(--text-secondary)]">Unanswered questions:</span>
                <ul className="mt-1 flex flex-col gap-1">
                  {score.questionsMissing.map((q) => (
                    <li key={q}>{q}</li>
                  ))}
                </ul>
              </div>
            )}
            {score.entitiesMissing.length === 0 && score.questionsMissing.length === 0 && (
              <p className="mt-2 text-[12px] text-[var(--text-secondary)]">
                Everything in the brief is covered.
              </p>
            )}
          </Card>
        ) : null}

        <p className="text-[11px] text-[var(--text-secondary)]">
          Scored here as you type and again on the server when you save — the same arithmetic
          both times. It costs nothing and does not use AI.
          {initialScore.score !== score.score && ' The stored score updates when you save.'}
        </p>
      </aside>
    </div>
  );
}
