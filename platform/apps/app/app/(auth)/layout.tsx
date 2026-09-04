import '../globals.css';

/**
 * Signed-out chrome: no rail, no context nav. The marketing typography at
 * full strength — 56px at weight 300, left aligned, no hero image.
 */
export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" data-theme="light" suppressHydrationWarning>
      <body suppressHydrationWarning>
        <div className="grid min-h-dvh lg:grid-cols-2">
          <div className="flex items-center justify-center px-6 py-16">
            <div className="w-full max-w-sm">{children}</div>
          </div>
          <aside className="hidden flex-col justify-center border-l border-[var(--border-hairline)] bg-[var(--surface-band)] px-12 lg:flex">
            <div className="max-w-md">
              <div className="mb-6 flex size-10 items-center justify-center rounded-[4px] bg-[var(--accent-solid)] text-[var(--on-accent)]">
                M
              </div>
              <h2 className="text-[48px] leading-[1.03] tracking-[-0.96px] text-[var(--text-primary)]">
                Six tools.
                <br />
                One workspace.
              </h2>
              <p className="mt-6 text-[20px] leading-[1.4] text-[var(--text-secondary)]">
                Audit, Confirm, Link, Market, Monitor and Track — each useful on its own, and able
                to hand work to each other.
              </p>
              <ul className="mt-10 space-y-3 text-[14px] text-[var(--text-muted)]">
                <li>An audit finds a broken link, and Monitor starts watching it.</li>
                <li>A real conversion in Track becomes social proof in Confirm.</li>
                <li>A published post gets shortened, tagged and attributed automatically.</li>
              </ul>
            </div>
          </aside>
        </div>
      </body>
    </html>
  );
}
