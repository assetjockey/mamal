'use client';

import { useState, useTransition } from 'react';
import { Button, Card, StatusBadge, Table, Td, Th, Tr } from '@mamal/ui';
import type { ToolField, ToolOutput } from '@mamal/seo-tools';
import { runTool } from '../actions';

export function ToolRunner({
  slug,
  fields,
  fetches,
}: {
  slug: string;
  fields: ToolField[];
  fetches: boolean;
}) {
  const [pending, start] = useTransition();
  const [output, setOutput] = useState<ToolOutput | null>(null);
  const [copied, setCopied] = useState(false);

  return (
    <>
      <form
        action={(data) =>
          start(async () => {
            setCopied(false);
            const input = Object.fromEntries(
              fields.map((f) => [f.name, String(data.get(f.name) ?? '')]),
            );
            setOutput(await runTool(slug, input));
          })
        }
        className="space-y-5"
      >
        {fields.map((field) => (
          <Field key={field.name} field={field} />
        ))}

        <div className="flex items-center gap-3">
          <Button type="submit" disabled={pending}>
            {pending ? 'Working…' : 'Run'}
          </Button>
          {fetches ? (
            <span className="text-[12px] text-[var(--text-faint)]">
              Fetches the live page — limited to 30 an hour.
            </span>
          ) : (
            <span className="text-[12px] text-[var(--text-faint)]">
              Runs locally. Nothing is stored.
            </span>
          )}
        </div>
      </form>

      {output ? (
        <div className="mt-8">
          <div className="mb-2 flex items-center justify-between">
            <span className="text-[11px] uppercase tracking-[0.5px] text-[var(--text-faint)]">
              Result
            </span>
            {output.kind === 'text' ? (
              <button
                onClick={() => {
                  void navigator.clipboard.writeText(output.value);
                  setCopied(true);
                }}
                className="text-[12px] text-[var(--accent)] transition-colors hover:text-[var(--accent-hover)]"
              >
                {copied ? 'Copied' : 'Copy'}
              </button>
            ) : null}
          </div>
          <Output output={output} />
        </div>
      ) : null}
    </>
  );
}

function Output({ output }: { output: ToolOutput }) {
  if (output.kind === 'error') {
    return (
      <Card>
        <div className="flex items-start gap-3">
          <StatusBadge status="error">Error</StatusBadge>
          <span className="text-[14px] text-[var(--text-primary)]">{output.message}</span>
        </div>
      </Card>
    );
  }

  if (output.kind === 'text') {
    return (
      <Card>
        <pre className="overflow-x-auto whitespace-pre-wrap break-words font-mono text-[13px] leading-[1.6] text-[var(--text-primary)]">
          {output.value}
        </pre>
      </Card>
    );
  }

  if (output.kind === 'pairs') {
    return (
      <Card padded={false}>
        <dl>
          {output.pairs.map((pair) => (
            <div
              key={pair.label}
              className="flex flex-col gap-1 border-b border-[var(--border-hairline)] px-4 py-2.5 last:border-b-0 sm:flex-row sm:gap-4"
            >
              <dt className="w-56 shrink-0 text-[13px] text-[var(--text-faint)]">{pair.label}</dt>
              <dd className="min-w-0 flex-1 whitespace-pre-wrap break-words font-mono text-[13px] text-[var(--text-primary)]">
                {pair.value}
              </dd>
            </div>
          ))}
        </dl>
      </Card>
    );
  }

  return (
    <Table>
      <thead>
        <tr>
          {output.columns.map((c) => (
            <Th key={c}>{c}</Th>
          ))}
        </tr>
      </thead>
      <tbody>
        {output.rows.map((row, i) => (
          <Tr key={i}>
            {row.map((cell, j) => (
              <Td key={j} muted={j > 0}>
                <span className="block max-w-md truncate font-mono text-[12px]" title={String(cell)}>
                  {String(cell)}
                </span>
              </Td>
            ))}
          </Tr>
        ))}
      </tbody>
    </Table>
  );
}

function Field({ field }: { field: ToolField }) {
  const shared =
    'w-full rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-raised)] px-3 text-[14px] outline-none transition-colors duration-[120ms] focus:border-[var(--accent)]';

  return (
    <div>
      <label htmlFor={field.name} className="mb-1.5 block text-[13px] text-[var(--text-secondary)]">
        {field.label}
      </label>

      {field.type === 'textarea' ? (
        <textarea
          id={field.name} name={field.name} rows={6}
          required={field.required} placeholder={field.placeholder}
          className={`${shared} py-2 font-mono`}
        />
      ) : field.type === 'select' ? (
        <select id={field.name} name={field.name} className={`${shared} h-10`}>
          {(field.options ?? []).map((o) => (
            <option key={o.value} value={o.value}>{o.label}</option>
          ))}
        </select>
      ) : (
        <input
          id={field.name} name={field.name}
          type={field.type === 'url' ? 'text' : field.type}
          required={field.required} placeholder={field.placeholder}
          className={`${shared} h-10`}
        />
      )}

      {field.hint ? <p className="mt-1 text-[12px] text-[var(--text-faint)]">{field.hint}</p> : null}
    </div>
  );
}
