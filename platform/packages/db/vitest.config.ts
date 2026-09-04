import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    // Live DB tests share one Postgres; running files in parallel would let
    // fixtures from one file leak into another's assertions.
    fileParallelism: false,
    env: {
      TEST_DATABASE_URL:
        process.env.TEST_DATABASE_URL ??
        'postgres://mamal_app:mamal_dev_pw@localhost:5432/mamal_dev',
    },
  },
});
