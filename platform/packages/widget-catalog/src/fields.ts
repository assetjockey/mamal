import { z } from 'zod';
import type { WidgetDef } from './types.ts';

/**
 * Form fields, derived from the type's own zod schema.
 *
 * This is what "the catalogue is data" buys. A widget's schema already
 * validates every write and already generates its JSON Schema; deriving the
 * editor form from the same source means a new widget type gets a working
 * editor with no UI written for it, and — more importantly — the form can never
 * offer a field the validator will reject, or omit one it requires.
 *
 * Via `z.toJSONSchema` rather than by walking zod internals: that is a public,
 * versioned surface, and it is the same call the MCP server makes to describe
 * its tools. One mechanism, two consumers.
 */

export type FieldKind =
  | 'text' | 'textarea' | 'number' | 'boolean' | 'select' | 'url' | 'colour' | 'datetime'
  | 'string-list' | 'unsupported';

export type Field = {
  name: string;
  label: string;
  kind: FieldKind;
  required: boolean;
  /** Present for `select`. */
  options?: string[];
  min?: number;
  max?: number;
  default?: unknown;
  /** Text that may carry `{{token}}` interpolation — the editor says so. */
  templated: boolean;
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
  maxLength?: number;
  default?: unknown;
  items?: JsonSchema;
  anyOf?: JsonSchema[];
};

/** `linkLabel` -> `Link label`, `utm_source` -> `Utm source`. */
function humanise(name: string): string {
  const spaced = name.replace(/_/g, ' ').replace(/([a-z0-9])([A-Z])/g, '$1 $2');
  return spaced.charAt(0).toUpperCase() + spaced.slice(1);
}

/** Long-form copy deserves a textarea; a heading does not. */
const TEXTAREA = new Set(['body', 'html', 'followUp', 'consentText', 'successMessage', 'placeholder']);
const TEMPLATED = new Set(['title', 'body', 'followUp', 'successMessage', 'consentText', 'linkLabel']);

function kindOf(name: string, schema: JsonSchema): FieldKind {
  // `anyOf` is how zod emits a union — `url().or(literal(''))` for an optional
  // URL, most commonly. Take the first branch that we can render.
  const s = schema.anyOf?.find((b) => b.type || b.format || b.enum) ?? schema;

  if (s.enum && s.enum.length > 0) return 'select';
  if (s.const !== undefined) return 'boolean';
  if (s.type === 'boolean') return 'boolean';
  if (s.type === 'number' || s.type === 'integer') return 'number';
  if (s.type === 'array') return s.items?.type === 'string' ? 'string-list' : 'unsupported';
  if (s.type === 'string') {
    if (s.format === 'uri' || /Url$/.test(name)) return 'url';
    if (s.format === 'date-time') return 'datetime';
    if (s.pattern === '^#[0-9a-fA-F]{6}$' || /Color$/.test(name)) return 'colour';
    return TEXTAREA.has(name) ? 'textarea' : 'text';
  }
  // An object or a shape we do not render — `lead_form.fields` is the only one,
  // and it gets its own editor rather than a generic control.
  return 'unsupported';
}

export function fieldsFor(def: WidgetDef): Field[] {
  let json: JsonSchema;
  try {
    json = z.toJSONSchema(def.settings, { io: 'input' }) as JsonSchema;
  } catch {
    // A schema we cannot describe still has a working widget; it just has no
    // generated form. Better an empty panel than a crashed editor.
    return [];
  }

  const props = json.properties ?? {};
  const required = new Set(json.required ?? []);

  return Object.entries(props).map(([name, schema]) => {
    const s = schema.anyOf?.find((b) => b.type || b.format || b.enum) ?? schema;
    return {
      name,
      label: humanise(name),
      kind: kindOf(name, schema),
      required: required.has(name),
      options: (s.enum ?? []).map(String),
      min: s.minimum,
      max: s.maximum ?? s.maxLength,
      default: schema.default ?? s.default ?? def.defaults[name],
      templated: TEMPLATED.has(name),
    };
  });
}
