<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;
use Microsoft\Graph\Model\Message;

class CreateDraftReplyAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - message_id      (string) : original message ID to reply to (required)
     *   - body            (string) : reply body (optional)
     *   - store_output_as (string) : context key (default: draft_reply)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'draft_reply';
        $messageId = $this->interpolate($config['message_id'] ?? '', $context);
        $body = $this->interpolate($config['body'] ?? '', $context);

        $graph = $this->factory->make($workflow->user_id);

        $payload = [];

        if ($body !== '') {
            $payload['message'] = [
                'body' => ['contentType' => 'Text', 'content' => $body],
            ];
        }

        $draft = $graph->createRequest('POST', "/me/messages/{$messageId}/createReply")
            ->attachBody($payload)
            ->setReturnType(Message::class)
            ->execute();

        return array_merge($context, [
            $storeOutputAs => [
                'message_id'          => $draft->getId(),
                'original_message_id' => $messageId,
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
