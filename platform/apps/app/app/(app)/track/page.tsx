import { Button, EmptyState, PageHeader } from '@mamal/ui';

export default function Page() {
  return (
    <>
      <PageHeader title="Track" description="Analytics, replays, heatmaps and funnels." />
      <EmptyState
        title="Not built yet"
        description="This screen arrives in Phase 6. The shell, tenancy, billing, AI registry and interop bus it depends on are already live."
        action={<Button variant="ghost">Read the plan</Button>}
      />
    </>
  );
}
