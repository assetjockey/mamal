'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import type { Field } from '@mamal/link-catalog';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { newBlock, publishBioPage, removeBlock, reorderBlocks, saveBlock } from '../../actions';

/**
 * The bio page builder.
 *
 * Three panes at desktop, stacked below 1024: the block palette, the block
 * list, and a phone-frame preview. The preview renders the *families* — one
 * renderer for each of the twelve, not one per type — which is the whole point
 * of the catalogue design: 84 types, twelve ways of drawing them.
 *
 * Ordering is by explicit Up/Down buttons rather than drag-and-drop. Dragging
 * is nicer with a mouse and unusable with a keyboard or on a phone, and this
 * builder has to work on a phone — a bio page is the one thing people edit
 * from the device they publish it on.
 */

export type BuilderBlock = {
  id: string; type: string; label: string; family: string;
  settings: Record<string, unknown>; fields: Field[];
  isEnabled: boolean; clicks: number;
};

// `min-w-0`: a <select> keeps a minimum width from its longest option.
const control =
  'w-full min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

export function Builder({
  page,
  palette,
  blocks: initial,
}: {
  page: { id: string; alias: string; title: string | null; isPublished: boolean; url: string };
  palette: { category: string; items: { key: string; label: string; family: string }[] }[];
  blocks: BuilderBlock[];
}) {
  const [blocks, setBlocks] = useState(initial);
  const [selected, setSelected] = useState<string | null>(initial[0]?.id ?? null);

  /*
   * Re-sync when the server sends a new list.
   *
   * `useState(initial)` reads its argument once, so after `router.refresh()`
   * the freshly-added block arrived in props and the list on screen still
   * showed the old array — adding a block appeared to do nothing until a full
   * page load. Adjusting during render (rather than in an effect) is the
   * documented way to do this: React re-renders immediately with the new value
   * and never paints the stale one.
   *
   * Nothing is lost by discarding local state here: every local edit is
   * persisted before the refresh that replaces it.
   */
  const [lastFromServer, setLastFromServer] = useState(initial);
  if (lastFromServer !== initial) {
    setLastFromServer(initial);
    setBlocks(initial);
    if (!initial.some((b) => b.id === selected)) setSelected(initial[0]?.id ?? null);
  }
  const [published, setPublished] = useState(page.isPublished);
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const current = blocks.find((b) => b.id === selected) ?? null;

  const add = (type: string) => {
    start(async () => {
      const result = await newBlock(page.id, type);
      if (!result.ok) { toast({ kind: 'error', message: result.error }); return; }
      router.refresh();
    });
  };

  const move = (index: number, by: number) => {
    const to = index + by;
    if (to < 0 || to >= blocks.length) return;
    const next = [...blocks];
    [next[index], next[to]] = [next[to]!, next[index]!];
    setBlocks(next);
    start(async () => { await reorderBlocks(page.id, next.map((b) => b.id)); });
  };

  const drop = (block: BuilderBlock) => {
    start(async () => {
      await removeBlock(page.id, block.id);
      setBlocks((list) => list.filter((b) => b.id !== block.id));
      toast({
        kind: 'info',
        message: `Removed the ${block.label.toLowerCase()} block.`,
        onUndo: async () => {
          // Re-created rather than restored: a block carries no history worth
          // preserving, and re-adding it with its settings is the same result.
          const result = await newBlock(page.id, block.type);
          if (result.ok) await saveBlock(page.id, result.id, block.settings);
          router.refresh();
        },
      });
      router.refresh();
    });
  };

  const save = (block: BuilderBlock, settings: Record<string, unknown>) => {
    setBlocks((list) => list.map((b) => (b.id === block.id ? { ...b, settings } : b)));
    start(async () => {
      const result = await saveBlock(page.id, block.id, settings);
      if (!result.ok) toast({ kind: 'error', message: result.error });
    });
  };

  const togglePublished = () => {
    start(async () => {
      await publishBioPage(page.id, !published);
      setPublished((v) => !v);
      toast({
        kind: 'ok',
        message: published ? 'Unpublished. The page now 404s.' : `Published at ${page.url}.`,
      });
      router.refresh();
    });
  };

  /*
   * Two panes at xl, three only at 2xl.
   *
   * At 1280 the content column is 920px wide, and 220 + 300 of panels plus two
   * 48px gaps left the block list 304px — narrow enough that its own header
   * overflowed the track and the preview card drew on top of the Publish
   * button. `minmax(0,1fr)` lets a track shrink below its content, so the
   * symptom was an unclickable button rather than a scrollbar.
   */
  return (
    <div className="grid gap-4 [&>*]:min-w-0 lg:grid-cols-[200px_minmax(0,1fr)] 2xl:grid-cols-[200px_minmax(0,1fr)_300px]">
      <div className="min-w-0">
        <Card>
          <SectionLabel>Add a block</SectionLabel>
          <div className="mt-3 grid gap-3">
            {palette.map((group) => (
              <div key={group.category}>
                <p className="mb-1 text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
                  {group.category}
                </p>
                <label htmlFor={`add-${group.category}`} className="sr-only">
                  Add {article(group.category)} {group.category} block
                </label>
                <select
                  id={`add-${group.category}`}
                  value=""
                  onChange={(e) => { if (e.target.value) add(e.target.value); }}
                  className={control}
                  disabled={pending}
                >
                  <option value="">Choose…</option>
                  {group.items.map((b) => <option key={b.key} value={b.key}>{b.label}</option>)}
                </select>
              </div>
            ))}
          </div>
        </Card>
      </div>

      <div className="grid min-w-0 gap-6 [&>*]:min-w-0">
        <Card>
          <div className="flex flex-wrap items-center justify-between gap-3">
            <SectionLabel>Blocks</SectionLabel>
            <div className="flex items-center gap-2">
              <StatusBadge status={published ? 'ok' : 'neutral'}>
                {published ? 'Published' : 'Draft'}
              </StatusBadge>
              <Button size="sm" variant="quiet" onClick={togglePublished} disabled={pending}>
                {published ? 'Unpublish' : 'Publish'}
              </Button>
            </div>
          </div>

          {blocks.length === 0 ? (
            <div className="mt-4">
              <EmptyState
                title="No blocks yet"
                description="Pick one from the palette. A link block is the usual first one."
              />
            </div>
          ) : (
            <ol className="mt-4 grid gap-2">
              {blocks.map((b, i) => (
                <li
                  key={b.id}
                  className={`rounded-[4px] border p-3 ${
                    b.id === selected
                      ? 'border-[var(--accent-solid)] bg-[var(--accent-wash)]'
                      : 'border-[var(--border-hairline)]'
                  }`}
                >
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <button
                      type="button"
                      onClick={() => setSelected(b.id)}
                      aria-pressed={b.id === selected}
                      className="min-w-0 text-left text-[14px] text-[var(--text-primary)] focus-visible:outline-2 focus-visible:outline-[var(--accent-solid)]"
                    >
                      {/*
                        The space matters: without it the two spans concatenate
                        into one word and a screen reader announces "Linklink".
                      */}
                      <span>{b.label}</span>{' '}
                      <span className="text-[12px] tabular-nums text-[var(--text-faint)]">
                        {b.clicks > 0 ? `${b.clicks.toLocaleString()} clicks` : b.family}
                      </span>
                    </button>
                    <div className="flex shrink-0 gap-1">
                      <Button size="sm" variant="quiet" onClick={() => move(i, -1)} disabled={i === 0 || pending}>
                        Up
                      </Button>
                      <Button size="sm" variant="quiet" onClick={() => move(i, 1)}
                              disabled={i === blocks.length - 1 || pending}>
                        Down
                      </Button>
                      <Button size="sm" variant="quiet" onClick={() => drop(b)} disabled={pending}>
                        Remove
                      </Button>
                    </div>
                  </div>
                </li>
              ))}
            </ol>
          )}
        </Card>

        {current ? (
          <Card>
            <SectionLabel>{current.label}</SectionLabel>
            <BlockForm block={current} onChange={(s) => save(current, s)} />
          </Card>
        ) : null}
      </div>

      <div className="min-w-0 lg:col-span-2 2xl:col-span-1">
        <Card>
          <SectionLabel>Preview</SectionLabel>
          <div className="mt-3 mx-auto w-full max-w-[280px] rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] p-4">
            <p className="mb-3 truncate text-center text-[15px] text-[var(--text-primary)]">
              {page.title ?? `/${page.alias}`}
            </p>
            <div className="grid gap-2">
              {blocks.filter((b) => b.isEnabled).map((b) => <Rendered key={b.id} block={b} />)}
            </div>
            {blocks.length === 0 ? (
              <p className="py-8 text-center text-[12px] text-[var(--text-faint)]">
                Blocks appear here as you add them.
              </p>
            ) : null}
          </div>
        </Card>
      </div>
    </div>
  );
}

/** "a standard block", "an embed block" — this is read aloud. */
const article = (word: string) => (/^[aeiou]/i.test(word) ? 'an' : 'a');

/* --------------------------------------------------------------------- form */

function BlockForm({
  block,
  onChange,
}: {
  block: BuilderBlock;
  onChange: (settings: Record<string, unknown>) => void;
}) {
  const [draft, setDraft] = useState(block.settings);

  const set = (name: string, value: unknown) => {
    const next = { ...draft, [name]: value };
    setDraft(next);
    onChange(next);
  };

  if (block.fields.length === 0) {
    return (
      <p className="mt-2 text-[13px] text-[var(--text-secondary)]">
        This block has no settings to edit.
      </p>
    );
  }

  return (
    <div className="mt-3 grid gap-3 md:grid-cols-2">
      {block.fields.map((f) => {
        const id = `block-${block.id}-${f.name}`;
        const value = draft[f.name];

        if (f.kind === 'unsupported' || f.kind === 'string-list') {
          return (
            <p key={f.name} className="text-[12px] text-[var(--text-faint)] md:col-span-2">
              {f.label} is edited on the page itself.
            </p>
          );
        }

        if (f.kind === 'boolean') {
          return (
            <label key={f.name} className="flex items-center gap-2 self-end pb-1.5 text-[13px] text-[var(--text-secondary)]">
              <input id={id} type="checkbox" checked={Boolean(value)}
                     onChange={(e) => set(f.name, e.target.checked)} />
              {f.label}
            </label>
          );
        }

        if (f.kind === 'select') {
          return (
            <div key={f.name}>
              <label htmlFor={id} className={labelStyle}>{f.label}</label>
              <select id={id} value={String(value ?? '')} className={control}
                      onChange={(e) => set(f.name, e.target.value)}>
                {(f.options ?? []).map((o) => <option key={o} value={o}>{o}</option>)}
              </select>
            </div>
          );
        }

        if (f.kind === 'textarea') {
          return (
            <div key={f.name} className="md:col-span-2">
              <label htmlFor={id} className={labelStyle}>{f.label}</label>
              <textarea id={id} rows={4} value={String(value ?? '')} className={`${control} resize-y`}
                        onChange={(e) => set(f.name, e.target.value)} />
            </div>
          );
        }

        return (
          <div key={f.name}>
            <label htmlFor={id} className={labelStyle}>
              {f.label}{f.required ? '' : ' (optional)'}
            </label>
            <input
              id={id}
              type={f.kind === 'number' ? 'number' : f.kind === 'datetime' ? 'datetime-local' : f.kind === 'colour' ? 'color' : 'text'}
              value={String(value ?? '')}
              className={control}
              placeholder={f.kind === 'url' ? 'https://' : undefined}
              onChange={(e) => set(f.name, f.kind === 'number' ? Number(e.target.value) : e.target.value)}
            />
          </div>
        );
      })}
    </div>
  );
}

/* ------------------------------------------------------------------ preview */

/**
 * One renderer per family, not per type.
 *
 * Twelve cases cover all 84 blocks, which is the entire argument for the
 * catalogue being data. A thirteenth type in the `link` family needs no code
 * here at all — and one in a family we have not drawn falls through to a label,
 * so the builder still works while the renderer catches up.
 */
function Rendered({ block }: { block: BuilderBlock }) {
  const s = block.settings as Record<string, string | undefined>;

  switch (block.family) {
    case 'link':
    case 'file':
    case 'commerce':
      return (
        <div className="truncate rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-3 py-2 text-center text-[13px] text-[var(--text-primary)]">
          {s.label || s.title || block.label}
        </div>
      );
    case 'text':
      return (
        <p className="text-[13px] leading-relaxed text-[var(--text-secondary)]">
          {s.text || s.title || <span className="text-[var(--text-faint)]">{block.label}</span>}
        </p>
      );
    case 'media':
      return (
        <div className="grid h-20 place-items-center rounded-[4px] border border-dashed border-[var(--border-hairline)] text-[12px] text-[var(--text-faint)]">
          {s.url ? 'Image' : `${block.label} — add a URL`}
        </div>
      );
    case 'embed':
      return (
        <div className="grid h-24 place-items-center rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] text-[12px] text-[var(--text-faint)]">
          {block.label}
        </div>
      );
    case 'form':
      return (
        <div className="rounded-[4px] border border-[var(--border-hairline)] p-2">
          <p className="text-[12px] text-[var(--text-secondary)]">{s.title || block.label}</p>
          <div className="mt-1.5 h-7 rounded-[4px] bg-[var(--surface-ground)]" />
          <div className="mt-1.5 rounded-[4px] bg-[var(--accent-solid)] py-1 text-center text-[11px] text-[var(--on-accent)]">
            {s.buttonLabel || 'Subscribe'}
          </div>
        </div>
      );
    case 'card':
      return (
        <div className="rounded-[4px] border border-[var(--border-hairline)] p-2 text-[12px] text-[var(--text-secondary)]">
          <p className="text-[var(--text-primary)]">{s.title || s.name || block.label}</p>
          {s.description || s.quote ? <p className="mt-0.5">{s.description || s.quote}</p> : null}
        </div>
      );
    case 'list':
      return (
        <div className="grid gap-1">
          {[0, 1].map((i) => (
            <div key={i} className="h-5 rounded-[4px] bg-[var(--surface-ground)]" />
          ))}
        </div>
      );
    case 'widget':
      return (
        <div className="rounded-[4px] border border-[var(--border-hairline)] py-2 text-center text-[13px] tabular-nums text-[var(--text-primary)]">
          {s.label || block.label}
        </div>
      );
    case 'layout':
      return <div className="h-px bg-[var(--border-hairline)]" />;
    default:
      return (
        <div className="rounded-[4px] border border-dashed border-[var(--border-hairline)] py-2 text-center text-[12px] text-[var(--text-faint)]">
          {block.label}
        </div>
      );
  }
}
