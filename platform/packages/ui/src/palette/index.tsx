'use client';

import { useCallback, useEffect, useId, useMemo, useRef, useState } from 'react';
import { rank } from './match.ts';

/* ------------------------------------------------------------------- types */

export type PaletteItem = {
  key: string;
  label: string;
  /** Shown after the label — the tool, the section, or a resource's host. */
  hint?: string;
  section: string;
  /** Extra words to match on that are not worth showing. */
  keywords?: string;
  /** Right-aligned shortcut hint, e.g. "g a". */
  shortcut?: string;
  /** Navigation target, or an action. Exactly one. */
  href?: string;
  run?: () => void | Promise<void>;
};

export type PaletteProps = {
  open: boolean;
  onClose: () => void;
  items: PaletteItem[];
  /**
   * Live cross-tool resource lookup. Called debounced; its results are merged
   * in under their own section. Left undefined the palette is purely local.
   */
  onSearch?: (query: string) => Promise<PaletteItem[]>;
  /** Navigation is the host's concern — the palette does not import a router. */
  onNavigate: (href: string) => void;
  placeholder?: string;
  /** Ordering for sections; anything unlisted falls to the end. */
  sectionOrder?: string[];
};

const DEFAULT_SECTIONS = ['Actions', 'Go to', 'Results', 'Help'];

/* -------------------------------------------------------------- component */

/**
 * ⌘K. Actions, navigation and resources across every tool in one list.
 *
 * Implemented as a combobox over a listbox rather than a menu: the user is
 * filtering a set, not picking from a fixed menu, and that is the pattern
 * screen readers announce correctly (`aria-activedescendant` keeps real focus
 * in the input while the highlight moves through the options).
 */
export function CommandPalette({
  open,
  onClose,
  items,
  onSearch,
  onNavigate,
  placeholder = 'Search actions, pages and resources…',
  sectionOrder = DEFAULT_SECTIONS,
}: PaletteProps) {
  const [query, setQuery] = useState('');
  const [remote, setRemote] = useState<PaletteItem[]>([]);
  const [searching, setSearching] = useState(false);
  const [active, setActive] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);
  const listRef = useRef<HTMLDivElement>(null);
  const restoreTo = useRef<HTMLElement | null>(null);
  const baseId = useId();

  /* --- open/close lifecycle --- */

  useEffect(() => {
    if (!open) return;
    // Remember who had focus so Escape puts it back; losing your place in the
    // page is the thing that makes keyboard users abandon a palette.
    restoreTo.current = document.activeElement as HTMLElement | null;
    setQuery('');
    setRemote([]);
    setActive(0);
    const t = setTimeout(() => inputRef.current?.focus(), 0);
    return () => clearTimeout(t);
  }, [open]);

  const close = useCallback(() => {
    onClose();
    restoreTo.current?.focus?.();
  }, [onClose]);

  /* --- remote search, debounced --- */

  useEffect(() => {
    if (!open || !onSearch) return;
    const q = query.trim();
    if (q.length < 2) {
      setRemote([]);
      setSearching(false);
      return;
    }
    setSearching(true);
    let cancelled = false;
    const t = setTimeout(async () => {
      try {
        const found = await onSearch(q);
        // A slow response for an old query must never overwrite a newer one.
        if (!cancelled) setRemote(found);
      } catch {
        if (!cancelled) setRemote([]);
      } finally {
        if (!cancelled) setSearching(false);
      }
    }, 160);
    return () => {
      cancelled = true;
      clearTimeout(t);
    };
  }, [query, open, onSearch]);

  /* --- the visible list --- */

  const results = useMemo(() => {
    const local = rank(items, query, (i) => `${i.label} ${i.hint ?? ''} ${i.keywords ?? ''}`);
    const all = [...local, ...remote];
    const bySection = new Map<string, PaletteItem[]>();
    for (const item of all) {
      bySection.set(item.section, [...(bySection.get(item.section) ?? []), item]);
    }
    const order = (s: string) => {
      const i = sectionOrder.indexOf(s);
      return i === -1 ? sectionOrder.length : i;
    };
    return [...bySection.entries()]
      .sort((a, b) => order(a[0]) - order(b[0]))
      .map(([section, items]) => ({ section, items }));
  }, [items, remote, query, sectionOrder]);

  // Flattened, because arrow keys move through the list and not through sections.
  const flat = useMemo(() => results.flatMap((g) => g.items), [results]);

  useEffect(() => {
    setActive((a) => (a >= flat.length ? 0 : a));
  }, [flat.length]);

  const choose = useCallback(
    async (item: PaletteItem | undefined) => {
      if (!item) return;
      close();
      if (item.href) onNavigate(item.href);
      else await item.run?.();
    },
    [close, onNavigate],
  );

  /* --- keyboard --- */

  const onKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'ArrowDown' || (e.key === 'n' && e.ctrlKey)) {
      e.preventDefault();
      setActive((a) => (flat.length ? (a + 1) % flat.length : 0));
    } else if (e.key === 'ArrowUp' || (e.key === 'p' && e.ctrlKey)) {
      e.preventDefault();
      setActive((a) => (flat.length ? (a - 1 + flat.length) % flat.length : 0));
    } else if (e.key === 'Home') {
      e.preventDefault();
      setActive(0);
    } else if (e.key === 'End') {
      e.preventDefault();
      setActive(Math.max(0, flat.length - 1));
    } else if (e.key === 'Enter') {
      e.preventDefault();
      void choose(flat[active]);
    } else if (e.key === 'Escape') {
      e.preventDefault();
      close();
    } else if (e.key === 'Tab') {
      // The dialog is the whole interaction; there is nowhere else to tab to.
      e.preventDefault();
    }
  };

  // Keep the highlighted row in view when the keyboard, not the mouse, moves it.
  useEffect(() => {
    if (!open) return;
    const el = listRef.current?.querySelector(`[data-index="${active}"]`);
    el?.scrollIntoView({ block: 'nearest' });
  }, [active, open]);

  if (!open) return null;

  const activeId = flat[active] ? `${baseId}-opt-${active}` : undefined;

  return (
    <div
      className="fixed inset-0 z-50 flex items-start justify-center px-4 pt-[12vh]"
      role="presentation"
      onMouseDown={(e) => {
        if (e.target === e.currentTarget) close();
      }}
    >
      <div className="absolute inset-0 bg-[color-mix(in_srgb,var(--text-primary)_35%,transparent)]" />

      <div
        role="dialog"
        aria-modal="true"
        aria-label="Command palette"
        className="relative flex max-h-[70vh] w-full max-w-[560px] flex-col overflow-hidden rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)]"
      >
        <div className="flex items-center gap-3 border-b border-[var(--border-hairline)] px-4">
          <input
            ref={inputRef}
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setActive(0);
            }}
            onKeyDown={onKeyDown}
            placeholder={placeholder}
            aria-label="Search actions, pages and resources"
            role="combobox"
            aria-expanded
            aria-controls={`${baseId}-list`}
            aria-activedescendant={activeId}
            aria-autocomplete="list"
            autoComplete="off"
            spellCheck={false}
            className="h-12 w-full min-w-0 bg-transparent text-[14px] text-[var(--text-primary)] placeholder:text-[var(--text-faint)] focus:outline-none"
          />
          <kbd className="shrink-0 rounded-[3px] border border-[var(--border-hairline)] px-1.5 py-0.5 text-[10px] text-[var(--text-muted)]">
            esc
          </kbd>
        </div>

        <div
          ref={listRef}
          id={`${baseId}-list`}
          role="listbox"
          aria-label="Results"
          className="min-h-0 flex-1 overflow-y-auto overscroll-contain py-2"
        >
          {flat.length === 0 ? (
            <p className="px-4 py-8 text-center text-[13px] text-[var(--text-muted)]">
              {searching ? 'Searching…' : `Nothing matches “${query}”.`}
            </p>
          ) : (
            results.map((group) => (
              <div key={group.section} role="group" aria-label={group.section}>
                <div className="px-4 pb-1 pt-3 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
                  {group.section}
                </div>
                {group.items.map((item) => {
                  const index = flat.indexOf(item);
                  const isActive = index === active;
                  return (
                    <div
                      key={item.key}
                      id={`${baseId}-opt-${index}`}
                      data-index={index}
                      role="option"
                      aria-selected={isActive}
                      onMouseMove={() => setActive(index)}
                      onClick={() => void choose(item)}
                      className={`flex cursor-pointer items-center gap-3 px-4 py-2 text-[14px] ${
                        isActive
                          ? 'bg-[var(--accent-wash)] text-[var(--text-primary)]'
                          : 'text-[var(--text-secondary)]'
                      }`}
                    >
                      <span className="min-w-0 flex-1 truncate">{item.label}</span>
                      {item.hint ? (
                        <span className="shrink-0 truncate text-[12px] text-[var(--text-muted)]">
                          {item.hint}
                        </span>
                      ) : null}
                      {item.shortcut ? (
                        <kbd className="shrink-0 rounded-[3px] border border-[var(--border-hairline)] px-1.5 py-0.5 text-[10px] text-[var(--text-muted)]">
                          {item.shortcut}
                        </kbd>
                      ) : null}
                    </div>
                  );
                })}
              </div>
            ))
          )}
        </div>

        <div className="flex items-center gap-4 border-t border-[var(--border-hairline)] px-4 py-2 text-[11px] text-[var(--text-muted)]">
          <span>↑↓ navigate</span>
          <span>↵ open</span>
          <span className="ml-auto">? for shortcuts</span>
        </div>
      </div>
    </div>
  );
}
