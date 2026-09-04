<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions;

use App\Extensions\AIAgent\System\Actions\Contracts\AIAgentActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\AddAttendeesToCalendarEventAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\CopyEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\CreateContactAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\CreateDraftEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\CreateDraftReplyAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\CreateEventAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\CreateFolderAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\DeleteContactAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\DeleteEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\FindCalendarEventsAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\FindContactsAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\FindEmailsAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\FlagUnflagEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\ForwardEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\MarkEmailAsReadUnreadAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\MoveEmailToFolderAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\RemoveCategoriesFromEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\ReplyToEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\SendDraftEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\SendEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\SetCategoriesOnEmailAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\SetEmailImportanceAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\UpdateCalendarEventAction;
use App\Extensions\AIAgentOutlook\System\Actions\Outlook\UpdateContactAction;
use Exception;

class OutlookAction implements AIAgentActionInterface
{
    public function __construct(
        private readonly AddAttendeesToCalendarEventAction $addAttendeesToCalendarEventAction,
        private readonly CopyEmailAction $copyEmailAction,
        private readonly CreateContactAction $createContactAction,
        private readonly CreateDraftEmailAction $createDraftEmailAction,
        private readonly CreateDraftReplyAction $createDraftReplyAction,
        private readonly CreateEventAction $createEventAction,
        private readonly CreateFolderAction $createFolderAction,
        private readonly DeleteContactAction $deleteContactAction,
        private readonly DeleteEmailAction $deleteEmailAction,
        private readonly FindCalendarEventsAction $findCalendarEventsAction,
        private readonly FindContactsAction $findContactsAction,
        private readonly FindEmailsAction $findEmailsAction,
        private readonly FlagUnflagEmailAction $flagUnflagEmailAction,
        private readonly ForwardEmailAction $forwardEmailAction,
        private readonly MarkEmailAsReadUnreadAction $markEmailAsReadUnreadAction,
        private readonly MoveEmailToFolderAction $moveEmailToFolderAction,
        private readonly RemoveCategoriesFromEmailAction $removeCategoriesFromEmailAction,
        private readonly ReplyToEmailAction $replyToEmailAction,
        private readonly SendDraftEmailAction $sendDraftEmailAction,
        private readonly SendEmailAction $sendEmailAction,
        private readonly SetCategoriesOnEmailAction $setCategoriesOnEmailAction,
        private readonly SetEmailImportanceAction $setEmailImportanceAction,
        private readonly UpdateCalendarEventAction $updateCalendarEventAction,
        private readonly UpdateContactAction $updateContactAction,
    ) {}

    /**
     * Config keys:
     *   - action_event (string) : sub-operation to perform (required)
     *
     * Per action_event keys:
     *   send_email:                          to, subject, body, cc (optional), store_output_as
     *   create_draft_email:                  to, subject, body, cc (optional), store_output_as
     *   send_draft_email:                    message_id, store_output_as
     *   create_draft_reply:                  message_id, body (optional), store_output_as
     *   reply_to_email:                      message_id, body, store_output_as
     *   forward_email:                       message_id, to, comment (optional), store_output_as
     *   copy_email:                          message_id, destination_id, store_output_as
     *   move_email_to_folder:                message_id, destination_id, store_output_as
     *   delete_email:                        message_id, store_output_as
     *   mark_email_as_read_unread:           message_id, is_read, store_output_as
     *   flag_unflag_email:                   message_id, flag_status, store_output_as
     *   set_email_importance:                message_id, importance, store_output_as
     *   set_categories_on_email:             message_id, categories, store_output_as
     *   remove_categories_from_email:        message_id, store_output_as
     *   find_emails:                         query, max_results, return_format, store_output_as
     *   create_folder:                       display_name, store_output_as
     *   create_event:                        subject, start, end, timezone, body, location, attendees, is_online, store_output_as
     *   update_calendar_event:               event_id, subject, start, end, timezone, body, location, store_output_as
     *   add_attendees_to_calendar_event:     event_id, attendees, attendee_type, store_output_as
     *   find_calendar_events:                query, start_datetime, end_datetime, max_results, return_format, store_output_as
     *   create_contact:                      given_name, surname, email, phone, company, job_title, store_output_as
     *   update_contact:                      contact_id, given_name, surname, email, phone, company, job_title, store_output_as
     *   delete_contact:                      contact_id, store_output_as
     *   find_contacts:                       query, max_results, return_format, store_output_as
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $actionEvent = $config['action_event'] ?? '';

        return match ($actionEvent) {
            'add_attendees_to_calendar_event' => $this->addAttendeesToCalendarEventAction->execute($config, $context, $workflow, $run),
            'copy_email'                      => $this->copyEmailAction->execute($config, $context, $workflow, $run),
            'create_contact'                  => $this->createContactAction->execute($config, $context, $workflow, $run),
            'create_draft_email'              => $this->createDraftEmailAction->execute($config, $context, $workflow, $run),
            'create_draft_reply'              => $this->createDraftReplyAction->execute($config, $context, $workflow, $run),
            'create_event'                    => $this->createEventAction->execute($config, $context, $workflow, $run),
            'create_folder'                   => $this->createFolderAction->execute($config, $context, $workflow, $run),
            'delete_contact'                  => $this->deleteContactAction->execute($config, $context, $workflow, $run),
            'delete_email'                    => $this->deleteEmailAction->execute($config, $context, $workflow, $run),
            'find_calendar_events'            => $this->findCalendarEventsAction->execute($config, $context, $workflow, $run),
            'find_contacts'                   => $this->findContactsAction->execute($config, $context, $workflow, $run),
            'find_emails'                     => $this->findEmailsAction->execute($config, $context, $workflow, $run),
            'flag_unflag_email'               => $this->flagUnflagEmailAction->execute($config, $context, $workflow, $run),
            'forward_email'                   => $this->forwardEmailAction->execute($config, $context, $workflow, $run),
            'mark_email_as_read_unread'       => $this->markEmailAsReadUnreadAction->execute($config, $context, $workflow, $run),
            'move_email_to_folder'            => $this->moveEmailToFolderAction->execute($config, $context, $workflow, $run),
            'remove_categories_from_email'    => $this->removeCategoriesFromEmailAction->execute($config, $context, $workflow, $run),
            'reply_to_email'                  => $this->replyToEmailAction->execute($config, $context, $workflow, $run),
            'send_draft_email'                => $this->sendDraftEmailAction->execute($config, $context, $workflow, $run),
            'send_email'                      => $this->sendEmailAction->execute($config, $context, $workflow, $run),
            'set_categories_on_email'         => $this->setCategoriesOnEmailAction->execute($config, $context, $workflow, $run),
            'set_email_importance'            => $this->setEmailImportanceAction->execute($config, $context, $workflow, $run),
            'update_calendar_event'           => $this->updateCalendarEventAction->execute($config, $context, $workflow, $run),
            'update_contact'                  => $this->updateContactAction->execute($config, $context, $workflow, $run),
            default                           => throw new Exception('OutlookAction: unknown action_event "' . $actionEvent . '".'),
        };
    }

    public function getCategory(): string
    {
        return 'utilities';
    }

    public function getLabel(): string
    {
        return 'Outlook';
    }

    public function getDescription(): string
    {
        return 'Manage your Outlook: send emails, manage calendar events, contacts, and more.';
    }

    public function getIcon(): string
    {
        return 'tabler-brand-office';
    }

    public function getConfigSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['action_event'],
            'properties' => [
                'action_event' => [
                    'type'        => 'string',
                    'enum'        => [
                        'add_attendees_to_calendar_event',
                        'copy_email',
                        'create_contact',
                        'create_draft_email',
                        'create_draft_reply',
                        'create_event',
                        'create_folder',
                        'delete_contact',
                        'delete_email',
                        'find_calendar_events',
                        'find_contacts',
                        'find_emails',
                        'flag_unflag_email',
                        'forward_email',
                        'mark_email_as_read_unread',
                        'move_email_to_folder',
                        'remove_categories_from_email',
                        'reply_to_email',
                        'send_draft_email',
                        'send_email',
                        'set_categories_on_email',
                        'set_email_importance',
                        'update_calendar_event',
                        'update_contact',
                    ],
                    'description' => 'The Outlook operation to perform.',
                ],
                'message_id'        => ['type' => 'string', 'description' => 'Outlook message ID.'],
                'event_id'          => ['type' => 'string', 'description' => 'Calendar event ID.'],
                'contact_id'        => ['type' => 'string', 'description' => 'Contact ID.'],
                'to'                => ['type' => 'string', 'description' => 'Recipient email address(es), comma-separated.'],
                'cc'                => ['type' => 'string', 'description' => 'CC email address(es), comma-separated.'],
                'subject'           => ['type' => 'string', 'description' => 'Email or event subject.'],
                'body'              => ['type' => 'string', 'description' => 'Email body or event description.'],
                'comment'           => ['type' => 'string', 'description' => 'Comment for forward_email.'],
                'destination_id'    => ['type' => 'string', 'description' => 'Destination folder ID or well-known name (copy_email, move_email_to_folder).'],
                'display_name'      => ['type' => 'string', 'description' => 'Folder display name (create_folder).'],
                'is_read'           => ['type' => 'boolean', 'description' => 'Mark email read (true) or unread (false).'],
                'flag_status'       => ['type' => 'string', 'enum' => ['notFlagged', 'flagged', 'complete'], 'description' => 'Follow-up flag status.'],
                'importance'        => ['type' => 'string', 'enum' => ['low', 'normal', 'high'], 'description' => 'Email importance level.'],
                'categories'        => ['type' => 'string', 'description' => 'Comma-separated category names.'],
                'query'             => ['type' => 'string', 'description' => 'OData search/filter expression.'],
                'max_results'       => ['type' => 'integer', 'description' => 'Max results to return (default: 10).'],
                'return_format'     => ['type' => 'string', 'enum' => ['id', 'subject', 'sender', 'date', 'body', 'full_as_string', 'name', 'email'], 'description' => 'Output format.'],
                'start'             => ['type' => 'string', 'description' => 'ISO 8601 start datetime (create_event, update_calendar_event).'],
                'end'               => ['type' => 'string', 'description' => 'ISO 8601 end datetime (create_event, update_calendar_event).'],
                'start_datetime'    => ['type' => 'string', 'description' => 'ISO 8601 range start for find_calendar_events.'],
                'end_datetime'      => ['type' => 'string', 'description' => 'ISO 8601 range end for find_calendar_events.'],
                'timezone'          => ['type' => 'string', 'description' => 'Timezone name e.g. UTC, Europe/London (default: UTC).'],
                'location'          => ['type' => 'string', 'description' => 'Event location name.'],
                'attendees'         => ['type' => 'string', 'description' => 'Comma-separated attendee emails.'],
                'attendee_type'     => ['type' => 'string', 'enum' => ['required', 'optional', 'resource'], 'description' => 'Attendee type (default: required).'],
                'is_online'         => ['type' => 'boolean', 'description' => 'Create as Teams meeting (create_event).'],
                'given_name'        => ['type' => 'string', 'description' => 'Contact first name.'],
                'surname'           => ['type' => 'string', 'description' => 'Contact last name.'],
                'email'             => ['type' => 'string', 'description' => 'Contact email address.'],
                'phone'             => ['type' => 'string', 'description' => 'Contact mobile phone.'],
                'company'           => ['type' => 'string', 'description' => 'Contact company name.'],
                'job_title'         => ['type' => 'string', 'description' => 'Contact job title.'],
                'store_output_as'   => ['type' => 'string', 'description' => 'Context key to store results under.'],
            ],
        ];
    }
}
