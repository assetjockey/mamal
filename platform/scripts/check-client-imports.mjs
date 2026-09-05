/**
 * Two checks, both guarding one bug.
 *
 * Every `tools/x/src/index.ts` re-exports its runners and services, which reach
 * `@mamal/db`, then `postgres`, then `fs`. Anything that drags that into a
 * browser bundle fails the build with "Module not found: Can't resolve 'fs'"
 * pointing at node_modules rather than at the import that caused it — a
 * genuinely hard error to trace back to its source.
 *
 * 1. **No client component imports a tool's bare barrel.** Type-only imports
 *    are fine; they are erased before bundling.
 *
 * 2. **A browser-safe subpath is actually browser-safe.** This is the half that
 *    was missing the first time: the client component imported the approved
 *    `@mamal/tool-market/scoring` subpath, and the *subpath* had picked up a
 *    re-export from a module that touches the database. Checking the import
 *    site alone said everything was fine while the build was broken.
 */
import { globSync, readFileSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';

/** Packages that reach a socket, a file handle, or Postgres. */
const SERVER_ONLY = [
  '@mamal/db', '@mamal/ai', '@mamal/credits', '@mamal/entitlements',
  '@mamal/resources', '@mamal/bus', '@mamal/storage', '@mamal/notify',
  'drizzle-orm', 'postgres', 'ioredis', 'node:fs', 'node:net', 'node:tls',
];

const problems = [];

/* -- 1. client components may not import a tool's barrel ------------------ */

const appFiles = globSync('apps/*/app/**/*.{ts,tsx}', { cwd: process.cwd() });

for (const file of appFiles) {
  const source = readFileSync(file, 'utf8');
  // The directive has to be the first statement, so only the head is scanned.
  if (!/^\s*(['"])use client\1/m.test(source.slice(0, 400))) continue;

  const pattern = /^\s*import\s+(?!type\b)([^;]*?)\s+from\s+['"](@mamal\/tool-[a-z]+)['"]/gm;
  for (const match of source.matchAll(pattern)) {
    // `import { type A, type B }` is erased in full, same as `import type`.
    const names = match[1]
      .replace(/^\{|\}$/g, '')
      .split(',')
      .map((name) => name.trim())
      .filter(Boolean);
    if (names.length > 0 && names.every((name) => name.startsWith('type '))) continue;

    problems.push(`${file}: imports values from ${match[2]} — use a browser-safe subpath`);
  }
}

/* -- 2. every browser-safe subpath is pure, transitively ------------------ */

/**
 * Follows *value* imports from an entry file and returns every file reached.
 *
 * `import type` and `export type … from` are skipped: TypeScript erases them
 * before anything is bundled, so a type-only edge into a database module is
 * genuinely harmless. Following them would report leaks that do not exist and
 * push somebody to restructure working code.
 */
function reachableFrom(entry) {
  const seen = new Set();
  const queue = [entry];

  while (queue.length > 0) {
    const file = queue.pop();
    if (seen.has(file) || !existsSync(file)) continue;
    seen.add(file);

    const source = readFileSync(file, 'utf8');
    for (const match of source.matchAll(
      /(^|\n)\s*(import|export)\s+([\s\S]*?)from\s+['"](\.[^'"]+)['"]/g,
    )) {
      if (isTypeOnly(match[3])) continue;
      queue.push(resolve(dirname(file), match[4]));
    }
  }
  return seen;
}

/** `type { A }`, or `{ type A, type B }` — both vanish at compile time. */
function isTypeOnly(clause) {
  const trimmed = clause.trim();
  if (trimmed.startsWith('type ') || trimmed.startsWith('type{')) return true;

  const braces = trimmed.match(/^\{([\s\S]*)\}$/);
  if (!braces) return false;
  const names = braces[1].split(',').map((n) => n.trim()).filter(Boolean);
  return names.length > 0 && names.every((n) => n.startsWith('type '));
}

const toolManifests = globSync('tools/*/package.json', { cwd: process.cwd() });
let subpathsChecked = 0;

for (const manifestPath of toolManifests) {
  const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
  const exports = manifest.exports ?? {};

  /*
   * A tool *declares* which of its subpaths promise browser safety, in a
   * `browserSafe` array. Guessing "everything except '.'" was wrong: a tool's
   * `./commands` subpath is a server-side cross-tool surface and never claimed
   * otherwise, so checking it produced noise rather than a finding.
   */
  const declared = new Set(manifest.browserSafe ?? []);

  for (const [subpath, target] of Object.entries(exports)) {
    if (!declared.has(subpath) || typeof target !== 'string') continue;
    subpathsChecked += 1;

    const entry = resolve(dirname(manifestPath), target);
    for (const file of reachableFrom(entry)) {
      const source = readFileSync(file, 'utf8');
      for (const pkg of SERVER_ONLY) {
        const pattern = new RegExp(
          `(^|\\n)\\s*(import|export)\\s+([\\s\\S]*?)from\\s+['"]${pkg.replace('/', '\\/')}['"]`,
        );
        const match = source.match(pattern);
        // Type-only again: erased, and therefore not a leak.
        if (match && !isTypeOnly(match[3])) {
          problems.push(
            `${manifest.name}${subpath.slice(1)}: reaches ${pkg} through ` +
              `${file.replace(`${process.cwd()}/`, '')} — the subpath is not browser-safe`,
          );
        }
      }
    }
  }
}

if (problems.length > 0) {
  console.error('check-client-imports: server code is reachable from the browser.\n');
  for (const problem of [...new Set(problems)]) console.error(`  ${problem}`);
  console.error(
    '\nMove the pure logic into its own module and re-export that, rather than\n' +
      'widening a browser-safe subpath to cover a file that touches the database.',
  );
  process.exit(1);
}

console.log(
  `check-client-imports: ${appFiles.length} app file(s) and ${subpathsChecked} ` +
    'browser-safe subpath(s) scanned, no server code reachable from the browser.',
);
