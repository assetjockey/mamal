'use client';

import { useEffect, useRef } from 'react';

export type ShortcutGroup = { title: string; keys: { keys: string[]; label: string }[] };

/**
 * The `?` sheet.
 *
 * Every shortcut the app binds is listed here, because a keyboard affordance
 * nobody can discover is not an affordance. The list is passed in rather than
 * hardcoded so it is generated from the same data that binds the keys — a
 * shortcut cannot drift out of this sheet without also ceasing to exist.
 */
export function ShortcutSheet({
  open,
  onClose,
  groups,
}: {
  open: boolean;
  onClose: () => void;
  groups: ShortcutGroup[];
}) {
  const closeRef = useRef<HTMLButtonElement>(null);
  const restoreTo = useRef<HTMLElement | null>(null);

  useEffect(() => {
    if (!open) return;
    restoreTo.current = document.activeElement as HTMLElement | null;
    const t = setTimeout(() => closeRef.current?.focus(), 0);
    return () => clearTimeout(t);
  }, [open]);

  if (!open) return null;

  const dismiss = () => {
    onClose();
    restoreTo.current?.focus?.();
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center px-4"
      role="presentation"
      onMouseDown={(e) => {
        if (e.target === e.currentTarget) dismiss();
      }}
    >
      <div className="absolute inset-0 bg-[color-mix(in_srgb,var(--text-primary)_35%,transparent)]" />
      <div
        role="dialog"
        aria-modal="true"
        aria-label="Keyboard shortcuts"
        onKeyDown={(e) => {
          if (e.key === 'Escape') dismiss();
          if (e.key === 'Tab') e.preventDefault();
        }}
        className="relative max-h-[80vh] w-full max-w-[520px] overflow-y-auto rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] p-6"
      >
        <div className="mb-5 flex items-start justify-between gap-4">
          <h2 className="text-[20px] leading-[1.4] text-[var(--text-primary)]">
            Keyboard shortcuts
          </h2>
          <button
            ref={closeRef}
            onClick={dismiss}
            className="rounded-[4px] px-2 py-1 text-[13px] text-[var(--text-secondary)] hover:bg-[var(--surface-hover)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
          >
            Close
          </button>
        </div>

        {groups.map((group) => (
          <div key={group.title} className="mb-5 last:mb-0">
            <div className="mb-2 text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
              {group.title}
            </div>
            <dl className="space-y-1.5">
              {group.keys.map((row) => (
                <div key={row.label} className="flex items-baseline gap-4">
                  <dt className="flex shrink-0 gap-1">
                    {row.keys.map((k) => (
                      <kbd
                        key={k}
                        className="rounded-[3px] border border-[var(--border-hairline)] px-1.5 py-0.5 text-[11px] text-[var(--text-muted)]"
                      >
                        {k}
                      </kbd>
                    ))}
                  </dt>
                  <dd className="min-w-0 flex-1 text-[13px] text-[var(--text-secondary)]">
                    {row.label}
                  </dd>
                </div>
              ))}
            </dl>
          </div>
        ))}
      </div>
    </div>
  );
}
