/**
 * The transfer download page.
 *
 * Every unavailable state says *why* — expired, pulled back with the sender's
 * reason, out of downloads — because the alternative is a recipient who has to
 * go back and ask, and the sender who has to guess what they saw.
 */

const mb = (bytes: number) =>
  bytes >= 1_000_000_000
    ? `${(bytes / 1_000_000_000).toFixed(1)} GB`
    : `${(bytes / 1_000_000).toFixed(bytes < 10_000_000 ? 1 : 0)} MB`;

export function TransferPage({
  transfer,
  files,
}: {
  transfer: {
    id: string; subject: string | null; message: string | null; senderName: string | null;
    cancelReason: string | null; expired: boolean; exhausted: boolean;
    needsPassword: boolean; totalFiles: number; totalBytes: number;
  };
  files: { id: string; name: string; sizeBytes: number }[];
}) {
  const unavailable =
    transfer.cancelReason ? { title: 'This transfer was pulled back', body: transfer.cancelReason }
    : transfer.expired ? { title: 'This transfer has expired', body: 'Ask the sender for a new link.' }
    : transfer.exhausted ? { title: 'This transfer has been fully downloaded', body: 'It reached its download limit.' }
    : null;

  return (
    <main className="mx-auto grid min-h-dvh w-full max-w-[560px] content-start gap-6 px-4 py-12">
      <header>
        <h1 className="text-[26px] font-light tracking-[-0.01em] text-[var(--text-primary)]">
          {transfer.subject ?? 'Files for you'}
        </h1>
        <p className="mt-1 text-[14px] text-[var(--text-muted)]">
          {transfer.senderName ? `From ${transfer.senderName} · ` : ''}
          {transfer.totalFiles} file{transfer.totalFiles === 1 ? '' : 's'} · {mb(transfer.totalBytes)}
        </p>
      </header>

      {transfer.message ? (
        <p className="whitespace-pre-line text-[15px] leading-relaxed text-[var(--text-secondary)]">
          {transfer.message}
        </p>
      ) : null}

      {unavailable ? (
        <section
          role="status"
          className="rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] p-4"
        >
          <h2 className="text-[16px] text-[var(--text-primary)]">{unavailable.title}</h2>
          <p className="mt-1 text-[14px] text-[var(--text-secondary)]">{unavailable.body}</p>
        </section>
      ) : (
        <>
          {/*
            One form per file, each posting its own `fileId`.

            Every download is claimed and counted separately, and the response
            is a redirect to a short-lived signed URL — so the bytes come from
            the object store, not from this process, and a saved URL cannot be
            replayed past its expiry or past the download limit.
          */}
          <ul className="grid gap-2">
            {files.map((f) => (
              <li
                key={f.id}
                className="flex items-center justify-between gap-3 rounded-[4px] border border-[var(--border-hairline)] px-3 py-2"
              >
                <span className="min-w-0 truncate text-[14px] text-[var(--text-primary)]">{f.name}</span>
                <span className="flex shrink-0 items-center gap-3">
                  <span className="text-[13px] tabular-nums text-[var(--text-faint)]">
                    {mb(f.sizeBytes)}
                  </span>
                  {files.length > 1 ? (
                    <form method="post" action={`/api/link/transfers/${transfer.id}/download`}>
                      <input type="hidden" name="fileId" value={f.id} />
                      {transfer.needsPassword ? (
                        <input type="hidden" name="password" value="" data-password-mirror />
                      ) : null}
                      <button
                        type="submit"
                        className="rounded-[4px] border border-[var(--border-hairline)] px-2.5 py-1 text-[13px] text-[var(--text-primary)]"
                      >
                        Download
                      </button>
                    </form>
                  ) : null}
                </span>
              </li>
            ))}
          </ul>

          {files.length === 0 ? (
            <p className="text-[14px] text-[var(--text-muted)]">
              The sender has not finished uploading yet. Check back shortly.
            </p>
          ) : (
            <form method="post" action={`/api/link/transfers/${transfer.id}/download`} className="grid gap-3">
              {transfer.needsPassword ? (
                <div>
                  <label
                    htmlFor="transfer-password"
                    className="mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]"
                  >
                    Password
                  </label>
                  <input
                    id="transfer-password"
                    name="password"
                    type="password"
                    required
                    autoComplete="off"
                    className="w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[16px] text-[var(--text-primary)]"
                  />
                </div>
              ) : null}
              <button
                type="submit"
                className="rounded-[4px] bg-[var(--accent-solid)] px-4 py-2.5 text-[15px] text-[var(--on-accent)]"
              >
                {files.length === 1 ? 'Download file' : 'Unlock the files above'}
              </button>
              {files.length > 1 ? (
                <p className="text-[13px] text-[var(--text-muted)]">
                  Each file downloads on its own. We do not zip them, because
                  every byte would have to travel through us and back out again.
                </p>
              ) : null}
            </form>
          )}
        </>
      )}
    </main>
  );
}
