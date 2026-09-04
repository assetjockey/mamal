import { PageSkeleton } from '@mamal/ui';

/**
 * The streaming fallback for every route under (app). Next renders this the
 * moment a navigation starts, so a slow server component shows the shape of
 * the page immediately instead of leaving the previous one frozen.
 */
export default function Loading() {
  return <PageSkeleton />;
}
