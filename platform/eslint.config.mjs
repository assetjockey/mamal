import tseslint from 'typescript-eslint';

/**
 * The architectural boundaries, enforced by CI.
 *
 * One "temporary" direct cross-tool import and the whole independence story is
 * gone — so it is a lint error, not a convention.
 */
const TOOLS = ['audit', 'confirm', 'link', 'market', 'monitor', 'track'];

/** A tool may import shared packages and another tool's `commands` — nothing else. */
const crossToolPatterns = TOOLS.flatMap((tool) => [
  {
    group: [`@mamal/tool-${tool}`, `@mamal/tool-${tool}/*`, `**/tools/${tool}/**`],
    message:
      `Do not import tools/${tool} directly. Cross-tool work goes through ` +
      `commands.dispatch('${tool}.<verb>') so a workspace without ${tool} installed ` +
      `degrades instead of failing to build.`,
  },
]);

export default tseslint.config(
  {
    ignores: [
      '**/node_modules/**', '**/.next/**', '**/dist/**', '**/.turbo/**', '**/vendor/**',
      // Build output copied in for serving — minified, and not ours to lint.
      'apps/*/public/**',
    ],
  },

  ...tseslint.configs.recommended,

  {
    rules: {
      '@typescript-eslint/no-unused-vars': [
        'error',
        { argsIgnorePattern: '^_', varsIgnorePattern: '^_' },
      ],
      '@typescript-eslint/no-explicit-any': 'warn',
    },
  },

  // ---- tools may not reach into each other ------------------------------
  {
    files: ['tools/*/src/**/*.{ts,tsx}'],
    rules: {
      'no-restricted-imports': ['error', { patterns: crossToolPatterns }],
    },
  },

  // ---- only packages/ai may touch a provider SDK -------------------------
  // The third enforcement point for "lifetime plans exclude AI": if a driver
  // can only be constructed inside packages/ai, there is no path around
  // ai.execute()'s entitlement re-check.
  {
    files: ['**/*.{ts,tsx}'],
    ignores: ['packages/ai/**'],
    rules: {
      'no-restricted-imports': [
        'error',
        {
          patterns: [
            {
              group: [
                '@anthropic-ai/*', 'openai', 'openai/*', '@google/generative-ai',
                'replicate', '@fal-ai/*', '@ai-sdk/*', 'ai',
                // @mamal/ai is the sanctioned boundary, not a provider SDK:
                // banning it would push callers toward a provider directly.
                '!@mamal/ai',
              ],
              message:
                'AI provider SDKs may only be imported inside packages/ai/drivers. ' +
                'Everything else calls ai.execute(ctx, featureKey), which re-resolves ' +
                'entitlements server-side and cannot be bypassed.',
            },
          ],
        },
      ],
    },
  },

  // ---- the raw DB client is not exported for a reason --------------------
  {
    files: ['tools/**/*.{ts,tsx}', 'apps/*/app/**/*.{ts,tsx}', 'apps/*/lib/**/*.{ts,tsx}'],
    /*
     * Test fixtures, long-running workers and CLI scripts legitimately span
     * tenants — they are composition roots, not request handlers, and there is
     * no session to derive a workspace from. All three still go through
     * withWorkspace/asPlatformAdmin for the actual work; what they need the raw
     * client for is *obtaining* a handle.
     *
     * `packages/**` scripts were already outside this rule's `files`; naming
     * scripts explicitly makes that consistent rather than accidental.
     */
    ignores: ['**/__tests__/**', 'services/**', '**/scripts/**'],
    rules: {
      'no-restricted-syntax': [
        'error',
        {
          selector:
            "ImportSpecifier[imported.name='unsafeUnscopedDb']",
          message:
            'unsafeUnscopedDb bypasses row level security. Use withWorkspace(), or ' +
            'asPlatformAdmin() if this genuinely spans tenants.',
        },
      ],
    },
  },
);
