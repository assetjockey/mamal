<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class FindCalendarEventsAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - query           (string) : OData $filter or $search expression (optional)
     *   - start_datetime  (string) : ISO 8601 range start (optional)
     *   - end_datetime    (string) : ISO 8601 range end (optional)
     *   - max_results     (int)    : max events (default: 10, max: 50)
     *   - return_format   (string) : id | subject | full_as_string
     *   - store_output_as (string) : context key (default: found_events)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'found_events';
        $returnFormat = $config['return_format'] ?? 'id';
        $maxResults = min((int) ($config['max_results'] ?? 10), 50);

        $graph = $this->factory->make($workflow->user_id);

        $params = [
            '$top'     => $maxResults,
            '$select'  => 'id,subject,start,end,location,webLink,bodyPreview',
            '$orderby' => 'start/dateTime asc',
        ];

        $filter = [];

        $start = $this->interpolate($config['start_datetime'] ?? '', $context);
        $end = $this->interpolate($config['end_datetime'] ?? '', $context);

        if ($start !== '') {
            $filter[] = "start/dateTime ge '{$start}'";
        }

        if ($end !== '') {
            $filter[] = "end/dateTime le '{$end}'";
        }

        if ($filter !== []) {
            $params['$filter'] = implode(' and ', $filter);
        }

        $query = $this->interpolate($config['query'] ?? '', $context);
        if ($query !== '') {
            $params['$search'] = $query;
        }

        $endpoint = '/me/events?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $response = $graph->createRequest('GET', $endpoint)->execute();
        $events = $response->getProperties()['value'] ?? [];

        $output = match ($returnFormat) {
            'id'             => implode(', ', array_column($events, 'id')),
            'subject'        => implode("\n", array_column($events, 'subject')),
            'full_as_string' => $this->formatFull($events),
            default          => implode(', ', array_column($events, 'id')),
        };

        return array_merge($context, [$storeOutputAs => $output]);
    }

    private function formatFull(array $events): string
    {
        $lines = [];
        $i = 1;

        foreach ($events as $event) {
            $lines[] = implode("\n", array_filter([
                "{$i}.",
                'ID: ' . ($event['id'] ?? ''),
                'Subject: ' . ($event['subject'] ?? ''),
                'Start: ' . data_get($event, 'start.dateTime', ''),
                'End: ' . data_get($event, 'end.dateTime', ''),
                'Location: ' . data_get($event, 'location.displayName', ''),
                'Snippet: ' . ($event['bodyPreview'] ?? ''),
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
