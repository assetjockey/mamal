import type { Config } from 'drizzle-kit';

export default {
  schema: './src/schema/index.ts',
  out: './src/migrations',
  dialect: 'postgresql',
  dbCredentials: {
    url: process.env.DATABASE_URL ?? 'postgres://localhost:5432/mamal_dev',
  },
  casing: 'snake_case',
  strict: true,
  verbose: true,
} satisfies Config;
