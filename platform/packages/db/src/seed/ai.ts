/**
 * The AI registry ships as DATA, not config arrays — adding a model on launch
 * day is an admin form, not a deploy. (magicads' media_models/text_models,
 * generalized across modalities.)
 *
 * creditCost is anchored to OUR cost: 1 credit = $0.01 of vendor spend.
 * vendorCostMicros records the real rate so the margin report stays honest.
 */

export const AI_PROVIDERS = [
  { key: 'anthropic', label: 'Anthropic', driver: 'AnthropicDriver', credentialField: 'anthropic_key', baseUrl: 'https://api.anthropic.com/v1', sortOrder: 1 },
  { key: 'openai', label: 'OpenAI', driver: 'OpenAiDriver', credentialField: 'openai_key', baseUrl: 'https://api.openai.com/v1', sortOrder: 2 },
  { key: 'google', label: 'Google', driver: 'GoogleDriver', credentialField: 'gemini_key', baseUrl: 'https://generativelanguage.googleapis.com/v1beta', sortOrder: 3 },
  { key: 'openrouter', label: 'OpenRouter', driver: 'OpenRouterDriver', credentialField: 'openrouter_key', baseUrl: 'https://openrouter.ai/api/v1', sortOrder: 4 },
  { key: 'fal', label: 'fal.ai', driver: 'FalDriver', credentialField: 'fal_key', baseUrl: 'https://queue.fal.run', sortOrder: 5 },
  { key: 'replicate', label: 'Replicate', driver: 'ReplicateDriver', credentialField: 'replicate_key', baseUrl: 'https://api.replicate.com/v1', sortOrder: 6 },
];

export const AI_MODELS = [
  // text
  { providerKey: 'anthropic', modelId: 'claude-opus-5', label: 'Claude Opus 5', modality: 'text', tier: 'premium', creditCost: 3, costUnit: '1k_output_tokens', vendorCostMicros: 15_000, isRecommended: true, capabilities: { contextWindow: 200_000, tools: true, jsonMode: true } },
  { providerKey: 'anthropic', modelId: 'claude-sonnet-5', label: 'Claude Sonnet 5', modality: 'text', tier: 'standard', creditCost: 1, costUnit: '1k_output_tokens', vendorCostMicros: 3_000, isRecommended: true, capabilities: { contextWindow: 200_000, tools: true, jsonMode: true } },
  { providerKey: 'anthropic', modelId: 'claude-haiku-4-5-20251001', label: 'Claude Haiku 4.5', modality: 'text', tier: 'fast', creditCost: 1, costUnit: '1k_output_tokens', vendorCostMicros: 500, capabilities: { contextWindow: 200_000, tools: true, jsonMode: true } },
  { providerKey: 'openai', modelId: 'gpt-5.4', label: 'GPT-5.4', modality: 'text', tier: 'premium', creditCost: 2, costUnit: '1k_output_tokens', vendorCostMicros: 10_000, capabilities: { contextWindow: 128_000, tools: true, jsonMode: true } },
  { providerKey: 'google', modelId: 'gemini-3.1-pro', label: 'Gemini 3.1 Pro', modality: 'text', tier: 'premium', creditCost: 2, costUnit: '1k_output_tokens', vendorCostMicros: 7_000, capabilities: { contextWindow: 1_000_000, tools: true, jsonMode: true } },

  // image
  { providerKey: 'openai', modelId: 'gpt-image-2', label: 'GPT Image 2', modality: 'image', tier: 'premium', creditCost: 8, costUnit: 'image', vendorCostMicros: 80_000, isRecommended: true, capabilities: { maxResolution: '2048x2048', textRendering: 'best' } },
  { providerKey: 'google', modelId: 'imagen-4', label: 'Imagen 4', modality: 'image', tier: 'standard', creditCost: 5, costUnit: 'image', vendorCostMicros: 50_000, capabilities: { maxResolution: '2048x2048', textRendering: 'good' } },
  { providerKey: 'fal', modelId: 'fal-ai/flux-2-pro', label: 'FLUX.2 Pro', modality: 'image', tier: 'standard', creditCost: 5, costUnit: 'image', vendorCostMicros: 50_000, capabilities: { maxResolution: '2048x2048', textRendering: 'good' } },

  // video
  { providerKey: 'google', modelId: 'veo-3.1', label: 'Veo 3.1', modality: 'video', tier: 'premium', creditCost: 40, costUnit: 'video_second', vendorCostMicros: 400_000, capabilities: { audio: true, durations: [4, 6, 8], maxDuration: 8, textRendering: 'native' } },
  { providerKey: 'fal', modelId: 'fal-ai/kling-video/v3/pro', label: 'Kling 3.0 Pro', modality: 'video', tier: 'standard', creditCost: 20, costUnit: 'video_second', vendorCostMicros: 200_000, capabilities: { durations: [5, 10], maxDuration: 10 } },
];

/** Every AI feature is individually toggleable; the master switch is instance-level. */
export const AI_FEATURES = [
  { key: 'audit.ai_summary', tool: 'audit', name: 'Audit summary', modality: 'text', description: 'Executive summary of an audit run.' },
  { key: 'audit.ai_fix_brief', tool: 'audit', name: 'Fix instructions', modality: 'text', description: "Tailored remediation using the page's actual markup." },
  { key: 'audit.ai_alt_text', tool: 'audit', name: 'Alt text', modality: 'vision' },
  { key: 'confirm.ai_copy', tool: 'confirm', name: 'Notification copy', modality: 'text' },
  { key: 'confirm.ai_translate', tool: 'confirm', name: 'Widget translation', modality: 'text' },
  { key: 'link.ai_bio_layout', tool: 'link', name: 'Bio page layout', modality: 'text' },
  { key: 'link.ai_qr_art', tool: 'link', name: 'Artistic QR', modality: 'image' },
  { key: 'market.ai_copy', tool: 'market', name: 'Ad copy', modality: 'text' },
  { key: 'market.ai_image', tool: 'market', name: 'Ad image', modality: 'image' },
  { key: 'market.ai_video', tool: 'market', name: 'Ad video', modality: 'video' },
  { key: 'market.ai_blog', tool: 'market', name: 'Blog writer', modality: 'text' },
  { key: 'market.ai_visibility', tool: 'market', name: 'AI visibility probe', modality: 'text' },
  { key: 'monitor.ai_rca', tool: 'monitor', name: 'Root-cause analysis', modality: 'text' },
  { key: 'track.ai_digest', tool: 'track', name: 'Insight digest', modality: 'text' },
];

export const INSTANCE_MODULES = [
  ...['audit', 'confirm', 'link', 'market', 'monitor', 'track'].map((key) => ({
    key, kind: 'tool', version: '0.1.0', installed: true, enabled: true,
  })),
  ...['teams', 'affiliate', 'newsletters', 'push', 'pwa', 'signatures', 'image-optimizer'].map((key) => ({
    key, kind: 'plugin', version: '0.1.0', installed: false, enabled: false,
  })),
];
