'use client';

import { useMemo, useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Card, EmptyState, SectionLabel, StatusBadge, useToast } from '@mamal/ui';
import {
  AD_PLATFORMS, FRAMEWORKS, TONES, aspectRatio, presetsFor, type CopyProblem,
} from '@mamal/tool-market/scoring';
import { generateAdCopy, generateCreative, saveBrandKit } from '../actions';

type Brand = {
  id: string; name: string; voice: string | null; audience: string | null;
  palette: string[]; dos: string[]; donts: string[]; isDefault: boolean;
};

type Creative = {
  id: string; type: string; status: string; prompt: string; preset: string | null;
  width: number | null; height: number | null; assetId: string | null;
  creditsSpent: number; error: string | null; createdAt: string;
};

type Variant = { values: Record<string, string[]>; problems: CopyProblem[]; usable: boolean };

export function Studio({ brands, creatives }: { brands: Brand[]; creatives: Creative[] }) {
  const [tab, setTab] = useState<'copy' | 'creative' | 'brand'>('copy');

  return (
    <div className="flex flex-col gap-6">
      <nav className="flex flex-wrap gap-2" aria-label="Studio sections">
        {(['copy', 'creative', 'brand'] as const).map((key) => (
          <button
            key={key}
            type="button"
            onClick={() => setTab(key)}
            aria-current={tab === key ? 'page' : undefined}
            className={
              'rounded-full border px-3 py-1 text-[12px] capitalize ' +
              (tab === key
                ? 'border-[var(--accent)] bg-[var(--accent-wash)] text-[var(--accent)]'
                : 'border-[var(--border)] text-[var(--text-secondary)] hover:bg-[var(--surface-hover)]')
            }
          >
            {key === 'copy' ? 'Copy' : key === 'creative' ? 'Creative' : 'Brand kit'}
          </button>
        ))}
      </nav>

      {tab === 'copy' && <CopyStudio brands={brands} />}
      {tab === 'creative' && <CreativeStudio brands={brands} creatives={creatives} />}
      {tab === 'brand' && <BrandKit brands={brands} />}
    </div>
  );
}

/* ------------------------------------------------------------------ copy */

function CopyStudio({ brands }: { brands: Brand[] }) {
  const [platform, setPlatform] = useState('google_search');
  const [brief, setBrief] = useState('');
  const [framework, setFramework] = useState('aida');
  const [tone, setTone] = useState('professional');
  const [brandId, setBrandId] = useState(brands.find((b) => b.isDefault)?.id ?? '');
  const [variants, setVariants] = useState<Variant[] | null>(null);

  const [pending, start] = useTransition();
  const toast = useToast();

  const spec = AD_PLATFORMS[platform]!;

  const run = () => {
    start(async () => {
      const result = await generateAdCopy({
        platform, brief, framework, tone, brandId: brandId || null,
      });
      if (!result.ok) {
        toast({ kind: 'error', message: result.error });
        return;
      }
      setVariants(result.variants as Variant[]);
      toast({ kind: 'ok', message: `${result.variants.length} variants, ${result.creditsSpent} credits.` });
    });
  };

  return (
    <div className="flex flex-col gap-4 xl:flex-row xl:items-start">
      <div className="flex min-w-0 flex-1 flex-col gap-3">
        <div className="flex flex-wrap gap-3">
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Platform</span>
            <select
              value={platform}
              onChange={(e) => { setPlatform(e.target.value); setVariants(null); }}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              {Object.values(AD_PLATFORMS).map((p) => (
                <option key={p.key} value={p.key}>{p.label}</option>
              ))}
            </select>
          </label>
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Structure</span>
            <select
              value={framework}
              onChange={(e) => setFramework(e.target.value)}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              {Object.entries(FRAMEWORKS).map(([key, f]) => (
                <option key={key} value={key}>{f.label}</option>
              ))}
            </select>
          </label>
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Tone</span>
            <select
              value={tone}
              onChange={(e) => setTone(e.target.value)}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px] capitalize"
            >
              {TONES.map((t) => <option key={t} value={t}>{t}</option>)}
            </select>
          </label>
          {brands.length > 0 && (
            <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
              <span className="text-[var(--text-secondary)]">Brand</span>
              <select
                value={brandId}
                onChange={(e) => setBrandId(e.target.value)}
                className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
              >
                <option value="">None</option>
                {brands.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
              </select>
            </label>
          )}
        </div>

        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Brief</span>
          <textarea
            value={brief}
            onChange={(e) => setBrief(e.target.value)}
            rows={4}
            placeholder="Widget racks for small teams. £19, ships in two days, fits any shelf."
            className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-3 text-[14px]"
          />
        </label>

        <div>
          <Button onClick={run} disabled={pending || brief.trim().length < 10}>
            {pending ? 'Writing…' : 'Write copy'}
          </Button>
        </div>

        {variants && (
          <ul className="flex flex-col gap-3">
            {variants.map((variant, i) => (
              <li key={i}>
                <Card>
                  <div className="flex items-baseline justify-between gap-2">
                    <span className="text-[12px] text-[var(--text-secondary)]">
                      Variant {i + 1}
                    </span>
                    <StatusBadge status={variant.usable ? 'ok' : 'error'}>
                      {variant.usable ? 'fits' : 'needs editing'}
                    </StatusBadge>
                  </div>
                  <dl className="mt-2 flex flex-col gap-2 text-[13px]">
                    {Object.entries(variant.values).map(([field, values]) => (
                      <div key={field}>
                        <dt className="text-[11px] text-[var(--text-secondary)]">
                          {spec.fields.find((f) => f.key === field)?.label ?? field}
                        </dt>
                        <dd>
                          <ul className="flex flex-col gap-[2px]">
                            {values.map((value, j) => (
                              <li key={j} className="flex items-baseline justify-between gap-2">
                                <span className="min-w-0">{value}</span>
                                <span className="shrink-0 text-[11px] tabular-nums text-[var(--text-secondary)]">
                                  {[...value].length}
                                </span>
                              </li>
                            ))}
                          </ul>
                        </dd>
                      </div>
                    ))}
                  </dl>
                  {variant.problems.length > 0 && (
                    <ul className="mt-2 flex flex-col gap-1 text-[11px]">
                      {variant.problems.map((problem, j) => (
                        <li
                          key={j}
                          className={
                            problem.level === 'error'
                              ? 'text-[var(--status-error)]'
                              : 'text-[var(--text-secondary)]'
                          }
                        >
                          {problem.message}
                        </li>
                      ))}
                    </ul>
                  )}
                </Card>
              </li>
            ))}
          </ul>
        )}
      </div>

      {/* ------------------------------------------- what the platform takes */}
      <aside className="flex w-full min-w-0 flex-col gap-2 xl:w-[320px] xl:shrink-0">
        <Card>
          <SectionLabel>{spec.label} fields</SectionLabel>
          <ul className="mt-2 flex flex-col gap-2 text-[12px]">
            {spec.fields.map((field) => (
              <li key={field.key}>
                <span>{field.label}</span>
                <span className="text-[var(--text-secondary)]">
                  {' '}— {field.min === field.max ? field.min : `${field.min}–${field.max}`},{' '}
                  {field.maxLength} characters each
                </span>
                {field.guidance && (
                  <span className="block text-[11px] text-[var(--text-secondary)]">
                    {field.guidance}
                  </span>
                )}
              </li>
            ))}
          </ul>
        </Card>
      </aside>
    </div>
  );
}

/* -------------------------------------------------------------- creative */

function CreativeStudio({ brands, creatives }: { brands: Brand[]; creatives: Creative[] }) {
  const [platform, setPlatform] = useState('meta_feed');
  const [preset, setPreset] = useState('square');
  const [prompt, setPrompt] = useState('');
  const [type, setType] = useState<'image' | 'video'>('image');
  const [brandId, setBrandId] = useState(brands.find((b) => b.isDefault)?.id ?? '');

  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const presets = useMemo(() => presetsFor(platform), [platform]);

  const run = () => {
    start(async () => {
      const result = await generateCreative({
        type, prompt, platform, preset, brandId: brandId || null,
      });
      toast(
        result.ok
          ? {
              kind: 'ok',
              message:
                result.status === 'polling'
                  // Video takes minutes; saying so beats a spinner that looks
                  // stuck.
                  ? 'Started. Video takes a few minutes — it will appear here when it is done.'
                  : 'Done.',
            }
          : { kind: 'error', message: result.error },
      );
      router.refresh();
    });
  };

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap gap-3">
        <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Platform</span>
          <select
            value={platform}
            onChange={(e) => {
              setPlatform(e.target.value);
              setPreset(presetsFor(e.target.value)[0]?.key ?? '');
            }}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          >
            {Object.values(AD_PLATFORMS).map((p) => (
              <option key={p.key} value={p.key}>{p.label}</option>
            ))}
          </select>
        </label>
        <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Size</span>
          <select
            value={preset}
            onChange={(e) => setPreset(e.target.value)}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          >
            {presets.length === 0 && <option value="">No sizes for this platform</option>}
            {presets.map((p) => (
              <option key={p.key} value={p.key}>
                {p.label} · {p.width}×{p.height} ({aspectRatio(p.width, p.height)})
              </option>
            ))}
          </select>
        </label>
        <label className="flex min-w-0 flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Kind</span>
          <select
            value={type}
            onChange={(e) => setType(e.target.value as 'image' | 'video')}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          >
            <option value="image">Image</option>
            <option value="video">Video</option>
          </select>
        </label>
        {brands.length > 0 && (
          <label className="flex min-w-0 flex-1 flex-col gap-1 text-[12px]">
            <span className="text-[var(--text-secondary)]">Brand</span>
            <select
              value={brandId}
              onChange={(e) => setBrandId(e.target.value)}
              className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
            >
              <option value="">None</option>
              {brands.map((b) => <option key={b.id} value={b.id}>{b.name}</option>)}
            </select>
          </label>
        )}
      </div>

      <label className="flex flex-col gap-1 text-[12px]">
        <span className="text-[var(--text-secondary)]">What should it show?</span>
        <textarea
          value={prompt}
          onChange={(e) => setPrompt(e.target.value)}
          rows={3}
          placeholder="A widget rack on a clean desk, morning light"
          className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-3 text-[14px]"
        />
      </label>

      <div>
        <Button onClick={run} disabled={pending || prompt.trim().length < 5 || !preset}>
          {pending ? 'Starting…' : type === 'video' ? 'Generate video' : 'Generate image'}
        </Button>
      </div>

      {creatives.length === 0 ? (
        <EmptyState
          title="Nothing generated yet"
          description="Sizes come from the platform you pick, so nothing is generated at a shape the platform will not take."
        />
      ) : (
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 2xl:grid-cols-3">
          {creatives.map((creative) => (
            <Card key={creative.id}>
              <div className="flex flex-wrap items-baseline justify-between gap-2">
                <span className="text-[12px] capitalize">{creative.type}</span>
                <StatusBadge
                  status={
                    creative.status === 'completed' ? 'ok'
                      : creative.status === 'failed' ? 'error' : 'info'
                  }
                >
                  {creative.status === 'polling' ? 'rendering' : creative.status}
                </StatusBadge>
              </div>
              <p className="mt-1 max-w-full truncate text-[13px]">{creative.prompt}</p>
              <p className="mt-1 text-[11px] text-[var(--text-secondary)] tabular-nums">
                {creative.width && creative.height ? `${creative.width}×${creative.height}` : '—'}
                {creative.creditsSpent > 0 ? ` · ${creative.creditsSpent} credits` : ''}
              </p>
              {creative.error && (
                <p className="mt-1 text-[11px] text-[var(--status-error)]">{creative.error}</p>
              )}
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

/* ------------------------------------------------------------ brand kit */

function BrandKit({ brands }: { brands: Brand[] }) {
  const [name, setName] = useState('');
  const [voice, setVoice] = useState('');
  const [audience, setAudience] = useState('');
  const [donts, setDonts] = useState('');

  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const save = () => {
    start(async () => {
      const result = await saveBrandKit({
        name, voice, audience,
        donts: donts.split('\n').map((d) => d.trim()).filter(Boolean),
        isDefault: brands.length === 0,
      });
      toast(
        result.ok
          ? { kind: 'ok', message: 'Saved. It goes into every generation from now on.' }
          : { kind: 'error', message: result.error },
      );
      if (result.ok) setName('');
      router.refresh();
    });
  };

  return (
    <div className="flex flex-col gap-4">
      <p className="text-[12px] text-[var(--text-secondary)]">
        A paragraph of voice and a few rules turn &ldquo;write an ad&rdquo; into &ldquo;write an
        ad that sounds like us&rdquo;. The brand is copied onto each generation as it was at the
        time, so regenerating something from last year is not quietly a rebrand.
      </p>

      {brands.length > 0 && (
        <ul className="flex flex-col gap-2">
          {brands.map((brand) => (
            <li key={brand.id}>
              <Card>
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <span className="text-[14px]">{brand.name}</span>
                  {brand.isDefault && <StatusBadge status="ok">Default</StatusBadge>}
                </div>
                {brand.voice && <p className="mt-1 text-[12px]">{brand.voice}</p>}
                {brand.donts.length > 0 && (
                  <p className="mt-1 text-[11px] text-[var(--text-secondary)]">
                    Never: {brand.donts.join('; ')}
                  </p>
                )}
              </Card>
            </li>
          ))}
        </ul>
      )}

      <div className="flex flex-col gap-3">
        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Name</span>
          <input
            value={name}
            onChange={(e) => setName(e.target.value)}
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Voice</span>
          <textarea
            value={voice}
            onChange={(e) => setVoice(e.target.value)}
            rows={2}
            placeholder="Plain and direct. Short sentences. No superlatives."
            className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-2 text-[14px]"
          />
        </label>
        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Audience</span>
          <input
            value={audience}
            onChange={(e) => setAudience(e.target.value)}
            placeholder="Operations leads at teams of five to fifty"
            className="h-9 min-w-0 rounded-[4px] border border-[var(--border)] bg-[var(--surface)] px-2 text-[14px]"
          />
        </label>
        <label className="flex flex-col gap-1 text-[12px]">
          <span className="text-[var(--text-secondary)]">Never do this — one per line</span>
          <textarea
            value={donts}
            onChange={(e) => setDonts(e.target.value)}
            rows={3}
            placeholder={'No exclamation marks\nNever say "revolutionary"'}
            className="min-w-0 resize-y rounded-[4px] border border-[var(--border)] bg-[var(--surface)] p-2 text-[14px]"
          />
        </label>
        <div>
          <Button onClick={save} disabled={pending || !name.trim()}>Save brand</Button>
        </div>
      </div>
    </div>
  );
}
