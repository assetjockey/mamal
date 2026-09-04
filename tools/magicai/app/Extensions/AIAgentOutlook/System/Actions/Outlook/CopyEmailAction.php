<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;
use Microsoft\Graph\Model\Message;

class CopyEmailAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - message_id        (string) : message ID to copy (required)
     *   - destination_id    (string) : destination folder ID or well-known name (required)
     *   - store_output_as   (string) : context key (default: copied_email)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'copied_email';
        $messageId = $this->interpolate($config['message_id'] ?? '', $context);
        $destinationId = $this->interpolate($config['destination_id'] ?? '', $context);

        $graph = $this->factory->make($workflow->user_id);

        $copy = $graph->createRequest('POST', "/me/messages/{$messageId}/copy")
            ->attachBody(['destinationId' => $destinationId])
            ->setReturnType(Message::class)
            ->execute();

        return array_merge($context, [
            $storeOutputAs => [
                'message_id'     => $copy->getId(),
                'original_id'    => $messageId,
                'destination_id' => $destinationId,
            ],
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
