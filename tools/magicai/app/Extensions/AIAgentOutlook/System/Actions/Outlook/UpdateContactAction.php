<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class UpdateContactAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - contact_id      (string) : contact ID to update (required)
     *   - given_name      (string) : first name (optional)
     *   - surname         (string) : last name (optional)
     *   - email           (string) : email address (optional)
     *   - phone           (string) : mobile phone number (optional)
     *   - company         (string) : company name (optional)
     *   - job_title       (string) : job title (optional)
     *   - store_output_as (string) : context key (default: updated_contact)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'updated_contact';
        $contactId = $this->interpolate($config['contact_id'] ?? '', $context);

        $graph = $this->factory->make($workflow->user_id);
        $payload = [];

        $givenName = $this->interpolate($config['given_name'] ?? '', $context);
        if ($givenName !== '') {
            $payload['givenName'] = $givenName;
        }

        $surname = $this->interpolate($config['surname'] ?? '', $context);
        if ($surname !== '') {
            $payload['surname'] = $surname;
        }

        $company = $this->interpolate($config['company'] ?? '', $context);
        if ($company !== '') {
            $payload['companyName'] = $company;
        }

        $jobTitle = $this->interpolate($config['job_title'] ?? '', $context);
        if ($jobTitle !== '') {
            $payload['jobTitle'] = $jobTitle;
        }

        $email = $this->interpolate($config['email'] ?? '', $context);
        if ($email !== '') {
            $payload['emailAddresses'] = [['address' => $email]];
        }

        $phone = $this->interpolate($config['phone'] ?? '', $context);
        if ($phone !== '') {
            $payload['mobilePhone'] = $phone;
        }

        $graph->createRequest('PATCH', "/me/contacts/{$contactId}")
            ->attachBody($payload)
            ->execute();

        return array_merge($context, [
            $storeOutputAs => ['contact_id' => $contactId, 'updated' => true],
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
