/**
 * vCard, event and static links.
 *
 * Placeholders with an honest message rather than a blank page. The redirect
 * resolver already routes these kinds here, so the route must answer — and
 * telling a visitor the page is not ready is better than a 404 that makes the
 * *link* look broken to the person who printed it.
 */
export function CardPage({
  kind,
  title,
}: {
  kind: 'vcard' | 'event' | 'static';
  title: string | null;
}) {
  const what =
    kind === 'vcard' ? 'contact card' : kind === 'event' ? 'calendar event' : 'page';

  return (
    <main className="mx-auto grid min-h-dvh w-full max-w-[480px] content-center gap-4 px-4 py-12 text-center">
      <h1 className="text-[26px] font-light tracking-[-0.01em] text-[var(--text-primary)]">
        {title ?? `This ${what}`}
      </h1>
      <p className="text-[15px] text-[var(--text-secondary)]">
        This {what} has not been published yet. The link works — there is just
        nothing on it so far.
      </p>
    </main>
  );
}
