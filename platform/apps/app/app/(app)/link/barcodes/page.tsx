import { redirect } from 'next/navigation';
import { sql } from 'drizzle-orm';
import { withWorkspace } from '@mamal/db';
import { BARCODE_CATALOG, BARCODE_FAMILIES } from '@mamal/link-catalog';
import { PageHeader } from '@mamal/ui';
import { getSession } from '@/lib/session';
import { db } from '@/lib/db';
import { BarcodeStudio } from './client';

export const dynamic = 'force-dynamic';

export default async function Barcodes() {
  const session = await getSession();
  if (!session) redirect('/sign-in');
  const ws = session.workspace.id;

  const saved = await withWorkspace(
    ws,
    (tx) => tx.execute<{ id: string; symbology: string; value: string }>(sql`
      select id, symbology, value from barcodes
       where workspace_id = ${ws} order by created_at desc limit 50`),
    { db: db() },
  );

  return (
    <>
      <PageHeader
        title="Barcodes"
        description="29 symbologies, each validated before it can be saved — a wrong check digit is ten thousand labels no scanner will read."
      />
      <BarcodeStudio
        families={BARCODE_FAMILIES.map((family) => ({
          family,
          items: BARCODE_CATALOG.filter((b) => b.family === family).map((b) => ({
            key: b.key, label: b.label, description: b.description,
            example: b.example, checkDigit: b.checkDigit,
          })),
        }))}
        saved={saved}
      />
    </>
  );
}
