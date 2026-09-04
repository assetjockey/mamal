import { Button, EmptyState, PageHeader } from '@mamal/ui';

export default function Page() {
  return (
    <>
      <PageHeader title="Market — Content" description="SEO, AI visibility, content, social and ads." />
      <EmptyState
        title="Not built yet"
        description="This screen arrives in Phase 4. The shell, tenancy, billing, AI registry and interop bus it depends on are already live."
        action={<Button variant="ghost">Read the plan</Button>}
      />
    </>
  );
}
