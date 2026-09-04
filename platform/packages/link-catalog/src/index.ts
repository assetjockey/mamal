export {
  BLOCK_CATEGORIES, BLOCK_FAMILIES, BLOCK_CATALOG, blockDef, blocksIn,
  type BlockCategory, type BlockFamily, type BlockDef,
} from './blocks.ts';

export {
  QR_CATEGORIES, QR_CATALOG, qrDef, qrTypesIn, encodePayload,
  type QrCategory, type QrDef,
} from './qr.ts';

export {
  BARCODE_FAMILIES, BARCODE_CATALOG, barcodeDef, barcodesIn,
  validateBarcode, withCheckDigit, gtinCheckDigit,
  type BarcodeFamily, type BarcodeDef, type Validity,
} from './barcodes.ts';

export {
  BODY_PATTERNS, INNER_EYE_SHAPES, OUTER_EYE_SHAPES, FRAMES, FRAME_FONTS,
  ERROR_CORRECTION, EXPORT_FORMATS, qrStyleSchema, styleWarnings,
  type QrStyle, type ExportFormat,
} from './styles.ts';

export { fieldsFor, type Field, type FieldKind } from './fields.ts';
