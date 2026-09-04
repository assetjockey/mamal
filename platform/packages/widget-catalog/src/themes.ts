/**
 * The 30 named themes, from `66socialproof`'s set.
 *
 * A theme is six values, not a stylesheet. The runtime turns one into CSS
 * custom properties inside the widget's shadow root, so a theme change is six
 * string assignments and no re-render — and so the editor preview and the live
 * widget cannot diverge, because both read the same six values.
 *
 * They are city names in the source products and the names are kept: they are
 * what customers have learned, and renaming them would be churn with no gain.
 */

export type Theme = {
  key: string;
  label: string;
  /** Widget background. */
  bg: string;
  /** Primary text. */
  fg: string;
  /** Secondary text — timestamps, captions. */
  muted: string;
  /** Buttons, links, active states. */
  accent: string;
  /** Text on the accent. */
  onAccent: string;
  /** Hairline border; '' for none. */
  border: string;
};

const t = (
  key: string, label: string,
  bg: string, fg: string, muted: string, accent: string, onAccent: string, border: string,
): Theme => ({ key, label, bg, fg, muted, accent, onAccent, border });

export const THEMES: Theme[] = [
  t('stockholm', 'Stockholm', '#ffffff', '#111827', '#6b7280', '#2563eb', '#ffffff', '#e5e7eb'),
  t('oslo', 'Oslo', '#f8fafc', '#0f172a', '#64748b', '#0ea5e9', '#ffffff', '#e2e8f0'),
  t('copenhagen', 'Copenhagen', '#ffffff', '#1f2937', '#6b7280', '#059669', '#ffffff', '#e5e7eb'),
  t('helsinki', 'Helsinki', '#f1f5f9', '#0f172a', '#475569', '#334155', '#ffffff', '#cbd5e1'),
  t('reykjavik', 'Reykjavik', '#ffffff', '#0c4a6e', '#0369a1', '#0284c7', '#ffffff', '#bae6fd'),
  t('berlin', 'Berlin', '#18181b', '#fafafa', '#a1a1aa', '#facc15', '#18181b', '#3f3f46'),
  t('hamburg', 'Hamburg', '#1c1917', '#fafaf9', '#a8a29e', '#f97316', '#1c1917', '#44403c'),
  t('munich', 'Munich', '#0f172a', '#f8fafc', '#94a3b8', '#38bdf8', '#0f172a', '#1e293b'),
  t('vienna', 'Vienna', '#fefce8', '#422006', '#854d0e', '#a16207', '#ffffff', '#fde68a'),
  t('zurich', 'Zurich', '#ffffff', '#0a0a0a', '#525252', '#dc2626', '#ffffff', '#e5e5e5'),
  t('paris', 'Paris', '#fdf2f8', '#500724', '#9d174d', '#db2777', '#ffffff', '#fbcfe8'),
  t('lyon', 'Lyon', '#faf5ff', '#3b0764', '#7e22ce', '#9333ea', '#ffffff', '#e9d5ff'),
  t('madrid', 'Madrid', '#fff7ed', '#431407', '#9a3412', '#ea580c', '#ffffff', '#fed7aa'),
  t('barcelona', 'Barcelona', '#ecfeff', '#083344', '#0e7490', '#06b6d4', '#ffffff', '#a5f3fc'),
  t('lisbon', 'Lisbon', '#f0fdf4', '#052e16', '#15803d', '#16a34a', '#ffffff', '#bbf7d0'),
  t('porto', 'Porto', '#eff6ff', '#172554', '#1d4ed8', '#2563eb', '#ffffff', '#bfdbfe'),
  t('rome', 'Rome', '#fef2f2', '#450a0a', '#b91c1c', '#dc2626', '#ffffff', '#fecaca'),
  t('milan', 'Milan', '#fafafa', '#171717', '#737373', '#171717', '#ffffff', '#d4d4d4'),
  t('athens', 'Athens', '#ffffff', '#1e3a8a', '#3b82f6', '#1d4ed8', '#ffffff', '#dbeafe'),
  t('london', 'London', '#ffffff', '#1f2937', '#4b5563', '#7c3aed', '#ffffff', '#e5e7eb'),
  t('dublin', 'Dublin', '#f7fee7', '#1a2e05', '#4d7c0f', '#65a30d', '#ffffff', '#d9f99d'),
  t('edinburgh', 'Edinburgh', '#1e1b4b', '#eef2ff', '#a5b4fc', '#818cf8', '#1e1b4b', '#312e81'),
  t('amsterdam', 'Amsterdam', '#ffffff', '#7c2d12', '#c2410c', '#f97316', '#ffffff', '#fed7aa'),
  t('brussels', 'Brussels', '#fffbeb', '#451a03', '#b45309', '#d97706', '#ffffff', '#fde68a'),
  t('prague', 'Prague', '#faf5ff', '#4c1d95', '#7c3aed', '#8b5cf6', '#ffffff', '#ddd6fe'),
  t('warsaw', 'Warsaw', '#ffffff', '#450a0a', '#991b1b', '#b91c1c', '#ffffff', '#fecaca'),
  t('budapest', 'Budapest', '#f5f3ff', '#2e1065', '#6d28d9', '#7c3aed', '#ffffff', '#ddd6fe'),
  t('istanbul', 'Istanbul', '#042f2e', '#f0fdfa', '#5eead4', '#14b8a6', '#042f2e', '#134e4a'),
  t('new_york', 'New York', '#000000', '#ffffff', '#a3a3a3', '#ffffff', '#000000', '#262626'),
  t('rio_de_janeiro', 'Rio de Janeiro', '#fefce8', '#14532d', '#15803d', '#eab308', '#14532d', '#fef08a'),
];

const BY_KEY = new Map(THEMES.map((x) => [x.key, x]));

/**
 * A theme as CSS custom properties.
 *
 * Returned as a plain object rather than a string so the runtime can assign it
 * to a shadow root's style and the editor can hand it to React — one source,
 * two consumers, no template literal to get out of step.
 */
export function themeVars(key: string, accentOverride?: string): Record<string, string> {
  const theme = BY_KEY.get(key) ?? THEMES[0]!;
  return {
    '--w-bg': theme.bg,
    '--w-fg': theme.fg,
    '--w-muted': theme.muted,
    // A per-widget accent overrides the theme's, so brand colour does not force
    // a customer to abandon a theme they otherwise want.
    '--w-accent': accentOverride ?? theme.accent,
    '--w-on-accent': theme.onAccent,
    '--w-border': theme.border,
  };
}
