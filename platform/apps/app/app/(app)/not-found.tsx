import Link from 'next/link';
import { Button, EmptyState } from '@mamal/ui';

/** A 404 inside the shell, so the sidebar is still there to leave by. */
export default function NotFound() {
  return (
    <EmptyState
      title="This page does not exist"
      description="The link may be out of date, or the resource may have been deleted."
      action={
        <Link href="/">
          <Button>Back to dashboard</Button>
        </Link>
      }
    />
  );
}
