export { presign, type SignOptions } from './sigv4.ts';
export {
  StorageError,
  type StorageAdapter,
  type UploadHandle,
  type PartRef,
} from './types.ts';
export {
  localAdapter,
  verifySignature,
  writeLocalPart,
  readLocalObject,
  type LocalOptions,
} from './local.ts';
export { s3Adapter, type S3Options } from './s3.ts';
export { resolveAdapter, adapterFor, type ProviderRow } from './resolve.ts';
export {
  newDataKey,
  wrapKey,
  unwrapKey,
  seal,
  open,
  type WrappedKey,
  type Sealed,
} from './crypto.ts';
