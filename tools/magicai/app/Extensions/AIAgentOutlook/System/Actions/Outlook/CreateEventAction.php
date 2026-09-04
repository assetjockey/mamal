<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class CreateEventAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - subject         (string) : event subject (required)
     *   - start           (string) : ISO 8601 start datetime (required)
     *   - end             (string) : ISO 8601 end datetime (required)
     *   - timezone        (string) : timezone name e.g. UTC (default: UTC)
     *   - body            (string) : event description (optional)
     *   - location        (string) : location name (optional)
     *   - attendees       (string) : comma-separated attendee emails (optional)
     *   - is_online       (bool)   : create as Teams meeting (optional)
     *   - store_output_as (string) : context key (default: created_event)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'created_event';
        $timezone = $config['timezone'] ?? 'UTC';

        $graph = $this->factory->make($workflow->user_id);

        $payload = [
            'subject' => $this->interpolate($config['subject'] ?? '', $context),
            'start'   => [
                'dateTime' => $this->interpolate($config['start'] ?? '', $context),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $this->interpolate($config['end'] ?? '', $context),
                'timeZone' => $timezone,
            ],
        ];

        $body = $this->interpolate($config['body'] ?? '', $context);

        if ($body !== '') {
            $payload['body'] = ['contentType' => 'Text', 'content' => $body];
        }

        $location = $this->interpolate($config['location'] ?? '', $context);

        if ($location !== '') {
            $payload['location'] = ['displayName' => $location];
        }

        $attendeesRaw = $this->interpolate($config['attendees'] ?? '', $context);

        if ($attendeesRaw !== '') {
            $payload['attendees'] = array_map(
                fn (string $email) => [
                    'emailAddress' => ['address' => trim($email)],
                    'type'         => 'required',
                ],
                explode(',', $attendeesRaw)
            );
        }

        if (filter_var($config['is_online'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $payload['isOnlineMeeting'] = true;
            $payload['onlineMeetingProvider'] = 'teamsForBusiness';
        }

        $event = $graph->createRequest('POST', '/me/events')
            ->attachBody($payload)
            ->execute();

        $props = $event->getProperties();

        return array_merge($context, [
            $storeOutputAs => [
                'event_id'   => $props['id'] ?? '',
                'subject'    => $props['subject'] ?? '',
                'web_link'   => $props['webLink'] ?? '',
                'join_url'   => data_get($props, 'onlineMeeting.joinUrl', ''),
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
