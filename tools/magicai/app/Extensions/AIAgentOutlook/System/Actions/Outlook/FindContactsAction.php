<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class FindContactsAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - query           (string) : OData $search or $filter expression (optional)
     *   - max_results     (int)    : max contacts (default: 10, max: 50)
     *   - return_format   (string) : id | name | email | full_as_string
     *   - store_output_as (string) : context key (default: found_contacts)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'found_contacts';
        $returnFormat = $config['return_format'] ?? 'id';
        $query = $this->interpolate($config['query'] ?? '', $context);
        $maxResults = min((int) ($config['max_results'] ?? 10), 50);

        $graph = $this->factory->make($workflow->user_id);

        $params = [
            '$top'    => $maxResults,
            '$select' => 'id,displayName,givenName,surname,emailAddresses,mobilePhone,companyName,jobTitle',
        ];

        if ($query !== '') {
            $params['$search'] = $query;
        }

        $endpoint = '/me/contacts?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $response = $graph->createRequest('GET', $endpoint)->execute();
        $contacts = $response->getProperties()['value'] ?? [];

        $output = match ($returnFormat) {
            'id'             => implode(', ', array_column($contacts, 'id')),
            'name'           => implode("\n", array_column($contacts, 'displayName')),
            'email'          => implode("\n", array_map(fn ($c) => data_get($c, 'emailAddresses.0.address', ''), $contacts)),
            'full_as_string' => $this->formatFull($contacts),
            default          => implode(', ', array_column($contacts, 'id')),
        };

        return array_merge($context, [$storeOutputAs => $output]);
    }

    private function formatFull(array $contacts): string
    {
        $lines = [];
        $i = 1;

        foreach ($contacts as $contact) {
            $lines[] = implode("\n", array_filter([
                "{$i}.",
                'ID: ' . ($contact['id'] ?? ''),
                'Name: ' . ($contact['displayName'] ?? ''),
                'Email: ' . data_get($contact, 'emailAddresses.0.address', ''),
                'Phone: ' . ($contact['mobilePhone'] ?? ''),
                'Company: ' . ($contact['companyName'] ?? ''),
                'Title: ' . ($contact['jobTitle'] ?? ''),
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
