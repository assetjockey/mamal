<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class SendDraftEmailAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - message_id      (string) : draft message ID to send (required)
     *   - store_output_as (string) : context key (default: sent_draft)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'sent_draft';
        $messageId = $this->interpolate($config['message_id'] ?? '', $context);

        $graph = $this->factory->make($workflow->user_id);

        $graph->createRequest('POST', "/me/messages/{$messageId}/send")
            ->execute();

        return array_merge($context, [
            $storeOutputAs => ['message_id' => $messageId, 'sent' => true],
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
