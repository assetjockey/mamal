import { Queue, Worker, type ConnectionOptions, type Job, type JobsOptions } from 'bullmq';
import { QUEUES, type QueueName } from './queues.ts';

/**
 * BullMQ wiring.
 *
 * Queues are created lazily and cached: a process that only enqueues never
 * opens a worker connection, and a worker only subscribes to what it declares.
 */
let connection: ConnectionOptions | undefined;

export function redisConnection(): ConnectionOptions {
  if (!connection) {
    const url = new URL(process.env.REDIS_URL ?? 'redis://localhost:6379');
    connection = {
      host: url.hostname,
      port: Number(url.port || 6379),
      ...(url.password ? { password: url.password } : {}),
      // BullMQ requires this; without it a blocking command can be retried
      // forever against a dead node.
      maxRetriesPerRequest: null,
    };
  }
  return connection;
}

const queues = new Map<string, Queue>();

export function queue<T = unknown>(name: QueueName | string): Queue<T, void, string> {
  let existing = queues.get(name);
  if (!existing) {
    const spec = QUEUES[name as QueueName];
    existing = new Queue(name, {
      connection: redisConnection(),
      defaultJobOptions: {
        attempts: spec?.attempts ?? 3,
        backoff: { type: 'exponential', delay: 5_000 },
        // Keep a short tail for debugging; the DLQ table is the real record.
        removeOnComplete: { age: 3600, count: 500 },
        removeOnFail: { age: 86_400, count: 1000 },
      },
    });
    queues.set(name, existing);
  }
  return existing as Queue<T, void, string>;
}

export async function enqueue<T>(
  name: QueueName | string,
  jobName: string,
  data: T,
  opts: JobsOptions = {},
): Promise<string> {
  // BullMQ narrows the job-name type from the data type when the data is a
  // Job. Ours never is, but TypeScript cannot prove that for an unresolved
  // generic, so the queue is widened here rather than at every call site.
  const target = queue<T>(name) as unknown as {
    add(name: string, data: T, opts?: JobsOptions): Promise<{ id?: string }>;
  };
  const job = await target.add(jobName, data, opts);
  return job.id ?? '';
}

export type WorkerHandler<T> = (job: Job<T>) => Promise<void>;

export function startWorker<T>(
  name: QueueName | string,
  handler: WorkerHandler<T>,
  opts: { concurrency?: number } = {},
): Worker<T> {
  const spec = QUEUES[name as QueueName];
  const worker = new Worker<T>(name, handler, {
    connection: redisConnection(),
    concurrency: opts.concurrency ?? spec?.concurrency ?? 4,
  });

  worker.on('failed', (job, err) => {
    console.error(`[${name}] job ${job?.id} failed:`, err.message);
  });
  worker.on('error', (err) => {
    console.error(`[${name}] worker error:`, err.message);
  });

  return worker;
}

export async function closeQueues(): Promise<void> {
  await Promise.all([...queues.values()].map((q) => q.close()));
  queues.clear();
}

export type { Job, JobsOptions };
