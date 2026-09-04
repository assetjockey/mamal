'use client';

import { useEffect } from 'react';
import { Button, ErrorState } from '@mamal/ui';

/**
 * The error boundary for every route under (app). Without it a thrown server
 * component drops the user on Next's unstyled default page, outside the shell
 * and with no way forward but the back button.
 *
 * `reset()` re-renders the segment, which is the right offer: most failures
 * here are a dropped database connection or an expired upstream token, and
 * both clear on a retry.
 */
export default function AppError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error('route error', error);
  }, [error]);

  return (
    <ErrorState
      description="This page could not be loaded. The problem is on our side, not with anything you did."
      // The digest is the only handle support has on a production stack trace,
      // so surface it rather than swallowing it.
      detail={error.digest ? `Reference: ${error.digest}` : error.message}
      retry={<Button onClick={reset}>Try again</Button>}
    />
  );
}
