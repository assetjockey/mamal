/**
 * The shell's navigation and each tool's manifest have to agree.
 *
 * `packages/ui` cannot import `tools/*` — the eslint boundary forbids it, and
 * the per-tool build matrix compiles the app with tools absent — so the shell
 * carries its own copy of the nav. That copy drifted the first time a tool's
 * screens changed, and the symptom was a sidebar advertising sections that had
 * been renamed. Nothing failed; the links simply went nowhere useful.
 *
 * This is the cheap guard: parse both, compare, and fail the build on a
 * difference. Run from `ci:check`.
 */
import { readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const TOOLS = ['audit', 'confirm', 'link', 'market'];

/** Pull `{ key, href, group, requires }` out of a source file's nav array. */
function navFrom(source, { arrayStart }) {
  const start = source.indexOf(arrayStart);
  if (start === -1) return null;

  // Walk to the matching bracket rather than regexing the whole array: nav
  // entries contain brackets of their own once a tool declares `children`.
  const open = source.indexOf('[', start);
  let depth = 0;
  let end = open;
  for (let i = open; i < source.length; i++) {
    if (source[i] === '[') depth++;
    else if (source[i] === ']') {
      depth--;
      if (depth === 0) { end = i; break; }
    }
  }
  const block = source.slice(open, end + 1);

  const items = [];
  for (const match of block.matchAll(/\{[^{}]*\bkey:\s*'([^']+)'[^{}]*\}/g)) {
    const entry = match[0];
    const field = (name) => entry.match(new RegExp(`${name}:\\s*'([^']*)'`))?.[1];
    items.push({
      key: match[1],
      href: field('href'),
      group: field('group'),
      requires: field('requires') ?? null,
    });
  }
  return items;
}

const shell = readFileSync(join(root, 'packages/ui/src/shell/nav.ts'), 'utf8');
let failures = 0;

for (const tool of TOOLS) {
  const manifestPath = join(root, `tools/${tool}/src/manifest.ts`);
  if (!existsSync(manifestPath)) continue;

  const manifestNav = navFrom(readFileSync(manifestPath, 'utf8'), { arrayStart: '\n  nav:' });
  // Each tool's block in the shell list starts at its `key: '<tool>'`.
  const shellBlock = shell.slice(shell.indexOf(`key: '${tool}'`));
  const shellNav = navFrom(shellBlock, { arrayStart: '\n    items:' });

  if (!manifestNav || !shellNav) {
    console.error(`check-nav: could not read ${tool}'s navigation from both sides.`);
    failures++;
    continue;
  }

  const asText = (items) =>
    items.map((i) => `${i.key} ${i.href} [${i.group ?? '-'}] ${i.requires ?? '-'}`).sort();

  const fromManifest = asText(manifestNav);
  const fromShell = asText(shellNav);

  const missing = fromManifest.filter((i) => !fromShell.includes(i));
  const extra = fromShell.filter((i) => !fromManifest.includes(i));

  if (missing.length || extra.length) {
    failures++;
    console.error(`\ncheck-nav: ${tool}'s manifest and the shell disagree.`);
    for (const item of missing) console.error(`  only in the manifest: ${item}`);
    for (const item of extra) console.error(`  only in the shell:    ${item}`);
  } else {
    console.log(`check-nav: ${tool} … ${fromManifest.length} items agree`);
  }
}

if (failures > 0) {
  console.error(
    '\nUpdate packages/ui/src/shell/nav.ts to match, or the sidebar will point at ' +
      'sections that do not exist.',
  );
  process.exit(1);
}
console.log('check-nav: every tool\'s navigation matches its manifest.');
