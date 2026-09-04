export {
  envelopeSchema,
  partitionFor,
  DepthExceeded,
  MAX_DEPTH,
  MAX_AUTOMATION_DEPTH,
  type Envelope,
} from './envelope.ts';
export { EventRegistry, coreEvents, type EventDef } from './registry.ts';
export {
  InProcessTransport,
  RedisStreamTransport,
  type Transport,
  type RedisLike,
} from './transport.ts';
export { publish, type PublishInput } from './publish.ts';
export { OutboxRelay } from './relay.ts';
export { Dispatcher, type Handler, type HandlerTx, type DispatchResult } from './dispatch.ts';
