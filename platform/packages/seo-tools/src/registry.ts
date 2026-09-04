import { textTools } from './tools/text.ts';
import { developerTools } from './tools/developer.ts';
import { researchTools } from './tools/research.ts';
import type { Tool, ToolCategory } from './types.ts';

export const ALL_TOOLS: Tool[] = [...researchTools, ...developerTools, ...textTools];

const BY_SLUG = new Map(ALL_TOOLS.map((t) => [t.slug, t]));
if (BY_SLUG.size !== ALL_TOOLS.length) {
  throw new Error('duplicate tool slugs');
}

export function toolBySlug(slug: string): Tool | undefined {
  return BY_SLUG.get(slug);
}

export function toolsByCategory(category: ToolCategory): Tool[] {
  return ALL_TOOLS.filter((t) => t.category === category);
}

/** Tools that make an outbound request are rate-limited; the pure ones are free. */
export function isRateLimited(slug: string): boolean {
  return Boolean(BY_SLUG.get(slug)?.fetches);
}
