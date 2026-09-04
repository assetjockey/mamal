/**
 * The queue taxonomy.
 *
 * Concurrency is the cost cap for anything that runs in a container: the
 * Lighthouse pool cannot cost more than `concurrency` simultaneous 2 GB
 * processes, whatever the queue depth.
 */
export type QueueSpec = {
  name: string;
  concurrency: number;
  /**
   * Probes are 0 on purpose. Retrying a failed uptime check corrupts the
   * uptime maths — a failed probe IS the signal, not an error to paper over.
   */
  attempts: number;
  description: string;
};

export const QUEUES = {
  'bus.dispatch': { name: 'bus.dispatch', concurrency: 32, attempts: 8,
    description: 'Bus subscribers, partitioned by subject for per-subject ordering' },
  automations: { name: 'automations', concurrency: 16, attempts: 3,
    description: 'One job per automation run' },
  'monitor.check': { name: 'monitor.check', concurrency: 200, attempts: 0,
    description: 'Uptime probes — never retried' },
  'monitor.probe.heavy': { name: 'monitor.probe.heavy', concurrency: 20, attempts: 0,
    description: 'Game query, server agent, port scans' },
  'audit.crawl': { name: 'audit.crawl', concurrency: 8, attempts: 3,
    description: 'Crawl parent + per-page children' },
  'audit.lighthouse': { name: 'audit.lighthouse', concurrency: 4, attempts: 2,
    description: '2 GB containers — concurrency is the cost cap' },
  'ai.text': { name: 'ai.text', concurrency: 20, attempts: 3, description: 'Text generation' },
  'ai.image': { name: 'ai.image', concurrency: 10, attempts: 3, description: 'Image generation' },
  'ai.video': { name: 'ai.video', concurrency: 4, attempts: 3,
    description: 'Video — separate because latency is minutes, not seconds' },
  media: { name: 'media', concurrency: 8, attempts: 3, description: 'FFmpeg, sharp, QR raster, PDF' },
  notify: { name: 'notify', concurrency: 64, attempts: 5, description: 'Every outbound channel' },
  'ingest.bridge': { name: 'ingest.bridge', concurrency: 16, attempts: 5,
    description: 'Edge batches into the event store' },
  rollup: { name: 'rollup', concurrency: 4, attempts: 3, description: 'Daily aggregates' },
  maintenance: { name: 'maintenance', concurrency: 2, attempts: 2,
    description: 'Retention, quota reset, credit expiry' },
  // Free tenants run here: concurrency 1, no autoscaling. A free user can never
  // induce a scale-up event — their jobs are slow, and that IS the distinction.
  'free.crawl': { name: 'free.crawl', concurrency: 1, attempts: 1, description: 'Free-tier crawls' },
  'free.probe': { name: 'free.probe', concurrency: 2, attempts: 0, description: 'Free-tier probes' },
} as const satisfies Record<string, QueueSpec>;

export type QueueName = keyof typeof QUEUES;

/** Free workspaces are routed to the throttled mirror of a queue when one exists. */
export function queueFor(name: QueueName, isFreeTier: boolean): string {
  if (!isFreeTier) return name;
  const mirror = `free.${name.split('.').pop()}`;
  return mirror in QUEUES ? mirror : name;
}
