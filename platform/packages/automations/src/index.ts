export {
  parseDefinition,
  definitionSchema,
  conditionSchema,
  actionSchema,
  interpolate,
  readPath,
  parseDuration,
  CONDITION_OPS,
  ACTION_TYPES,
  type Definition,
  type DefinitionInput,
  type Condition,
  type Action,
  type ConditionOp,
  type ActionType,
} from './dsl.ts';
export { evaluate, evaluateAll, type EvalContext, type ConditionResult } from './conditions.ts';
export { runActions, type ActionDeps, type CommandExecutor, type StepResult } from './actions.ts';
export { AutomationRunner, type AutomationRow, type RunOutcome } from './runner.ts';
// Seed data lives in @mamal/db so the schema package stays a leaf; re-exported
// here because the engine is where callers expect to find it.
export { AUTOMATION_TEMPLATES, type TemplateSeed } from '@mamal/db';
