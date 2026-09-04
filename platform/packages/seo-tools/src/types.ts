export const TOOL_CATEGORIES = ['research', 'development', 'content'] as const;
export type ToolCategory = (typeof TOOL_CATEGORIES)[number];

export type ToolField = {
  name: string;
  label: string;
  type: 'text' | 'url' | 'textarea' | 'number' | 'select';
  placeholder?: string;
  required?: boolean;
  options?: { value: string; label: string }[];
  hint?: string;
};

export type ToolOutput =
  | { kind: 'text'; value: string }
  | { kind: 'table'; columns: string[]; rows: (string | number)[][] }
  | { kind: 'pairs'; pairs: { label: string; value: string }[] }
  | { kind: 'error'; message: string };

export type Tool = {
  slug: string;
  name: string;
  category: ToolCategory;
  description: string;
  /** Why someone would reach for this, in one sentence a non-specialist gets. */
  why: string;
  fields: ToolField[];
  /**
   * True when the tool makes an outbound HTTP request. Those are rate-limited
   * per IP and go through the SSRF guard; the pure ones are not, because they
   * cost nothing to run.
   */
  fetches?: boolean;
  run(input: Record<string, string>): Promise<ToolOutput> | ToolOutput;
};
