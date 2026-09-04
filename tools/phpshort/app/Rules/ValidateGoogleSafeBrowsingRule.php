<?php

namespace App\Rules;

use Closure;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateGoogleSafeBrowsingRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (config('settings.gsb') && config('settings.gsb_key')) {
            $urls = preg_split('/[\r\n]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

            $data = [];
            foreach ($urls as $url) {
                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                    $data[] = ['url' => $url];
                }
            }

            if (!empty($data)) {
                $httpClient = new GuzzleClient();

                try {
                    $gsbRequest = $httpClient->request('POST', 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . config('settings.gsb_key'), [
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                        'body' => json_encode([
                            'client' => [
                                'clientId' => mb_strtolower(config('settings.title')),
                                'clientVersion' => config('info.software.version'),
                            ],
                            'threatInfo' => [
                                'threatTypes' => [
                                    'MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE', 'POTENTIALLY_HARMFUL_APPLICATION'
                                ],
                                'platformTypes' => [
                                    'ALL_PLATFORMS',
                                ],
                                'threatEntryTypes' => [
                                    'URL', 'EXECUTABLE'
                                ],
                                'threatEntries' => [
                                    $data
                                ],
                            ],
                        ])
                    ]);

                    $response = json_decode($gsbRequest->getBody()->getContents(), true);
                } catch (Exception $e) {
                    $fail(__($e->getResponse()->getBody()->getContents()));
                }

                if (!empty($response)) {
                    $fail(__('This link has been banned.'));
                }
            }
        }
    }
}
