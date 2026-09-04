'use client';

import { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import { Button, EmptyState, SectionLabel, Table, Td, Th, Tr, useToast } from '@mamal/ui';
import { newFolder, removeFolder, renameFolder } from '../actions';

export function FolderList({
  folders,
}: {
  folders: { id: string; name: string; color: string | null; links: number }[];
}) {
  const [name, setName] = useState('');
  const [pending, start] = useTransition();
  const toast = useToast();
  const router = useRouter();

  const create = () => {
    if (!name.trim()) return;
    start(async () => {
      const result = await newFolder(name.trim());
      if (!result.ok) { toast({ kind: 'error', message: result.error }); return; }
      setName('');
      router.refresh();
    });
  };

  return (
    <>
      <div className="mb-6 flex flex-wrap items-center gap-2">
        <label htmlFor="folder-name" className="sr-only">Folder name</label>
        <input
          id="folder-name" value={name} onChange={(e) => setName(e.target.value)}
          onKeyDown={(e) => { if (e.key === 'Enter') create(); }}
          placeholder="Spring campaign"
          className="w-[min(280px,60vw)] rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-3 py-2 text-[14px] text-[var(--text-primary)] outline-none focus-visible:border-[var(--accent-solid)]"
        />
        <Button onClick={create} disabled={pending || !name.trim()}>New folder</Button>
      </div>

      {folders.length === 0 ? (
        <EmptyState
          title="No folders yet"
          description="Folders are optional — every link works without one. They matter once you have more links than you can scan."
        />
      ) : (
        <>
          <SectionLabel>Folders</SectionLabel>
          <Table label="Folders">
            <thead>
              <Tr><Th>Name</Th><Th align="right">Links</Th><Th align="right"> </Th></Tr>
            </thead>
            <tbody>
              {folders.map((f) => (
                <Row key={f.id} folder={f} pending={pending} start={start} />
              ))}
            </tbody>
          </Table>
        </>
      )}
    </>
  );
}

function Row({
  folder,
  pending,
  start,
}: {
  folder: { id: string; name: string; links: number };
  pending: boolean;
  start: (fn: () => void) => void;
}) {
  const [editing, setEditing] = useState(false);
  const [name, setName] = useState(folder.name);
  const toast = useToast();
  const router = useRouter();

  return (
    <Tr>
      <Td>
        {editing ? (
          <input
            autoFocus value={name} onChange={(e) => setName(e.target.value)}
            aria-label={`Rename ${folder.name}`}
            onKeyDown={(e) => {
              if (e.key === 'Escape') { setEditing(false); setName(folder.name); }
              if (e.key === 'Enter') {
                start(async () => {
                  await renameFolder(folder.id, name.trim() || folder.name);
                  setEditing(false);
                  router.refresh();
                });
              }
            }}
            className="rounded-[4px] border border-[var(--border-hairline)] bg-[var(--surface-ground)] px-2 py-1 text-[14px] text-[var(--text-primary)]"
          />
        ) : (
          <button
            type="button" onClick={() => setEditing(true)}
            className="text-left text-[var(--text-primary)] hover:text-[var(--accent)] focus-visible:outline-2 focus-visible:outline-[var(--accent-solid)]"
          >
            {folder.name}
          </button>
        )}
      </Td>
      <Td align="right"><span className="tabular-nums">{folder.links.toLocaleString()}</span></Td>
      <Td align="right">
        <Button
          size="sm" variant="quiet" disabled={pending}
          onClick={() => start(async () => {
            await removeFolder(folder.id);
            // The links survive: deleting a folder must never delete what is in
            // it, and the toast says so before anyone has to find out.
            toast({ kind: 'info', message: `Deleted “${folder.name}”. Its links are still there, just unfiled.` });
            router.refresh();
          })}
        >
          Delete
        </Button>
      </Td>
    </Tr>
  );
}
