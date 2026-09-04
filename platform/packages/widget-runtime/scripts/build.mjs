#!/usr/bin/env node
/**
 * Bundles confirm.js and reports its gzipped size.
 *
 * The size is the product constraint, not a nice-to-have: this script blocks
 * rendering on someone else's site, and the free tier's whole cost model
 * assumes it is cheap to serve. `pnpm test` asserts the number this prints.
 */
import { build } from 'esbuild';
import { gzipSync, brotliCompressSync } from 'node:zlib';
import { mkdirSync, writeFileSync } from 'node:fs';

const out = new URL('../dist/', import.meta.url).pathname;
mkdirSync(out, { recursive: true });

const result = await build({
  entryPoints: [new URL('../src/runtime.ts', import.meta.url).pathname],
  bundle: true,
  minify: true,
  format: 'iife',
  /*
   * The honest floor for what this code uses.
   *
   * Shadow DOM and sendBeacon are ancient; `adoptedStyleSheets` is Safari 16.4,
   * which is why there is an inline-<style> fallback for older engines. Aiming
   * lower would mean esbuild lowering syntax it does not need to and shipping
   * polyfills for browsers nobody in this audience is using — and the size
   * budget is the product constraint here, not compatibility with 2019.
   */
  target: ['es2022', 'chrome90', 'firefox90', 'safari15'],
  legalComments: 'none',
  write: false,
});

const code = result.outputFiles[0].text;
writeFileSync(`${out}confirm.js`, code);

const raw = Buffer.byteLength(code);
const gz = gzipSync(code, { level: 9 }).length;
const br = brotliCompressSync(code).length;
const kb = (n) => (n / 1024).toFixed(2);

writeFileSync(`${out}size.json`, JSON.stringify({ raw, gzip: gz, brotli: br }, null, 2));

console.log(`confirm.js  raw ${kb(raw)} KB   gzip ${kb(gz)} KB   brotli ${kb(br)} KB`);
console.log(`budget      gzip 12.00 KB      ${gz <= 12288 ? '✓ under' : '✗ OVER'} by ${kb(Math.abs(12288 - gz))} KB`);
if (gz > 12288) process.exit(1);
