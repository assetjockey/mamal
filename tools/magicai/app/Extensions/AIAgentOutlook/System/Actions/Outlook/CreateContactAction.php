<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class CreateContactAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - given_name      (string) : first name (required)
     *   - surname         (string) : last name (optional)
     *   - email           (string) : email address (optional)
     *   - phone           (string) : mobile phone number (optional)
     *   - company         (string) : company name (optional)
     *   - job_title       (string) : job title (optional)
     *   - store_output_as (string) : context key (default: created_contact)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'created_contact';

        $graph = $this->factory->make($workflow->user_id);

        $payload = array_filter([
            'givenName'    => $this->interpolate($config['given_name'] ?? '', $context),
            'surname'      => $this->interpolate($config['surname'] ?? '', $context),
            'companyName'  => $this->interpolate($config['company'] ?? '', $context),
            'jobTitle'     => $this->interpolate($config['job_title'] ?? '', $context),
        ], fn ($v) => $v !== '');

        $email = $this->interpolate($config['email'] ?? '', $context);
        if ($email !== '') {
            $payload['emailAddresses'] = [['address' => $email, 'name' => ($payload['givenName'] ?? '') . ' ' . ($payload['surname'] ?? '')]];
        }

        $phone = $this->interpolate($config['phone'] ?? '', $context);
        if ($phone !== '') {
            $payload['mobilePhone'] = $phone;
        }

        $contact = $graph->createRequest('POST', '/me/contacts')
            ->attachBody($payload)
            ->execute();

        $props = $contact->getProperties();

        return array_merge($context, [
            $storeOutputAs => [
                'contact_id'  => $props['id'] ?? '',
                'given_name'  => $props['givenName'] ?? '',
                'surname'     => $props['surname'] ?? '',
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
