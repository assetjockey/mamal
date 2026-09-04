<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Actions\Outlook;

use App\Extensions\AIAgent\System\Actions\Contracts\ActionInterface;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflow;
use App\Extensions\AIAgent\System\Models\AIAgentWorkflowRun;
use App\Extensions\AIAgentOutlook\System\Graph\GraphClientFactory;

class CreateFolderAction implements ActionInterface
{
    public function __construct(private readonly GraphClientFactory $factory) {}

    /**
     * Config keys:
     *   - display_name    (string) : folder display name (required)
     *   - store_output_as (string) : context key (default: created_folder)
     */
    public function execute(array $config, array $context, AIAgentWorkflow $workflow, AIAgentWorkflowRun $run): array
    {
        $storeOutputAs = $config['store_output_as'] ?? 'created_folder';
        $displayName = $this->interpolate($config['display_name'] ?? '', $context);

        $graph = $this->factory->make($workflow->user_id);

        $folder = $graph->createRequest('POST', '/me/mailFolders')
            ->attachBody(['displayName' => $displayName])
            ->execute();

        $props = $folder->getProperties();

        return array_merge($context, [
            $storeOutputAs => [
                'folder_id'    => $props['id'] ?? '',
                'display_name' => $props['displayName'] ?? $displayName,
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
