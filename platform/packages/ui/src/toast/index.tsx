'use client';

import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';

/**
 * Toasts, and the undo window that replaces confirmation dialogs.
 *
 * The house rule from the brief: a destructive action is *undoable for ten
 * seconds*, not guarded by an "are you sure?". A dialog taxes every correct
 * action to protect against the rare wrong one, and people learn to dismiss it
 * without reading — so it stops working exactly when it matters. An undo window
 * costs nothing when the user meant it and fully recovers when they did not.
 *
 * Anything genuinely unrecoverable (deleting a workspace, cancelling a plan)
 * still gets a dialog; those are excluded by policy, not by oversight.
 */

export type ToastKind = 'info' | 'ok' | 'error';

export type ToastSpec = {
  message: string;
  kind?: ToastKind;
  /** Present an Undo button; the toast stays until the window closes. */
  onUndo?: () => void | Promise<void>;
  /** Milliseconds before auto-dismiss. Undoable toasts default to 10s. */
  duration?: number;
};

type Toast = ToastSpec & { id: number; expiresAt: number };

const ToastContext = createContext<((spec: ToastSpec) => void) | null>(null);

/** Fire a toast. Throws outside the provider so a missing mount is loud. */
export function useToast() {
  const push = useContext(ToastContext);
  if (!push) throw new Error('useToast requires <ToastProvider>');
  return push;
}

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const nextId = useRef(1);

  const dismiss = useCallback((id: number) => {
    setToasts((t) => t.filter((x) => x.id !== id));
  }, []);

  const push = useCallback((spec: ToastSpec) => {
    const duration = spec.duration ?? (spec.onUndo ? 10_000 : 4_000);
    const id = nextId.current++;
    setToasts((t) => [...t, { ...spec, id, expiresAt: Date.now() + duration }]);
  }, []);

  // One timer for the whole stack rather than one per toast: N timers means N
  // chances to leak a handle on unmount, and the tick is cheap.
  useEffect(() => {
    if (toasts.length === 0) return;
    const t = setInterval(() => {
      const now = Date.now();
      setToasts((list) => list.filter((x) => x.expiresAt > now));
    }, 250);
    return () => clearInterval(t);
  }, [toasts.length]);

  const value = useMemo(() => push, [push]);

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div
        // polite, not assertive: a toast reports what already happened, and
        // interrupting a screen reader mid-sentence to say so is hostile.
        role="status"
        aria-live="polite"
        className="pointer-events-none fixed bottom-4 left-1/2 z-50 flex w-[min(28rem,calc(100vw-2rem))] -translate-x-1/2 flex-col gap-2"
      >
        {toasts.map((t) => (
          <ToastRow key={t.id} toast={t} onDismiss={() => dismiss(t.id)} />
        ))}
      </div>
    </ToastContext.Provider>
  );
}

function ToastRow({ toast, onDismiss }: { toast: Toast; onDismiss: () => void }) {
  const [undoing, setUndoing] = useState(false);
  const border =
    toast.kind === 'error'
      ? 'border-[var(--color-status-error)]'
      : toast.kind === 'ok'
        ? 'border-[var(--color-status-ok)]'
        : 'border-[var(--border-hairline)]';

  return (
    <div
      className={`pointer-events-auto flex items-center gap-3 rounded-[4px] border ${border} bg-[var(--surface-raised)] px-4 py-3`}
    >
      <span className="min-w-0 flex-1 text-[13px] text-[var(--text-primary)]">{toast.message}</span>

      {toast.onUndo ? (
        <button
          disabled={undoing}
          onClick={async () => {
            setUndoing(true);
            try {
              await toast.onUndo!();
            } finally {
              onDismiss();
            }
          }}
          className="shrink-0 rounded-[4px] px-2 py-1 text-[13px] text-[var(--accent)] hover:bg-[var(--accent-wash)] disabled:opacity-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
        >
          {undoing ? 'Undoing…' : 'Undo'}
        </button>
      ) : null}

      <button
        onClick={onDismiss}
        aria-label="Dismiss"
        className="shrink-0 rounded-[4px] px-1.5 py-1 text-[13px] text-[var(--text-faint)] hover:text-[var(--text-secondary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
      >
        ✕
      </button>
    </div>
  );
}
