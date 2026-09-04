<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class UpdateCalendarEventAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - event_id        (string) : event ID to update (required)
     *   - subject         (string) : new subject (optional)
     *   - start           (string) : ISO 8601 start datetime (optional)
     *   - end             (string) : ISO 8601 end datetime (optional)
     *   - timezone        (string) : timezone name (default: UTC)
     *   - body            (string) : new event description (optional)
     *   - location        (string) : new location name (optional)
     *   - store_output_as (string) : context key (default: updated_event)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'updated_event';
        $eventId = $this->interpolate($config['event_id'] ?? '', $context);
        $timezone = $config['timezone'] ?? 'UTC';

        $graph = $this->factory->make($workflow->user_id);
        $payload = [];

        $subject = $this->interpolate($config['subject'] ?? '', $context);
        if ($subject !== '') {
            $payload['subject'] = $subject;
        }

        $start = $this->interpolate($config['start'] ?? '', $context);
        if ($start !== '') {
            $payload['start'] = ['dateTime' => $start, 'timeZone' => $timezone];
        }

        $end = $this->interpolate($config['end'] ?? '', $context);
        if ($end !== '') {
            $payload['end'] = ['dateTime' => $end, 'timeZone' => $timezone];
        }

        $body = $this->interpolate($config['body'] ?? '', $context);
        if ($body !== '') {
            $payload['body'] = ['contentType' => 'Text', 'content' => $body];
        }

        $location = $this->interpolate($config['location'] ?? '', $context);
        if ($location !== '') {
            $payload['location'] = ['displayName' => $location];
        }

        $graph->createRequest('PATCH', "/me/events/{$eventId}")
            ->attachBody($payload)
            ->execute();

        return array_merge($context, [
            $storeOutputAs => ['event_id' => $eventId, 'updated' => true],
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
