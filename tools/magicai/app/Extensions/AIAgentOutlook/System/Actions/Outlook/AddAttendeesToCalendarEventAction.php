<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class AddAttendeesToCalendarEventAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - event_id        (string) : event ID to update (required)
     *   - attendees       (string) : comma-separated attendee emails to add (required)
     *   - attendee_type   (string) : required | optional | resource (default: required)
     *   - store_output_as (string) : context key (default: add_attendees_result)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'add_attendees_result';
        $eventId = $this->interpolate($config['event_id'] ?? '', $context);
        $attendeesRaw = $this->interpolate($config['attendees'] ?? '', $context);
        $attendeeType = $config['attendee_type'] ?? 'required';

        $graph = $this->factory->make($workflow->user_id);

        // Fetch existing attendees to merge
        $existing = $graph->createRequest('GET', "/me/events/{$eventId}?\$select=attendees")
            ->execute();

        $existingAttendees = $existing->getProperties()['attendees'] ?? [];

        $newAttendees = array_map(
            fn (string $email) => [
                'emailAddress' => ['address' => trim($email)],
                'type'         => $attendeeType,
            ],
            array_filter(explode(',', $attendeesRaw), fn ($e) => trim($e) !== '')
        );

        $merged = array_merge($existingAttendees, $newAttendees);

        $graph->createRequest('PATCH', "/me/events/{$eventId}")
            ->attachBody(['attendees' => $merged])
            ->execute();

        return array_merge($context, [
            $storeOutputAs => [
                'event_id'      => $eventId,
                'added'         => count($newAttendees),
                'total'         => count($merged),
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
