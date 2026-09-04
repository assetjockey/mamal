'use client';

import { useCallback, useRef, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useToast } from '@mamal/ui';
import { notePartUploaded, planFileUpload, readyTransfer, resumeFileUpload } from '../actions';

/**
 * The upload half of a transfer.
 *
 * Every byte goes from this browser straight to the object store through a
 * pre-authorised URL. The server hands out URLs and records what landed; it
 * never sees the file. That is what makes a 5 GB transfer possible on a small
 * box, and it is why the progress bar below is honest — it is counting parts
 * the store has actually accepted, not bytes we have queued.
 *
 * **Resumable, and it has to be.** A 5 GB upload over a domestic connection
 * will be interrupted, and the recovery has to be "carry on from part 41". The
 * client asks which parts are missing and sends only those; the URLs it was
 * given an hour ago have expired, so it gets fresh ones.
 */

type FileState = {
  name: string;
  size: number;
  fileId: string | null;
  done: number;
  total: number;
  error: string | null;
};

/** Two at a time. More saturates a domestic uplink and makes every part slower. */
const CONCURRENCY = 2;

export function Uploader({ transferId }: { transferId: string }) {
  const [files, setFiles] = useState<FileState[]>([]);
  const [busy, setBusy] = useState(false);
  const input = useRef<HTMLInputElement>(null);
  const toast = useToast();
  const router = useRouter();

  const update = useCallback((index: number, patch: Partial<FileState>) => {
    setFiles((list) => list.map((f, i) => (i === index ? { ...f, ...patch } : f)));
  }, []);

  const putPart = useCallback(
    async (url: string, blob: Blob, fileId: string, partNumber: number) => {
      const response = await fetch(url, { method: 'PUT', body: blob });
      if (!response.ok) throw new Error(`Part ${partNumber} was refused (${response.status}).`);
      // The store's ETag has to come back with the part: an S3-compatible
      // provider will not assemble the object without the exact set it issued.
      const etag = response.headers.get('etag') ?? '';
      await notePartUploaded(fileId, partNumber, etag);
    },
    [],
  );

  const upload = useCallback(
    async (file: File, index: number) => {
      const planned = await planFileUpload({
        transferId,
        name: file.name,
        sizeBytes: file.size,
        mimeType: file.type || undefined,
      });
      if (!planned.ok) {
        // The entitlement resolver's own sentence — "up to 100 MB on your
        // plan" — rather than a generic failure.
        update(index, { error: planned.error });
        return;
      }
      update(index, { fileId: planned.fileId, total: planned.parts, done: 0 });

      const queue = planned.partUrls.map((url, i) => ({ url, partNumber: i + 1 }));
      let completed = 0;

      const worker = async () => {
        for (;;) {
          const next = queue.shift();
          if (!next) return;
          const start = (next.partNumber - 1) * planned.partSize;
          await putPart(
            next.url,
            file.slice(start, start + planned.partSize),
            planned.fileId,
            next.partNumber,
          );
          completed += 1;
          update(index, { done: completed });
        }
      };

      await Promise.all(Array.from({ length: CONCURRENCY }, worker));

      /*
       * One resume pass, always.
       *
       * A part can fail without the loop noticing — a dropped socket surfaces
       * as a rejected promise the browser has already retried once at the
       * network layer. Asking the server what is actually missing is cheap and
       * it is the only source of truth; assuming the loop succeeded is how a
       * truncated file gets published.
       */
      const resumed = await resumeFileUpload(planned.fileId);
      if (resumed.ok && resumed.missing.length > 0) {
        for (const part of resumed.missing) {
          const start = (part.partNumber - 1) * resumed.partSize;
          await putPart(part.url, file.slice(start, start + resumed.partSize), planned.fileId, part.partNumber);
          completed += 1;
          update(index, { done: completed });
        }
      }
    },
    [transferId, putPart, update],
  );

  const onPick = useCallback(
    async (picked: FileList | null) => {
      if (!picked || picked.length === 0) return;
      const list = Array.from(picked);
      setFiles(list.map((f) => ({ name: f.name, size: f.size, fileId: null, done: 0, total: 0, error: null })));
      setBusy(true);

      try {
        for (const [i, file] of list.entries()) {
          await upload(file, i).catch((e: unknown) => {
            update(i, { error: e instanceof Error ? e.message : 'The upload failed.' });
          });
        }

        const ready = await readyTransfer(transferId);
        toast(
          ready.ok
            ? { kind: 'ok', message: 'Uploaded. The transfer is ready to share.' }
            : { kind: 'error', message: ready.error },
        );
        router.refresh();
      } finally {
        setBusy(false);
        if (input.current) input.current.value = '';
      }
    },
    [transferId, upload, update, toast, router],
  );

  return (
    <div className="rounded-[4px] border border-[var(--border-hairline)] p-4">
      <label htmlFor={`files-${transferId}`} className="mb-1.5 block text-[12px] uppercase tracking-[0.06em] text-[var(--text-faint)]">
        Add files
      </label>
      <input
        id={`files-${transferId}`}
        ref={input}
        type="file"
        multiple
        disabled={busy}
        onChange={(e) => void onPick(e.target.files)}
        className="block w-full text-[13px] text-[var(--text-secondary)] file:mr-3 file:rounded-[4px] file:border file:border-[var(--border-hairline)] file:bg-[var(--surface-ground)] file:px-3 file:py-1.5 file:text-[13px] file:text-[var(--text-primary)]"
      />

      {files.length > 0 ? (
        <ul className="mt-3 grid gap-2">
          {files.map((f) => (
            <li key={f.name}>
              <div className="flex items-center justify-between gap-3 text-[13px]">
                <span className="min-w-0 truncate text-[var(--text-primary)]">{f.name}</span>
                <span className="shrink-0 tabular-nums text-[var(--text-faint)]">
                  {f.error
                    ? ''
                    : f.total > 0
                      ? `${f.done} of ${f.total} part${f.total === 1 ? '' : 's'}`
                      : 'preparing…'}
                </span>
              </div>
              {f.error ? (
                <p role="alert" className="mt-0.5 text-[12px] text-[var(--color-status-error)]">{f.error}</p>
              ) : (
                <div
                  role="progressbar"
                  aria-label={`Uploading ${f.name}`}
                  aria-valuenow={f.total > 0 ? Math.round((f.done / f.total) * 100) : 0}
                  aria-valuemin={0}
                  aria-valuemax={100}
                  className="mt-1 h-1 w-full overflow-hidden rounded-[4px] bg-[var(--surface-ground)]"
                >
                  <div
                    className="h-full bg-[var(--accent-solid)] transition-[width] duration-[180ms]"
                    style={{ width: f.total > 0 ? `${(f.done / f.total) * 100}%` : '0%' }}
                  />
                </div>
              )}
            </li>
          ))}
        </ul>
      ) : (
        <p className="mt-2 text-[12px] text-[var(--text-faint)]">
          Files upload straight to storage and resume where they stopped, so a dropped
          connection costs you the part it was on, not the whole file.
        </p>
      )}
    </div>
  );
}
