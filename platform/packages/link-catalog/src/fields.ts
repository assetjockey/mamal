import { z } from 'zod';

/**
 * Form fields, derived from a schema.
 *
 * The same mechanism the widget catalogue uses, and for the same reason: a new
 * QR type or block type is a row, and its editor appears with it. Nothing here
 * walks zod internals — `z.toJSONSchema` is a public, versioned surface, and it
 * is the same call the MCP server makes to describe its tools.
 *
 * Shared between blocks and QR payloads because the question is identical:
 * given a schema, what inputs should a person see, and which of them must be
 * filled in.
 */

export type FieldKind =
  | 'text' | 'textarea' | 'number' | 'boolean' | 'select' | 'url' | 'colour'
  | 'datetime' | 'string-list' | 'unsupported';

export type Field = {
  name: string;
  label: string;
  kind: FieldKind;
  required: boolean;
  options?: string[];
  min?: number;
  max?: number;
  default?: unknown;
};

type JsonSchema = {
  type?: string;
  properties?: Record<string, JsonSchema>;
  required?: string[];
  enum?: unknown[];
  const?: unknown;
  format?: string;
  pattern?: string;
  minimum?: number;
  maximum?: number;
  minLength?: number;
  maxLength?: number;
  default?: unknown;
  items?: JsonSchema;
  anyOf?: JsonSchema[];
};

const TEXTAREA = new Set(['body', 'html', 'code', 'text', 'message', 'notes', 'description']);

function humanise(name: string): string {
  const spaced = name.replace(/_/g, ' ').replace(/([a-z0-9])([A-Z])/g, '$1 $2');
  return spaced.charAt(0).toUpperCase() + spaced.slice(1);
}

function kindOf(name: string, schema: JsonSchema): FieldKind {
  // `anyOf` is how zod emits a union — `url().or(literal(''))` for an optional
  // URL is the common one. Take the first branch we can render.
  const s = schema.anyOf?.find((b) => b.type || b.format || b.enum) ?? schema;

  if (s.enum && s.enum.length > 0) return 'select';
  if (s.type === 'boolean' || s.const !== undefined) return 'boolean';
  if (s.type === 'number' || s.type === 'integer') return 'number';
  if (s.type === 'array') return s.items?.type === 'string' ? 'string-list' : 'unsupported';
  if (s.type === 'string') {
    if (s.format === 'uri' || /url$/i.test(name)) return 'url';
    if (s.format === 'date-time' || /(^|[a-z])(At|Start|End)$/.test(name)) return 'datetime';
    if (s.pattern?.includes('#') || /colou?r$/i.test(name)) return 'colour';
    if (TEXTAREA.has(name) || (s.maxLength ?? 0) > 400) return 'textarea';
    return 'text';
  }
  return 'unsupported';
}

export function fieldsFor(schema: z.ZodTypeAny, defaults: Record<string, unknown> = {}): Field[] {
  let json: JsonSchema;
  try {
    json = z.toJSONSchema(schema, { io: 'input' }) as JsonSchema;
  } catch {
    // A schema we cannot describe still has a working type; it just gets no
    // generated form. An empty panel beats a crashed editor.
    return [];
  }

  const props = json.properties ?? {};
  const required = new Set(json.required ?? []);

  return Object.entries(props).map(([name, prop]) => {
    const s = prop.anyOf?.find((b) => b.type || b.format || b.enum) ?? prop;
    return {
      name,
      label: humanise(name),
      kind: kindOf(name, prop),
      required: required.has(name),
      options: (s.enum ?? []).map(String),
      min: s.minimum,
      max: s.maximum ?? s.maxLength,
      default: prop.default ?? s.default ?? defaults[name],
    };
  });
}
