import { Button, EmptyState, PageHeader } from '@mamal/ui';

export default function Page() {
  return (
    <>
      <PageHeader title="Confirm — Flows" description="Social proof widgets and web-push campaigns." />
      <EmptyState
        title="Not built yet"
        description="This screen arrives in Phase 2. The shell, tenancy, billing, AI registry and interop bus it depends on are already live."
        action={<Button variant="ghost">Read the plan</Button>}
      />
    </>
  );
}
