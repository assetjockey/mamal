/**
 * The browser-safe half of Market.
 *
 * `src/index.ts` is the tool's surface for the server: it re-exports the
 * runners, which import `@mamal/db`, which imports `postgres`, which imports
 * `fs`. Importing *anything* from that barrel inside a client component pulls
 * the whole database driver into the browser bundle and the build fails with
 * "Can't resolve 'fs'" — pointing at Postgres rather than at the import that
 * caused it.
 *
 * So the editor imports its scoring from here instead. Everything reachable
 * from this file is pure arithmetic over strings, which is also what makes the
 * live score and the stored score the same number: the browser and the server
 * run the same function.
 */
export {
  parseBody,
  scoreContent,
  readingEase,
  syllablesIn,
  wordsOf,
  DENSITY_BAND,
  type Check,
  type ContentScore,
  type Doc,
  type Brief,
  type Parsed,
} from './content-score.ts';
export { appearsIn, appearsInflected, mentionsOf, spansOf } from './text.ts';
export type { DocRow } from './content.ts';

/*
 * 4D's pure half, for the composer: per-network limits, character counting and
 * the queue grid all run in the browser as the writer types.
 */
export {
  NETWORKS,
  networkFor,
  countCharacters,
  hashtagsIn,
  hashtagCount,
  validatePost,
  canSchedule,
  splitThread,
  type Network,
  type Problem,
  type PostDraft,
} from './networks.ts';
export {
  nextSlot,
  nextSlots,
  defaultSlots,
  cleanSlots,
  isEmpty,
  DAYS,
  HORIZON_DAYS,
  type Slots,
  type Day,
} from './queue.ts';

/*
 * 4E's pure half, for the ad studios: the platform catalogue, canvas presets
 * and the spend arithmetic all run in the browser.
 */
export {
  AD_PLATFORMS,
  PRESETS,
  FRAMEWORKS,
  TONES,
  OBJECTIVES,
  platformFor,
  presetsFor,
  aspectRatio,
  validateCopy,
  copyIsUsable,
  type AdPlatform,
  type FieldSpec,
  type Preset,
  type CopyProblem,
} from './ad-platforms.ts';
export {
  totalsOf,
  comparisonWindows,
  type MetricRow,
  type Totals,
  type Finding,
} from './ad-performance.ts';

/*
 * 4F's pure half: the grid geometry and the NAP comparison, both of which the
 * local screen runs in the browser.
 */
export {
  buildGrid,
  distanceKm,
  summariseGrid,
  gridCost,
  GRID_SIZES,
  type Point,
  type GridPoint,
  type GridSize,
  type GridSummary,
  type GridReading,
} from './geo-grid.ts';
export {
  normaliseName,
  normaliseAddress,
  normalisePhone,
  compareNap,
  actionable,
  type Nap,
  type Difference,
} from './nap.ts';
export { triage, profileGaps, type LocalProfile, type ReviewRow } from './local-rules.ts';
export type { GridRun } from './geo-grid.ts';
