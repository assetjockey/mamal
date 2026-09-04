#!/usr/bin/env node
/**
 * Proves each tool can be installed alone.
 *
 * "Tools work independently" is only true if it is checked. The eslint boundary
 * stops a direct import; this catches the subtler failure — a tool that
 * compiles only because a sibling happens to be present.
 */
import { execFileSync } from 'node:child_process';
import { readdirSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const root = new URL('..', import.meta.url).pathname;
const toolsDir = join(root, 'tools');

const tools = existsSync(toolsDir)
  ? readdirSync(toolsDir, { withFileTypes: true })
      .filter((e) => e.isDirectory() && existsSync(join(toolsDir, e.name, 'package.json')))
      .map((e) => e.name)
  : [];

if (tools.length === 0) {
  console.log('build-matrix: no tool packages yet — nothing to check.');
  process.exit(0);
}

let failed = 0;
for (const tool of tools) {
  process.stdout.write(`build-matrix: ${tool} alone … `);
  try {
    execFileSync('pnpm', ['--filter', `./tools/${tool}...`, 'typecheck'], {
      cwd: root,
      stdio: 'pipe',
    });
    console.log('ok');
  } catch (err) {
    failed++;
    console.log('FAILED');
    console.error(String(err.stdout ?? err.message).slice(0, 2000));
  }
}

if (failed > 0) {
  console.error(`\n${failed} tool(s) do not build in isolation.`);
  process.exit(1);
}
console.log(`build-matrix: all ${tools.length} tools build in isolation.`);
