'use client';

import type { ReactNode } from 'react';

export function AuthForm({
  title, submitLabel, children, onSubmit, error, pending, footer,
}: {
  title: string;
  submitLabel: string;
  children: ReactNode;
  onSubmit: (data: FormData) => void | Promise<void>;
  error?: string | null;
  pending?: boolean;
  footer?: ReactNode;
}) {
  return (
    <form
      action={onSubmit}
      className="space-y-5"
      noValidate={false}
    >
      <h1 className="text-[32px] leading-[1.1] tracking-[-0.64px] text-[var(--text-primary)]">
        {title}
      </h1>

      {error ? (
        <p
          role="alert"
          className="rounded-[4px] bg-[var(--color-status-error-wash)] px-3 py-2 text-[13px] text-[var(--color-status-error)]"
        >
          {error}
        </p>
      ) : null}

      {children}

      <button
        type="submit"
        disabled={pending}
        className="h-10 w-full rounded-[4px] bg-[var(--accent-solid)] px-6 text-[14px] text-[var(--on-accent)] transition-colors duration-[120ms] hover:bg-[var(--accent-solid-hover)] disabled:opacity-50"
      >
        {pending ? 'Working…' : submitLabel}
      </button>

      {footer ? (
        <p className="text-[13px] text-[var(--text-secondary)]">{footer}</p>
      ) : null}
    </form>
  );
}

export function Field({
  name, label, hint, ...rest
}: { name: string; label: string; hint?: string } & React.InputHTMLAttributes<HTMLInputElement>) {
  return (
    <div>
      <label htmlFor={name} className="mb-1.5 block text-[13px] text-[var(--text-secondary)]">
        {label}
      </label>
      <input
        id={name}
        name={name}
        className="h-10 w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-3 text-[14px] text-[var(--text-primary)] outline-none transition-colors duration-[120ms] focus:border-[var(--accent)]"
        {...rest}
      />
      {hint ? <p className="mt-1 text-[12px] text-[var(--text-faint)]">{hint}</p> : null}
    </div>
  );
}
