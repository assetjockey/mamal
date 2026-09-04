/**
 * The bio page, as a visitor sees it.
 *
 * **One renderer per family, not per type.** Twelve cases cover all 84 blocks,
 * which is the entire argument for the catalogue being data: a new block in the
 * `link` family needs no code here at all, and one in a family we have not
 * drawn yet falls through to a labelled row rather than breaking the page.
 *
 * A server component with no client JavaScript. This is somebody's public
 * profile, opened from a phone on a bad connection — the fastest thing we can
 * send is HTML.
 */

export type PublicBlock = {
  id: string;
  type: string;
  family: string;
  label: string;
  settings: Record<string, unknown>;
};

type Theme = Record<string, string>;

export function BioPage({
  title,
  theme,
  blocks,
}: {
  title: string | null;
  theme: Theme;
  blocks: PublicBlock[];
}) {
  /*
   * The theme is customer data, so it is mapped onto a fixed set of custom
   * properties rather than spread into a style attribute. A theme object with
   * an unexpected key cannot inject a declaration this way.
   */
  const vars = {
    '--bio-bg': safeColour(theme.background) ?? '#f8fafd',
    '--bio-fg': safeColour(theme.foreground) ?? '#061b31',
    '--bio-card': safeColour(theme.card) ?? '#ffffff',
    '--bio-accent': safeColour(theme.accent) ?? '#533afd',
    '--bio-radius': /^\d{1,2}px$/.test(theme.radius ?? '') ? theme.radius! : '4px',
  } as React.CSSProperties;

  return (
    <main
      style={vars}
      className="mx-auto min-h-dvh w-full max-w-[600px] bg-[var(--bio-bg)] px-4 py-10 text-[var(--bio-fg)]"
    >
      {title ? (
        <h1 className="mb-6 text-center text-[26px] font-light tracking-[-0.01em]">{title}</h1>
      ) : null}

      <div className="grid gap-3">
        {blocks.map((b) => <Block key={b.id} block={b} />)}
      </div>

      {blocks.length === 0 ? (
        <p className="py-16 text-center text-[14px] opacity-60">Nothing here yet.</p>
      ) : null}
    </main>
  );
}

function Block({ block }: { block: PublicBlock }) {
  const s = block.settings as Record<string, string | undefined>;
  const card =
    'block rounded-[var(--bio-radius)] border border-black/10 bg-[var(--bio-card)] px-4 py-3';

  switch (block.family) {
    case 'link':
    case 'file':
      return s.url ? (
        <a
          href={s.url}
          rel="noopener noreferrer nofollow"
          className={`${card} text-center text-[15px] hover:border-[var(--bio-accent)]`}
        >
          {s.label || block.label}
          {s.description ? (
            <span className="mt-0.5 block text-[13px] opacity-70">{s.description}</span>
          ) : null}
        </a>
      ) : null;

    case 'commerce':
      return s.url ? (
        <a
          href={s.url}
          rel="noopener noreferrer nofollow"
          className="block rounded-[var(--bio-radius)] bg-[var(--bio-accent)] px-4 py-3 text-center text-[15px] text-white"
        >
          {s.buttonLabel || s.title || block.label}
          {s.amount ? <span className="ml-1 tabular-nums">{s.currency ?? ''}{s.amount}</span> : null}
        </a>
      ) : null;

    case 'text':
      return s.text ? (
        <div className="px-1 text-[15px] leading-relaxed">
          {s.text.split('\n').map((line, i) => <p key={i} className="mb-2 last:mb-0">{line}</p>)}
        </div>
      ) : null;

    case 'media':
      return s.url ? (
        /*
          A plain <img>, not next/image: the URL is whatever the customer
          pasted, and next/image needs a `remotePatterns` entry per host —
          which would mean a deploy every time somebody links a new image.
        */
        <img
          src={s.url}
          alt={s.alt ?? ''}
          loading="lazy"
          className="w-full rounded-[var(--bio-radius)]"
        />
      ) : null;

    case 'embed':
      return s.url ? (
        <div className="overflow-hidden rounded-[var(--bio-radius)]" style={{ aspectRatio: ratio(s.aspectRatio) }}>
          <iframe
            src={s.url}
            title={s.caption || block.label}
            loading="lazy"
            // Third-party content in somebody else's page: no same-origin, no
            // top-level navigation, no downloads.
            sandbox="allow-scripts allow-popups allow-presentation"
            referrerPolicy="no-referrer"
            allow="accelerometer; clipboard-write; encrypted-media; picture-in-picture"
            className="h-full w-full border-0"
          />
        </div>
      ) : null;

    case 'card':
      return (
        <div className={card}>
          {s.title || s.name ? <p className="text-[15px]">{s.title || s.name}</p> : null}
          {s.description || s.quote ? (
            <p className="mt-1 text-[14px] opacity-80">{s.description || s.quote}</p>
          ) : null}
          {s.code ? (
            <p className="mt-2 font-mono text-[15px] tracking-wide">{s.code}</p>
          ) : null}
        </div>
      );

    case 'form':
      return (
        <form
          method="post"
          action={`/api/link/collect/${block.id}`}
          className={`${card} text-left`}
        >
          <p className="text-[14px]">{s.title || block.label}</p>
          <label htmlFor={`v-${block.id}`} className="sr-only">
            {s.title || 'Your email address'}
          </label>
          <input
            id={`v-${block.id}`}
            name="value"
            type={block.type.startsWith('phone') ? 'tel' : 'email'}
            required
            className="mt-2 w-full rounded-[var(--bio-radius)] border border-black/15 bg-transparent px-3 py-2 text-[15px]"
          />
          {s.requireConsent !== undefined && String(s.requireConsent) !== 'false' ? (
            <label className="mt-2 flex items-start gap-2 text-[12px] opacity-80">
              <input type="checkbox" name="consent" required className="mt-0.5" />
              {s.consentText || 'I agree to be contacted.'}
            </label>
          ) : null}
          <button
            type="submit"
            className="mt-3 w-full rounded-[var(--bio-radius)] bg-[var(--bio-accent)] px-4 py-2 text-[14px] text-white"
          >
            {s.buttonLabel || 'Subscribe'}
          </button>
        </form>
      );

    case 'layout':
      return block.type === 'divider'
        ? <hr className="my-2 border-0 border-t border-black/10" />
        : block.type === 'avatar' && s.imageUrl
          ? (
            <div className="text-center">
              {/* Plain <img> for the same reason as the media family above. */}
              <img src={s.imageUrl} alt={s.name ?? ''} className="mx-auto h-20 w-20 rounded-full object-cover" />
              {s.name ? <p className="mt-2 text-[16px]">{s.name}</p> : null}
              {s.tagline ? <p className="text-[13px] opacity-70">{s.tagline}</p> : null}
            </div>
          )
          : null;

    case 'widget':
      return (
        <div className={`${card} text-center`}>
          <p className="text-[13px] opacity-70">{s.label || block.label}</p>
          {s.value ? <p className="mt-0.5 text-[22px] tabular-nums">{s.value}{s.suffix ?? ''}</p> : null}
        </div>
      );

    case 'list':
      return null; // Rendered by the specific list types once they carry items.

    case 'custom':
      // Deliberately not rendered as HTML. Custom markup on a public page is an
      // XSS vector and a paid feature; it is served from a sandboxed frame by
      // the same route the embed family uses, once that gate is in place.
      return null;

    default:
      return null;
  }
}

const ratio = (value: string | undefined) =>
  value === '4:3' ? '4 / 3' : value === '1:1' ? '1 / 1' : value === '9:16' ? '9 / 16' : '16 / 9';

/** Only a hex colour, so a theme value can never become a CSS declaration. */
function safeColour(value: string | undefined): string | null {
  return value && /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(value) ? value : null;
}
