import type { AiDriver, GenerationRequest, GenerationResult } from '../types.ts';

/**
 * Anthropic Messages API.
 *
 * Deliberately a plain fetch client rather than the SDK: drivers are the only
 * place a provider may be reached, and keeping them dependency-free means the
 * eslint boundary has nothing to police inside this directory.
 */
export const anthropicDriver: AiDriver = {
  key: 'anthropic',
  modalities: ['text', 'vision'],

  async generate(request: GenerationRequest, config): Promise<GenerationResult> {
    const started = Date.now();
    const base = config.baseUrl ?? 'https://api.anthropic.com/v1';

    const res = await fetch(`${base}/messages`, {
      method: 'POST',
      headers: {
        'content-type': 'application/json',
        'x-api-key': config.apiKey,
        'anthropic-version': '2023-06-01',
      },
      body: JSON.stringify({
        model: config.modelId,
        max_tokens: Number(request.options?.maxTokens ?? 2048),
        ...(request.system ? { system: request.system } : {}),
        messages: [{ role: 'user', content: request.prompt }],
      }),
      signal: AbortSignal.timeout(120_000),
    });

    const latencyMs = Date.now() - started;

    if (res.status === 429) {
      const retry = Number(res.headers.get('retry-after') ?? 5);
      return {
        ok: false, units: 0, inputTokens: 0, outputTokens: 0, vendorCostMicros: 0,
        latencyMs, rateLimited: true, retryAfterMs: retry * 1000,
        error: 'rate limited',
      };
    }

    if (!res.ok) {
      return {
        ok: false, units: 0, inputTokens: 0, outputTokens: 0, vendorCostMicros: 0,
        latencyMs, error: `HTTP ${res.status}: ${(await res.text()).slice(0, 300)}`,
      };
    }

    const body = (await res.json()) as {
      content?: { type: string; text?: string }[];
      usage?: { input_tokens?: number; output_tokens?: number };
    };

    const text = (body.content ?? [])
      .filter((c) => c.type === 'text')
      .map((c) => c.text ?? '')
      .join('');
    const inputTokens = body.usage?.input_tokens ?? 0;
    const outputTokens = body.usage?.output_tokens ?? 0;

    return {
      ok: true,
      text,
      // Text is priced per 1k output tokens; the ledger rounds up.
      units: Math.max(1, Math.ceil(outputTokens / 1000)),
      inputTokens,
      outputTokens,
      vendorCostMicros: 0, // filled from ai_models.vendor_cost_micros by the caller
      latencyMs,
    };
  },
};
