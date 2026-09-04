<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;
use Microsoft\Graph\Model\Message;

class CreateDraftEmailAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - to              (string) : recipient email address (required)
     *   - subject         (string) : email subject (required)
     *   - body            (string) : email body (required)
     *   - cc              (string) : CC email addresses, comma-separated (optional)
     *   - store_output_as (string) : context key (default: draft_email)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'draft_email';

        $graph = $this->factory->make($workflow->user_id);

        $to = $this->interpolate($config['to'] ?? '', $context);
        $subject = $this->interpolate($config['subject'] ?? '', $context);
        $body = $this->interpolate($config['body'] ?? '', $context);
        $cc = $this->interpolate($config['cc'] ?? '', $context);

        $payload = [
            'subject' => $subject,
            'body'    => [
                'contentType' => 'Text',
                'content'     => $body,
            ],
            'toRecipients' => $this->buildRecipients($to),
        ];

        if ($cc !== '') {
            $payload['ccRecipients'] = $this->buildRecipients($cc);
        }

        $message = $graph->createRequest('POST', '/me/messages')
            ->attachBody($payload)
            ->setReturnType(Message::class)
            ->execute();

        return array_merge($context, [
            $storeOutputAs => [
                'message_id' => $message->getId(),
                'subject'    => $subject,
            ],
        ]);
    }

    private function buildRecipients(string $addresses): array
    {
        return array_map(
            fn (string $email) => ['emailAddress' => ['address' => trim($email)]],
            explode(',', $addresses)
        );
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
