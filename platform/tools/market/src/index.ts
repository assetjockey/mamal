export { marketManifest } from './manifest.ts';
export {
  strikingDistance,
  lowCtr,
  expectedCtr,
  contentDecay,
  cannibalisation,
  risingQueries,
  type Opportunity,
  type PerformanceRow,
} from './opportunities.ts';
export {
  saveConnection,
  markConnectionFailed,
  upsertKeywords,
  createRankConfig,
  trackKeywords,
  recordRankSnapshots,
  isNotableMove,
  recomputeOpportunities,
  setOpportunityStatus,
  normaliseDomain,
  MarketNotAllowed,
  type KeywordInput,
} from './service.ts';
export {
  syncSearchConsole,
  claimDueConnections,
  type SyncResult,
  type SyncDeps,
} from './sync.ts';
