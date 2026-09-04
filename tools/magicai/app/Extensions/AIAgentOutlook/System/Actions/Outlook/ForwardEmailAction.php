<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class ForwardEmailAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - message_id      (string) : message ID to forward (required)
     *   - to              (string) : recipient email address (required)
     *   - comment         (string) : optional forwarding note (optional)
     *   - store_output_as (string) : context key (default: forward_result)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'forward_result';
        $messageId = $this->interpolate($config['message_id'] ?? '', $context);
        $to = $this->interpolate($config['to'] ?? '', $context);
        $comment = $this->interpolate($config['comment'] ?? '', $context);

        $graph = $this->factory->make($workflow->user_id);

        $payload = [
            'toRecipients' => array_map(
                fn (string $email) => ['emailAddress' => ['address' => trim($email)]],
                explode(',', $to)
            ),
        ];

        if ($comment !== '') {
            $payload['comment'] = $comment;
        }

        $graph->createRequest('POST', "/me/messages/{$messageId}/forward")
            ->attachBody($payload)
            ->execute();

        return array_merge($context, [
            $storeOutputAs => ['message_id' => $messageId, 'forwarded_to' => $to],
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
