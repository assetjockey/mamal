import { Card } from '@mamal/ui';

export type Point = { at: string; score: number };

/**
 * The score trend, as inline SVG.
 *
 * No charting library: one series of at most 90 points does not justify the
 * bundle, and the reference design wants a quiet line rather than a dashboard
 * widget. Drawn with stroke-dashoffset so it animates in without JavaScript.
 */
export function ScoreTrend({ points }: { points: Point[] }) {
  if (points.length < 2) return null;

  const width = 640;
  const height = 160;
  const padding = { top: 12, right: 8, bottom: 22, left: 28 };
  const plotW = width - padding.left - padding.right;
  const plotH = height - padding.top - padding.bottom;

  // Always show the full 0–100 range: a zoomed y-axis makes a two-point
  // wobble look like a collapse.
  const x = (i: number) => padding.left + (i / (points.length - 1)) * plotW;
  const y = (score: number) => padding.top + (1 - score / 100) * plotH;

  const line = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${x(i)} ${y(p.score)}`).join(' ');
  const area = `${line} L ${x(points.length - 1)} ${padding.top + plotH} L ${x(0)} ${padding.top + plotH} Z`;

  const first = points[0]!;
  const last = points[points.length - 1]!;
  const delta = last.score - first.score;

  return (
    <Card>
      <div className="mb-3 flex items-baseline justify-between">
        <span className="text-[32px] leading-[1.1] tracking-[-0.64px] tabular-nums">
          {last.score}
        </span>
        <span
          className="text-[13px] tabular-nums"
          style={{
            color:
              delta > 0 ? 'var(--color-status-ok)'
              : delta < 0 ? 'var(--color-status-error)'
              : 'var(--text-faint)',
          }}
        >
          {delta > 0 ? '+' : ''}
          {delta} over {points.length} runs
        </span>
      </div>

      <svg
        viewBox={`0 0 ${width} ${height}`}
        className="w-full"
        role="img"
        aria-label={`Score trend: ${first.score} to ${last.score} across ${points.length} audits`}
      >
        {[0, 50, 100].map((tick) => (
          <g key={tick}>
            <line
              x1={padding.left} x2={width - padding.right}
              y1={y(tick)} y2={y(tick)}
              stroke="var(--border-hairline)" strokeWidth="1"
            />
            <text
              x={padding.left - 6} y={y(tick) + 4}
              textAnchor="end" fontSize="11" fill="var(--text-faint)"
            >
              {tick}
            </text>
          </g>
        ))}

        <path d={area} fill="var(--accent-wash)" opacity="0.6" />
        <path
          d={line}
          fill="none"
          stroke="var(--accent)"
          strokeWidth="1.5"
          strokeLinejoin="round"
          strokeLinecap="round"
        />
        {points.map((p, i) => (
          <circle key={p.at} cx={x(i)} cy={y(p.score)} r="2.5" fill="var(--accent)">
            <title>{`${new Date(p.at).toLocaleDateString()}: ${p.score}`}</title>
          </circle>
        ))}
      </svg>

      <div className="mt-1 flex justify-between text-[11px] text-[var(--text-faint)]">
        <span>{new Date(first.at).toLocaleDateString()}</span>
        <span>{new Date(last.at).toLocaleDateString()}</span>
      </div>
    </Card>
  );
}
