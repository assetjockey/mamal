'use client';

import { useEffect, useRef, useState } from 'react';

export type ChecklistStep = {
  key: string;
  label: string;
  hint?: string;
  href: string;
  done: boolean;
};

/**
 * The docked setup checklist.
 *
 * Steps are *derived from state*, never from a "I clicked the button" flag —
 * a checklist that can disagree with the product is worse than no checklist,
 * because it tells a user to do something they already did. So "ran an audit"
 * means an audit row exists, not that a step was marked.
 *
 * It docks bottom-right on desktop and becomes a normal block below `lg`,
 * where a floating panel would cover the content it is pointing at.
 */
export function SetupChecklist({
  steps,
  onDismiss,
  title = 'Get set up',
}: {
  steps: ChecklistStep[];
  onDismiss?: () => void | Promise<void>;
  title?: string;
}) {
  /*
   * Collapsed by default.
   *
   * Expanded, the dock is 320×409 — a third of the right-hand side of a
   * 1280×800 screen — and it is `fixed`, so everything under it stops taking
   * clicks. On the dashboard that was two of the six tool cards, on every page
   * load, for every new user. Collapsed it is a progress bar and a count, which
   * is enough to invite the click that opens it.
   */
  const [open, setOpen] = useState(false);
  const dock = useRef<HTMLElement>(null);
  const [reserved, setReserved] = useState(0);

  /*
   * Reserve exactly as much room as the dock occupies.
   *
   * A floating panel that overlaps the page is not a cosmetic problem: the
   * controls underneath stop receiving clicks and nothing on screen says so.
   * The height is measured rather than guessed because it changes when the
   * panel is expanded or collapsed, and a fixed guess would be wrong half the
   * time in each direction.
   */
  useEffect(() => {
    const el = dock.current;
    if (!el || typeof ResizeObserver === 'undefined') return;
    const sync = () => {
      // Only while it is actually docked — below `lg` it is a normal block and
      // already takes its own space.
      const floating = getComputedStyle(el).position === 'fixed';
      setReserved(floating ? el.offsetHeight + 32 : 0);
    };
    sync();
    const observer = new ResizeObserver(sync);
    observer.observe(el);
    window.addEventListener('resize', sync);
    return () => {
      observer.disconnect();
      window.removeEventListener('resize', sync);
    };
  }, [open, steps.length]);

  const done = steps.filter((s) => s.done).length;
  const pct = steps.length ? Math.round((done / steps.length) * 100) : 0;
  const complete = done === steps.length;

  return (
    <>
      {/* In flow, so the last row of the page clears the dock. */}
      <div aria-hidden style={{ height: reserved }} />
    <aside
      ref={dock}
      aria-label={title}
      /* A stable hook: the title is customisable, and "the aside" is ambiguous
         once the context nav is on screen. */
      data-dock="checklist"
      /*
        Docked, and it reserves the space it occupies.
        A floating panel that overlaps the page is not a cosmetic problem: the
        controls underneath stop being clickable, and nothing on screen says so.
        The spacer below is in flow, so the last row of content clears the dock
        instead of hiding beneath it.
      */
      className="z-30 mt-8 w-full lg:fixed lg:bottom-4 lg:right-4 lg:mt-0 lg:w-[20rem]"
    >
      <div className="rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)]">
        <div className="flex items-center gap-2 px-4 py-3">
          <button
            onClick={() => setOpen((v) => !v)}
            aria-expanded={open}
            className="flex min-w-0 flex-1 items-baseline gap-2 text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
          >
            <span className="truncate text-[14px] text-[var(--text-primary)]">{title}</span>
            <span className="shrink-0 text-[12px] tabular-nums text-[var(--text-muted)]">
              {done}/{steps.length}
            </span>
          </button>
          {onDismiss ? (
            <button
              onClick={() => void onDismiss()}
              aria-label="Dismiss setup checklist"
              className="shrink-0 rounded-[4px] px-1.5 py-1 text-[13px] text-[var(--text-faint)] hover:text-[var(--text-secondary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
            >
              ✕
            </button>
          ) : null}
        </div>

        <div
          role="progressbar"
          aria-valuenow={pct}
          aria-valuemin={0}
          aria-valuemax={100}
          aria-label={`${pct}% complete`}
          className="h-0.5 w-full bg-[var(--surface-hover)]"
        >
          <div
            className="h-full bg-[var(--accent-solid)] transition-[width] duration-[320ms] ease-[cubic-bezier(0.2,0,0,1)]"
            style={{ width: `${pct}%` }}
          />
        </div>

        {open ? (
          <ol className="px-2 py-2">
            {steps.map((step) => (
              <li key={step.key}>
                <a
                  href={step.href}
                  className="flex items-start gap-2.5 rounded-[4px] px-2 py-1.5 hover:bg-[var(--surface-hover)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
                >
                  <span
                    aria-hidden
                    className={`mt-[3px] flex size-3.5 shrink-0 items-center justify-center rounded-full border text-[9px] ${
                      step.done
                        ? 'border-[var(--color-status-ok)] bg-[var(--color-status-ok)] text-[var(--surface-raised)]'
                        : 'border-[var(--border-hairline)]'
                    }`}
                  >
                    {step.done ? '✓' : ''}
                  </span>
                  <span className="min-w-0 flex-1">
                    <span
                      className={`block text-[13px] ${
                        step.done
                          ? 'text-[var(--text-faint)] line-through'
                          : 'text-[var(--text-secondary)]'
                      }`}
                    >
                      {step.label}
                    </span>
                    {step.hint && !step.done ? (
                      <span className="mt-0.5 block text-[12px] text-[var(--text-faint)]">
                        {step.hint}
                      </span>
                    ) : null}
                  </span>
                  {/* The screen-reader equivalent of the strikethrough. */}
                  <span className="sr-only">{step.done ? '(done)' : '(not done)'}</span>
                </a>
              </li>
            ))}
            {complete ? (
              <li className="px-2 pb-1 pt-2 text-[12px] text-[var(--color-status-ok)]">
                That is everything Audit needs. More steps appear as tools are added.
              </li>
            ) : null}
          </ol>
        ) : null}
      </div>
    </aside>
    </>
  );
}
