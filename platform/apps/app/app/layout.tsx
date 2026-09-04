import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Mamal',
  description: 'Audit, Confirm, Link, Market, Monitor, Track — one workspace.',
};

/**
 * Route groups own their own <html>/<body>: the signed-out chrome and the
 * app shell are different documents, not one wrapped in a conditional.
 */
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return children;
}
