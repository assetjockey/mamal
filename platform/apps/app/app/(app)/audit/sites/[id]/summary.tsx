'use client';

import { AiPanel } from '../../ai-panel';
import { generateSummary } from '../../ai-actions';

export function AuditSummary({ auditId }: { auditId: string }) {
  return (
    <div className="mb-8">
      <AiPanel
        label="Summary of this audit"
        hint="Three paragraphs on what is wrong, what to fix first, and what can wait — written from this site's actual findings."
        action={() => generateSummary(auditId)}
      />
    </div>
  );
}
