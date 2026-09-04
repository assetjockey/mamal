import { z } from 'zod';

export const MODALITIES = ['text', 'image', 'video', 'audio', 'embedding', 'vision'] as const;
export type Modality = (typeof MODALITIES)[number];

export type GenerationRequest = {
  featureKey: string;
  prompt: string;
  system?: string;
  modality: Modality;
  /** Provider-specific knobs — validated by each driver, not here. */
  options?: Record<string, unknown>;
  /** Video is priced per second and images per image; text per 1k tokens. */
  expectedUnits?: number;
};

export type GenerationResult = {
  ok: boolean;
  text?: string;
  /** Async providers hand back a task id instead of a payload. */
  externalTaskId?: string;
  url?: string;
  bytes?: Uint8Array;
  /** What we actually consumed — drives the credit true-up. */
  units: number;
  inputTokens: number;
  outputTokens: number;
  vendorCostMicros: number;
  latencyMs: number;
  rateLimited?: boolean;
  retryAfterMs?: number;
  error?: string;
};

/**
 * Every provider implements this. Drivers may only be constructed inside this
 * package — the eslint boundary bans provider SDK imports everywhere else, so
 * there is no path around `execute()`'s entitlement re-check.
 */
export interface AiDriver {
  readonly key: string;
  readonly modalities: readonly Modality[];
  generate(
    request: GenerationRequest,
    config: { apiKey: string; modelId: string; baseUrl?: string },
  ): Promise<GenerationResult>;
  /** Async providers only. */
  poll?(taskId: string, config: { apiKey: string; baseUrl?: string }): Promise<GenerationResult>;
}

export const AI_DENY_REASONS = [
  'ai_disabled_instance',
  'ai_disabled_tenant',
  'ai_disabled_feature',
  'ai_excluded_lifetime',
  'not_in_plan',
  'insufficient_credits',
  'no_model_available',
  'no_credential',
  'tool_unavailable',
  'unknown_feature',
] as const;
export type AiDenyReason = (typeof AI_DENY_REASONS)[number];

export class AiUnavailable extends Error {
  constructor(
    readonly reason: AiDenyReason,
    message: string,
  ) {
    super(message);
    this.name = 'AiUnavailable';
  }
}

export const generationRequestSchema = z.object({
  featureKey: z.string(),
  prompt: z.string().min(1),
  system: z.string().optional(),
  modality: z.enum(MODALITIES),
  options: z.record(z.string(), z.unknown()).optional(),
  expectedUnits: z.number().positive().optional(),
});
