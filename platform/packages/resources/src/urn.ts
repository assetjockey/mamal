/**
 * URNs are how one tool references another tool's object without importing it.
 *
 *   urn:mamal:<tool>:<type>:<id>
 *
 * `core` owns the nouns more than one tool needs — a site, a domain, a contact.
 * That is what lets Audit, Monitor and Track all point at ONE site row.
 */
export const URN_PREFIX = 'urn:mamal';

export type Urn = `urn:mamal:${string}:${string}:${string}`;

export type ParsedUrn = { tool: string; type: string; id: string };

const SEGMENT = /^[a-z0-9_-]+$/i;

export function makeUrn(tool: string, type: string, id: string): Urn {
  for (const [name, value] of Object.entries({ tool, type })) {
    if (!SEGMENT.test(value)) throw new Error(`invalid URN ${name}: ${JSON.stringify(value)}`);
  }
  if (!id) throw new Error('URN id is required');
  return `${URN_PREFIX}:${tool}:${type}:${id}` as Urn;
}

export function parseUrn(urn: string): ParsedUrn {
  const parts = urn.split(':');
  if (parts.length !== 5 || parts[0] !== 'urn' || parts[1] !== 'mamal') {
    throw new Error(`not a mamal URN: ${urn}`);
  }
  const [, , tool, type, id] = parts;
  return { tool: tool!, type: type!, id: id! };
}

export function isUrn(value: unknown): value is Urn {
  if (typeof value !== 'string') return false;
  try {
    parseUrn(value);
    return true;
  } catch {
    return false;
  }
}

/** Canonical URN builders for the shared core nouns. */
export const coreUrn = {
  site: (id: string) => makeUrn('core', 'site', id),
  domain: (id: string) => makeUrn('core', 'domain', id),
  customDomain: (id: string) => makeUrn('core', 'custom_domain', id),
  contact: (id: string) => makeUrn('core', 'contact', id),
  asset: (id: string) => makeUrn('core', 'asset', id),
  project: (id: string) => makeUrn('core', 'project', id),
  link: (id: string) => makeUrn('link', 'link', id),
};
