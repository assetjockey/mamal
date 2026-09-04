'use client';

import { useMemo, useState, useTransition } from 'react';
import NextLink from 'next/link';
import { useRouter } from 'next/navigation';
import { encode, toSvg, QrTooLong } from '@mamal/qr';
import { Button, Card, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import { newQr } from '../actions';
import type { QrField } from './page';

/**
 * The QR studio: type → payload → style → export.
 *
 * The preview encodes in the browser, with the real encoder, on every
 * keystroke. That is not a nicety — it is what makes the free tier free. §0.6
 * says a free action must create no dedicated compute, and a server render per
 * preview keystroke is exactly that. The paid distinction is the *export*:
 * print-resolution rasters and PDF/EPS come off the server.
 */

type TypeGroup = {
  category: string;
  items: {
    key: string; label: string; description: string;
    addressing: 'dynamic' | 'static' | 'either';
    fields: QrField[];
  }[];
};

type Code = {
  id: string; type: string; name: string;
  payload: Record<string, unknown>; style: Record<string, unknown>;
  scans: number; url: string | null;
};

const BODY_STYLES = [
  'square', 'dot', 'rounded', 'extra_rounded', 'classy', 'classy_rounded',
  'diamond', 'fluid', 'vertical_bars', 'horizontal_bars', 'star', 'hexagon',
] as const;
const EYE_STYLES = ['square', 'circle', 'rounded', 'extra_rounded', 'leaf', 'diamond'] as const;
const INNER_STYLES = ['square', 'dot', 'circle', 'rounded', 'diamond', 'star'] as const;

/*
 * `min-w-0` is load-bearing on the selects.
 *
 * A <select> keeps an intrinsic minimum width from its longest option, and
 * `w-full` does not override it — so the QR type picker (whose options include
 * full type descriptions) pushed its card 51px past a 360px viewport and gave
 * the whole page a horizontal scrollbar.
 */
const control =
  'w-full min-w-0 rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2.5 py-1.5 ' +
  'text-[13px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]';
const labelStyle = 'mb-1 block text-[11px] uppercase tracking-[0.06em] text-[var(--text-faint)]';

export function Studio({
  types,
  codes,
  limit,
  serverRenderAllowed,
}: {
  types: TypeGroup[];
  codes: Code[];
  limit: { used: number; max: number | null; allowed: boolean; why: string | null } | null;
  serverRenderAllowed: boolean;
}) {
  const [typeKey, setTypeKey] = useState('dynamic_url');
  const [name, setName] = useState('');
  const [payload, setPayload] = useState<Record<string, string>>({});
  const [style, setStyle] = useState({
    body: 'square' as string,
    outerEye: 'square' as string,
    innerEye: 'square' as string,
    foreground: '#061b31',
    background: '#ffffff',
    errorCorrection: 'M' as 'L' | 'M' | 'Q' | 'H',
    margin: 4,
  });
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const def = useMemo(
    () => types.flatMap((g) => g.items).find((t) => t.key === typeKey),
    [types, typeKey],
  );

  /*
   * What the code will encode.
   *
   * For a dynamic type this is the short link, which does not exist until the
   * code is saved — so the preview encodes a placeholder of the same *shape*.
   * A preview built from the payload instead would show a code that scans to
   * somewhere the real one never goes.
   */
  const encoded = useMemo(() => {
    if (!def) return '';
    if (def.addressing === 'dynamic') return 'https://mml.to/preview';
    return buildStatic(typeKey, payload);
  }, [def, typeKey, payload]);

  const svg = useMemo(() => {
    if (!encoded) return null;
    try {
      const matrix = encode(encoded, style.errorCorrection);
      return toSvg(matrix, { ...style, size: 320 });
    } catch (e) {
      // A payload too long for any symbol is a real answer, not a crash — the
      // panel says so and the download stays disabled.
      return e instanceof QrTooLong ? null : null;
    }
  }, [encoded, style]);

  const create = () => {
    start(async () => {
      const result = await newQr({
        type: typeKey,
        name: name.trim() || def?.label || 'QR code',
        payload,
        style,
      });
      toast(result.ok
        ? { kind: 'ok', message: 'QR code saved. Style it any time — the code keeps working.' }
        : { kind: 'error', message: result.error });
      if (result.ok) { setName(''); setPayload({}); router.refresh(); }
    });
  };

  const download = () => {
    if (!svg) return;
    const blob = new Blob([svg], { type: 'image/svg+xml' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${(name.trim() || typeKey).replace(/[^a-z0-9-]+/gi, '-').toLowerCase()}.svg`;
    a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="grid gap-6 [&>*]:min-w-0 xl:grid-cols-[minmax(0,1fr)_340px]">
      <div className="grid min-w-0 gap-6 [&>*]:min-w-0">
        <Card>
          <SectionLabel>Type</SectionLabel>
          <p className="mt-1 max-w-[70ch] text-[13px] text-[var(--text-secondary)]">
            {def?.description ?? 'Pick what the code should do.'}
          </p>
          <div className="mt-3">
            <label htmlFor="qr-type" className="sr-only">QR code type</label>
            <select id="qr-type" value={typeKey} onChange={(e) => { setTypeKey(e.target.value); setPayload({}); }}
                    className={control}>
              {types.map((group) => (
                <optgroup key={group.category} label={humanise(group.category)}>
                  {group.items.map((t) => <option key={t.key} value={t.key}>{t.label}</option>)}
                </optgroup>
              ))}
            </select>
          </div>

          {def ? (
            <p className="mt-3 flex items-center gap-2 text-[12px] text-[var(--text-faint)]">
              <StatusBadge status={def.addressing === 'static' ? 'warn' : 'ok'}>
                {def.addressing === 'static' ? 'Fixed forever' : def.addressing === 'dynamic' ? 'Editable later' : 'Your choice'}
              </StatusBadge>
              {def.addressing === 'static'
                ? 'Encodes its payload directly. Once printed it cannot be changed, and scans are not counted.'
                : 'Resolves through a short link, so you can change the destination and every scan is counted.'}
            </p>
          ) : null}
        </Card>

        <Card>
          <SectionLabel>Content</SectionLabel>
          <div className="mt-3 grid gap-3 md:grid-cols-2">
            <div className="md:col-span-2">
              <label htmlFor="qr-name" className={labelStyle}>Name, for your own reference</label>
              <input id="qr-name" value={name} onChange={(e) => setName(e.target.value)}
                     className={control} placeholder={def?.label ?? 'Poster code'} />
            </div>
            {(def?.fields ?? []).map((f) => (
              <PayloadField
                key={f.name} field={f}
                value={payload[f.name] ?? ''}
                onChange={(v) => setPayload((p) => ({ ...p, [f.name]: v }))}
              />
            ))}
          </div>

          <div className="mt-5 flex flex-wrap gap-2 border-t border-[var(--border-hairline)] pt-4">
            {limit && !limit.allowed ? (
              <NextLink href="/settings/billing">
                <Button variant="ghost">{limit.why ?? 'Upgrade for more codes'}</Button>
              </NextLink>
            ) : (
              <Button onClick={create} disabled={pending}>
                {pending ? 'Saving…' : 'Save code'}
              </Button>
            )}
            <Button variant="ghost" onClick={download} disabled={!svg}>Download SVG</Button>
            {!serverRenderAllowed ? (
              <span className="self-center text-[12px] text-[var(--text-faint)]">
                PNG, PDF and EPS export are on paid plans. SVG scales to any size.
              </span>
            ) : null}
          </div>
        </Card>

        {codes.length > 0 ? (
          <Card>
            <SectionLabel>Saved codes</SectionLabel>
            <ul className="mt-3 grid gap-2">
              {codes.map((c) => (
                <li key={c.id} className="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--border-hairline)] pb-2 last:border-0">
                  <div className="min-w-0">
                    <p className="truncate text-[14px] text-[var(--text-primary)]">{c.name}</p>
                    <p className="truncate text-[12px] text-[var(--text-faint)]">
                      {c.url ?? 'Static code'} · {c.type.replace(/_/g, ' ')}
                    </p>
                  </div>
                  <span className="shrink-0 text-[12px] tabular-nums text-[var(--text-faint)]">
                    {c.scans.toLocaleString()} scan{c.scans === 1 ? '' : 's'}
                  </span>
                </li>
              ))}
            </ul>
          </Card>
        ) : null}
      </div>

      <div className="min-w-0">
        <Card>
          <SectionLabel>Preview</SectionLabel>
          <div
            className="mt-3 grid place-items-center rounded-[4px] border border-[var(--border-hairline)] p-4"
            style={{
              // A checkerboard behind a transparent code, so nobody exports one
              // that vanishes onto a dark substrate without noticing.
              backgroundImage:
                style.background === 'transparent'
                  ? 'repeating-conic-gradient(var(--surface-ground) 0% 25%, var(--surface-raised) 0% 50%)'
                  : undefined,
              backgroundSize: style.background === 'transparent' ? '16px 16px' : undefined,
            }}
          >
            {svg ? (
              /*
                The SVG carries `width="320"` so a download opens at a sensible
                size, which means it also has a 320px intrinsic width — wider
                than a 360px phone once the page padding is taken off. The
                viewBox makes it scale, so the container overrides both
                dimensions rather than the renderer emitting a second size.
              */
              <div
                className="w-full max-w-[280px] [&>svg]:h-auto [&>svg]:w-full"
                aria-label="QR code preview"
                dangerouslySetInnerHTML={{ __html: svg }}
              />
            ) : (
              <p className="py-12 text-center text-[13px] text-[var(--text-faint)]">
                {encoded
                  ? 'That payload is longer than any QR code can hold. Shorten it, or lower the error correction.'
                  : 'Fill in the fields to see the code.'}
              </p>
            )}
          </div>

          <div className="mt-4 grid grid-cols-2 gap-3">
            <Select label="Body" value={style.body} options={BODY_STYLES as readonly string[]}
                    onChange={(v) => setStyle((s) => ({ ...s, body: v }))} />
            <Select label="Eye frame" value={style.outerEye} options={EYE_STYLES as readonly string[]}
                    onChange={(v) => setStyle((s) => ({ ...s, outerEye: v }))} />
            <Select label="Eye centre" value={style.innerEye} options={INNER_STYLES as readonly string[]}
                    onChange={(v) => setStyle((s) => ({ ...s, innerEye: v }))} />
            <Select label="Error correction" value={style.errorCorrection} options={['L', 'M', 'Q', 'H']}
                    onChange={(v) => setStyle((s) => ({ ...s, errorCorrection: v as 'L' | 'M' | 'Q' | 'H' }))} />
            <div>
              <label htmlFor="qr-fg" className={labelStyle}>Foreground</label>
              <input id="qr-fg" type="color" value={style.foreground}
                     onChange={(e) => setStyle((s) => ({ ...s, foreground: e.target.value }))}
                     className="h-9 w-full rounded-[4px] border border-[var(--border-hairline)] bg-transparent" />
            </div>
            <div>
              <label htmlFor="qr-bg" className={labelStyle}>Background</label>
              <input id="qr-bg" type="color"
                     value={style.background === 'transparent' ? '#ffffff' : style.background}
                     onChange={(e) => setStyle((s) => ({ ...s, background: e.target.value }))}
                     className="h-9 w-full rounded-[4px] border border-[var(--border-hairline)] bg-transparent" />
            </div>
          </div>

          <label className="mt-3 flex items-center gap-2 text-[12px] text-[var(--text-secondary)]">
            <input
              type="checkbox"
              checked={style.background === 'transparent'}
              onChange={(e) => setStyle((s) => ({ ...s, background: e.target.checked ? 'transparent' : '#ffffff' }))}
            />
            Transparent background
          </label>

          <p className="mt-3 text-[12px] text-[var(--text-faint)]">
            Higher error correction survives more damage but makes the code denser. Q or H
            if you are adding a logo or printing small.
          </p>
        </Card>
      </div>
    </div>
  );
}

function Select({
  label, value, options, onChange,
}: {
  label: string; value: string; options: readonly string[]; onChange: (v: string) => void;
}) {
  const id = `qr-${label.toLowerCase().replace(/\s+/g, '-')}`;
  return (
    <div>
      <label htmlFor={id} className={labelStyle}>{label}</label>
      <select id={id} value={value} onChange={(e) => onChange(e.target.value)} className={control}>
        {options.map((o) => <option key={o} value={o}>{humanise(o)}</option>)}
      </select>
    </div>
  );
}

function PayloadField({
  field, value, onChange,
}: {
  field: QrField; value: string; onChange: (v: string) => void;
}) {
  const id = `qr-field-${field.name}`;
  const label = (
    <label htmlFor={id} className={labelStyle}>
      {field.label}{field.required ? '' : ' (optional)'}
    </label>
  );

  if (field.kind === 'select') {
    return (
      <div>
        {label}
        <select id={id} value={value} onChange={(e) => onChange(e.target.value)} className={control}>
          {(field.options ?? []).map((o) => <option key={o} value={o}>{humanise(o)}</option>)}
        </select>
      </div>
    );
  }

  if (field.kind === 'boolean') {
    return (
      <label className="flex items-center gap-2 self-end pb-1.5 text-[13px] text-[var(--text-secondary)]">
        <input id={id} type="checkbox" checked={value === 'true'}
               onChange={(e) => onChange(String(e.target.checked))} />
        {field.label}
      </label>
    );
  }

  if (field.kind === 'textarea') {
    return (
      <div className="md:col-span-2">
        {label}
        <textarea id={id} value={value} rows={3} onChange={(e) => onChange(e.target.value)}
                  className={`${control} resize-y`} />
      </div>
    );
  }

  return (
    <div>
      {label}
      <input
        id={id}
        type={field.kind === 'number' ? 'number' : field.kind === 'datetime' ? 'datetime-local' : 'text'}
        inputMode={field.kind === 'url' ? 'url' : undefined}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className={control}
        placeholder={field.kind === 'url' ? 'https://' : undefined}
      />
    </div>
  );
}

/**
 * The client's copy of the static encoders.
 *
 * Deliberately a small subset: the preview only needs the formats a person is
 * likely to be typing while they watch it. `encodePayload` on the server is
 * still the authority, and the saved code is encoded from that — this exists so
 * the preview updates without a round trip per keystroke.
 */
function buildStatic(type: string, p: Record<string, string>): string {
  const esc = (s: string) => String(s ?? '').replace(/([\;,:"])/g, '\\$1');
  switch (type) {
    case 'wifi':
      return p.ssid
        ? `WIFI:T:${p.encryption || 'WPA'};S:${esc(p.ssid)};${p.password ? `P:${esc(p.password)};` : ''};`
        : '';
    case 'text': return p.text ?? '';
    case 'static_url': return p.url ?? '';
    case 'call': return p.phone ? `tel:${p.phone}` : '';
    case 'sms': return p.phone ? `SMSTO:${p.phone}:${p.message ?? ''}` : '';
    case 'email': {
      if (!p.to) return '';
      const q = new URLSearchParams();
      if (p.subject) q.set('subject', p.subject);
      if (p.body) q.set('body', p.body);
      return `mailto:${p.to}${q.toString() ? `?${q}` : ''}`;
    }
    case 'location': return p.latitude && p.longitude ? `geo:${p.latitude},${p.longitude}` : '';
    case 'crypto': return p.address ? `${p.chain || 'bitcoin'}:${p.address}` : '';
    case 'vcard':
      return p.firstName
        ? ['BEGIN:VCARD', 'VERSION:3.0',
           `FN:${esc([p.firstName, p.lastName].filter(Boolean).join(' '))}`,
           p.email ? `EMAIL:${esc(p.email)}` : '',
           p.phone ? `TEL:${esc(p.phone)}` : '',
           'END:VCARD'].filter(Boolean).join('\n')
        : '';
    default:
      // Anything else previews from whichever field looks like the payload.
      return p.url ?? p.value ?? p.text ?? Object.values(p).find(Boolean) ?? '';
  }
}

const humanise = (s: string) =>
  s.replace(/_/g, ' ').replace(/^./, (c) => c.toUpperCase());
