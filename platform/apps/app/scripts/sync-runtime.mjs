#!/usr/bin/env node
/**
 * Copies the built confirm.js into `public/`.
 *
 * A static file, not a route: it is byte-identical for every customer and must
 * never cost an origin request. In production the same artefact is uploaded to
 * R2 and served from the CDN; `public/` is what makes local development behave
 * the same way without standing up a second server.
 */
import { copyFileSync, existsSync, mkdirSync } from 'node:fs';

const src = new URL('../../../packages/widget-runtime/dist/confirm.js', import.meta.url).pathname;
const dir = new URL('../public/', import.meta.url).pathname;

if (!existsSync(src)) {
  console.error('confirm.js is not built — run `pnpm --filter @mamal/widget-runtime build`.');
  process.exit(1);
}
mkdirSync(dir, { recursive: true });
copyFileSync(src, `${dir}confirm.js`);
console.log('confirm.js -> apps/app/public/confirm.js');
