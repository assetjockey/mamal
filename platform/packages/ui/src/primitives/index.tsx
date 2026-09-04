import type { ReactNode } from 'react';

const cx = (...parts: (string | false | null | undefined)[]) => parts.filter(Boolean).join(' ');

/* ------------------------------------------------------------------ button */

export function Button({
  children,
  variant = 'primary',
  size = 'md',
  ...rest
}: {
  children: ReactNode;
  variant?: 'primary' | 'ghost' | 'tertiary' | 'quiet';
  size?: 'sm' | 'md';
} & React.ButtonHTMLAttributes<HTMLButtonElement>) {
  // 4px radius, no shadow in any state. Hover lightens the fill rather than
  // lifting the surface — depth is never elevation here.
  const base =
    'inline-flex items-center gap-2 rounded-[4px] font-normal transition-colors ' +
    'duration-[120ms] ease-[cubic-bezier(0.2,0,0,1)] disabled:opacity-50 ' +
    'disabled:pointer-events-none focus-visible:outline-2 focus-visible:outline-offset-2 ' +
    'focus-visible:outline-[var(--accent)]';
  const sizes = { sm: 'h-8 px-3 text-[13px]', md: 'h-10 px-6 text-[14px]' };
  const variants = {
    primary:
      'bg-[var(--accent-solid)] text-[var(--on-accent)] hover:bg-[var(--accent-solid-hover)]',
    ghost:
      'border border-[var(--color-lavender-border)] text-[var(--accent)] hover:bg-[var(--accent-wash)]',
    tertiary:
      'border border-[var(--color-lilac-border)] text-[var(--accent)] hover:bg-[var(--accent-wash)]',
    quiet: 'text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]',
  };
  return (
    <button className={cx(base, sizes[size], variants[variant])} {...rest}>
      {children}
    </button>
  );
}

/* -------------------------------------------------------------------- card */

export function Card({
  children,
  className,
  padded = true,
}: {
  children: ReactNode;
  className?: string;
  padded?: boolean;
}) {
  return (
    <div
      className={cx(
        'rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)]',
        padded && 'p-6',
        className,
      )}
    >
      {children}
    </div>
  );
}

/* ------------------------------------------------------------- page header */

export function PageHeader({
  title,
  description,
  action,
}: {
  title: string;
  description?: string;
  action?: ReactNode;
}) {
  return (
    <header className="mb-8 flex flex-wrap items-start justify-between gap-4">
      <div className="min-w-0">
        <h1 className="text-[26px] leading-[1.12] tracking-[-0.26px] text-[var(--text-primary)]">
          {title}
        </h1>
        {description ? (
          <p className="mt-2 max-w-2xl text-[14px] leading-[1.4] text-[var(--text-secondary)]">
            {description}
          </p>
        ) : null}
      </div>
      {/*
        Not shrink-0: an action slot that cannot be narrowed can never wrap, so
        three buttons push the whole page past a phone viewport. The header
        already wraps, and this lets the slot take a line of its own.
      */}
      {action ? <div className="min-w-0 max-w-full">{action}</div> : null}
    </header>
  );
}

/* ------------------------------------------------------------------- badge */

export type Status = 'ok' | 'warn' | 'error' | 'info' | 'neutral';

export function StatusBadge({ status, children }: { status: Status; children: ReactNode }) {
  const tones: Record<Status, string> = {
    ok: 'bg-[var(--color-status-ok-wash)] text-[var(--color-status-ok)]',
    warn: 'bg-[var(--color-status-warn-wash)] text-[var(--color-status-warn)]',
    error: 'bg-[var(--color-status-error-wash)] text-[var(--color-status-error)]',
    info: 'bg-[var(--color-status-info-wash)] text-[var(--color-status-info)]',
    neutral: 'bg-[var(--surface-band)] text-[var(--text-secondary)]',
  };
  return (
    <span
      className={cx(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-[12px] font-normal',
        tones[status],
      )}
    >
      {children}
    </span>
  );
}

/* --------------------------------------------------------------- stat tile */

export function StatTile({
  label,
  value,
  hint,
}: {
  label: string;
  value: ReactNode;
  hint?: string;
}) {
  return (
    <div className="border-l border-[var(--border-hairline)] pl-4 first:border-l-0 first:pl-0">
      <div className="text-[12px] uppercase tracking-[0.5px] text-[var(--text-faint)]">{label}</div>
      {/* Weight 300 at large size: the number carries the weight, not the chrome. */}
      <div className="mt-1 text-[32px] leading-[1.1] tracking-[-0.64px] tabular-nums">{value}</div>
      {hint ? <div className="mt-1 text-[12px] text-[var(--text-muted)]">{hint}</div> : null}
    </div>
  );
}

/* ------------------------------------------------------------------- table */

export function Table({ children, label }: { children: ReactNode; label?: string }) {
  // Wide content scrolls inside its own container; the page body never does.
  // A container that scrolls must be reachable by keyboard, or the columns past
  // the fold are unreadable without a mouse (axe: scrollable-region-focusable).
  // role="region" is only applied alongside a name — an unnamed region is worse
  // than none, so a labelled table announces itself and an unlabelled one is
  // simply a focusable scroller.
  return (
    <div
      tabIndex={0}
      {...(label ? { role: 'region', 'aria-label': label } : {})}
      className="overflow-x-auto rounded-[4px] border border-[var(--border-hairline)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--accent)]"
    >
      <table className="w-full min-w-[560px] border-collapse text-left">{children}</table>
    </div>
  );
}

export function Th({ children, align }: { children: ReactNode; align?: 'right' }) {
  return (
    <th
      className={cx(
        'border-b border-[var(--border-hairline)] bg-[var(--surface-band)] px-4 py-2.5',
        'text-[12px] font-normal uppercase tracking-[0.5px] text-[var(--text-faint)]',
        align === 'right' && 'text-right',
      )}
    >
      {children}
    </th>
  );
}

export function Td({
  children,
  align,
  muted,
}: {
  children: ReactNode;
  align?: 'right';
  muted?: boolean;
}) {
  return (
    <td
      className={cx(
        'border-b border-[var(--border-hairline)] px-4 py-3 text-[14px]',
        align === 'right' && 'text-right tabular-nums',
        muted && 'text-[var(--text-secondary)]',
      )}
    >
      {children}
    </td>
  );
}

export function Tr({ children }: { children: ReactNode }) {
  return (
    <tr className="transition-colors duration-[120ms] last:[&>td]:border-b-0 hover:bg-[var(--surface-hover)]">
      {children}
    </tr>
  );
}

/* -------------------------------------------------------------- empty state */

export function EmptyState({
  title,
  description,
  action,
}: {
  title: string;
  description: string;
  action?: ReactNode;
}) {
  return (
    <div className="rounded-[4px] border border-dashed border-[var(--border-hairline)] px-6 py-14 text-center">
      <h3 className="text-[20px] leading-[1.4] text-[var(--text-primary)]">{title}</h3>
      <p className="mx-auto mt-2 max-w-md text-[14px] leading-[1.4] text-[var(--text-secondary)]">
        {description}
      </p>
      {action ? <div className="mt-6 flex justify-center">{action}</div> : null}
    </div>
  );
}

/* ----------------------------------------------------------------- section */

export function SectionLabel({ children }: { children: ReactNode }) {
  return (
    <div className="mb-3 text-[11px] font-normal uppercase tracking-[0.5px] text-[var(--text-faint)]">
      {children}
    </div>
  );
}

export function Divider() {
  return <hr className="my-10 border-0 border-t border-[var(--border-hairline)]" />;
}

/* ---------------------------------------------------------------- loading */

/**
 * Skeletons, never spinners — a spinner says "wait", a skeleton says "here is
 * the shape of what is coming", and it holds the layout so nothing shifts when
 * the data lands. Animation is a token-driven pulse that
 * `prefers-reduced-motion` flattens along with everything else.
 */
export function Skeleton({ className = '' }: { className?: string }) {
  return (
    <div
      aria-hidden
      className={`animate-pulse rounded-[4px] bg-[var(--surface-hover)] ${className}`}
    />
  );
}

/** The page-level fallback: a header block plus a few rows of content. */
export function PageSkeleton({ rows = 5 }: { rows?: number }) {
  return (
    <div role="status" aria-label="Loading">
      <Skeleton className="h-7 w-56" />
      <Skeleton className="mt-2 h-4 w-80" />
      <div className="mt-8 space-y-2">
        {Array.from({ length: rows }, (_, i) => (
          <Skeleton key={i} className="h-14 w-full" />
        ))}
      </div>
    </div>
  );
}

/* ------------------------------------------------------------------ error */

/**
 * Every error names what to do next. `detail` carries the technical line for
 * someone who can act on it; `retry` is present whenever the failure might be
 * transient, because offering a dead-end is worse than offering nothing.
 */
export function ErrorState({
  title = 'Something went wrong',
  description,
  detail,
  retry,
}: {
  title?: string;
  description: string;
  detail?: string;
  retry?: ReactNode;
}) {
  return (
    <div className="rounded-[4px] border border-[var(--color-status-error)] bg-[var(--color-status-error-wash)] px-6 py-10 text-center">
      <h3 className="text-[20px] leading-[1.4] text-[var(--text-primary)]">{title}</h3>
      <p className="mx-auto mt-2 max-w-md text-[14px] leading-[1.4] text-[var(--text-secondary)]">
        {description}
      </p>
      {detail ? (
        <pre className="mx-auto mt-4 max-w-full overflow-x-auto whitespace-pre-wrap break-words text-left font-mono text-[12px] text-[var(--text-muted)]">
          {detail}
        </pre>
      ) : null}
      {retry ? <div className="mt-6 flex justify-center">{retry}</div> : null}
    </div>
  );
}

/* ------------------------------------------------------------ over-limit */

/**
 * The over-limit state. The rule from the brief is that a gate must name the
 * exact entitlement and its price — "upgrade to continue" tells the user
 * nothing and reads as a shakedown. `used`/`limit` are shown when known so the
 * number that stopped them is the number they see.
 */
export function UpgradeGate({
  feature,
  reason,
  used,
  limit,
  price,
  action,
}: {
  feature: string;
  reason: string;
  used?: number;
  limit?: number;
  price?: string;
  action?: ReactNode;
}) {
  const showMeter = typeof used === 'number' && typeof limit === 'number' && limit > 0;
  return (
    <div className="rounded-[4px] border border-[var(--color-lavender-border)] bg-[var(--accent-wash)] px-6 py-10 text-center">
      <h3 className="text-[20px] leading-[1.4] text-[var(--text-primary)]">{feature}</h3>
      <p className="mx-auto mt-2 max-w-md text-[14px] leading-[1.4] text-[var(--text-secondary)]">
        {reason}
      </p>
      {showMeter ? (
        <div className="mx-auto mt-5 max-w-xs">
          <div className="flex items-baseline justify-between text-[12px] tabular-nums text-[var(--text-muted)]">
            <span>
              {used!.toLocaleString()} of {limit!.toLocaleString()} used
            </span>
            <span>{Math.min(100, Math.round((used! / limit!) * 100))}%</span>
          </div>
          <div className="mt-1.5 h-1 w-full overflow-hidden rounded-[9999px] bg-[var(--surface-hover)]">
            <div
              className="h-full bg-[var(--accent-solid)]"
              style={{ width: `${Math.min(100, (used! / limit!) * 100)}%` }}
            />
          </div>
        </div>
      ) : null}
      {price ? (
        <p className="mt-4 text-[13px] text-[var(--text-muted)]">{price}</p>
      ) : null}
      {action ? <div className="mt-6 flex justify-center">{action}</div> : null}
    </div>
  );
}
