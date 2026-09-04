<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class FlagUnflagEmailAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - message_id      (string) : message ID to update (required)
     *   - flag_status     (string) : notFlagged | flagged | complete (required)
     *   - store_output_as (string) : context key (default: flag_result)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'flag_result';
        $messageId = $this->interpolate($config['message_id'] ?? '', $context);
        $flagStatus = $config['flag_status'] ?? 'flagged';

        $graph = $this->factory->make($workflow->user_id);

        $graph->createRequest('PATCH', "/me/messages/{$messageId}")
            ->attachBody(['flag' => ['flagStatus' => $flagStatus]])
            ->execute();

        return array_merge($context, [
            $storeOutputAs => ['message_id' => $messageId, 'flag_status' => $flagStatus],
        ]);
    }

    private function interpolate(string $template, array $context): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function (array $matches) use ($context): string {
            $value = $context[$matches[1]] ?? $matches[0];

            if (is_array($value)) {
                return json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ?: $matches[0];
            }

            return (string) $value;
        }, $template);
    }
}
