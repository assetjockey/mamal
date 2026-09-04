<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class FindEmailsAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - query           (string) : OData $search or $filter expression (required)
     *   - max_results     (int)    : max messages to return (default: 10, max: 50)
     *   - return_format   (string) : id | subject | sender | date | body | full_as_string
     *   - store_output_as (string) : context key (default: found_emails)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'found_emails';
        $returnFormat = $config['return_format'] ?? 'id';
        $query = $this->interpolate($config['query'] ?? '', $context);
        $maxResults = min((int) ($config['max_results'] ?? 10), 50);

        $graph = $this->factory->make($workflow->user_id);

        $endpoint = '/me/messages?' . http_build_query([
            '$search'  => $query !== '' ? $query : null,
            '$top'     => $maxResults,
            '$select'  => 'id,subject,from,receivedDateTime,bodyPreview,body',
            '$orderby' => 'receivedDateTime desc',
        ], '', '&', PHP_QUERY_RFC3986);

        $response = $graph->createRequest('GET', $endpoint)
            ->execute();

        $messages = $response->getProperties()['value'] ?? [];

        $output = match ($returnFormat) {
            'id'             => implode(', ', array_column($messages, 'id')),
            'subject'        => implode("\n", array_column($messages, 'subject')),
            'sender'         => implode("\n", array_map(fn ($m) => data_get($m, 'from.emailAddress.address', ''), $messages)),
            'date'           => implode("\n", array_column($messages, 'receivedDateTime')),
            'body'           => implode("\n---\n", array_map(fn ($m) => data_get($m, 'body.content', ''), $messages)),
            'full_as_string' => $this->formatFull($messages),
            default          => implode(', ', array_column($messages, 'id')),
        };

        return array_merge($context, [$storeOutputAs => $output]);
    }

    private function formatFull(array $messages): string
    {
        $lines = [];
        $i = 1;

        foreach ($messages as $message) {
            $lines[] = implode("\n", array_filter([
                "{$i}.",
                'ID: ' . ($message['id'] ?? ''),
                'Subject: ' . ($message['subject'] ?? ''),
                'From: ' . data_get($message, 'from.emailAddress.address', ''),
                'Date: ' . ($message['receivedDateTime'] ?? ''),
                'Snippet: ' . ($message['bodyPreview'] ?? ''),
            ]));
            $i++;
        }

        return implode("\n\n", $lines);
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
