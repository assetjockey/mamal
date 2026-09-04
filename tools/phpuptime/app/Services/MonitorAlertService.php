<?php

namespace App\Services;

use App\Mail\MonitorStatusMail;
use App\Models\Monitor;
use App\Models\User;
use Carbon\CarbonInterface;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MonitorAlertService
{
    /**
     * Process the monitor alerts.
     */
    public function process(array $monitors): void
    {
        $this->sendWebhookAlerts($monitors);
        $this->sendSmsAlerts($monitors);
        $this->sendEmailAlerts($monitors);
    }

    /**
     * Send webhook alerts.
     */
    private function sendWebhookAlerts(array $monitors): void
    {
        $webhookAlerts = [];

        // Handle webhook alerts
        foreach ($monitors as $monitor) {
            $monitor = (object) $monitor;

            if (!isset($monitor->alert)) {
                continue;
            }

            foreach ($monitor->alerts as $alert) {
                if ($monitor->user->cannot('webhookAlerts', [User::class])) {
                    break;
                }

                if ($alert->key == 'slack' && $monitor->user->can('webhookAlerts', [User::class])) {
                    $webhookAlerts[] = [
                        'url' => $alert->value,
                        'payload' => [
                            'attachments' => [
                                [
                                    'title' => $this->getAlertGreeting($monitor) . $this->getAlertMessage($monitor),
                                    'color' => ($monitor->alert->error ? '#dc3545' : '#28a745'),
                                    'text' => __('More details at :url.', ['url' => '<' . route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token]) . '>'], $monitor->user->locale),
                                ]
                            ]
                        ]
                    ];
                } elseif ($alert->key == 'teams' && $monitor->user->can('webhookAlerts', [User::class])) {
                    $webhookAlerts[] = [
                        'url' => $alert->value,
                        'payload' => [
                            'text' => '<strong style="color: #' . ($monitor->alert->error ? 'dc3545' : '28a745') . ';">' . $this->getAlertGreeting($monitor) . '</strong> ' .
                                $this->getAlertMessage($monitor) . ' ' . __('More details at :url.', ['url' => '<' . route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token]) . '>'], $monitor->user->locale)
                        ]
                    ];
                } elseif ($alert->key == 'discord' && $monitor->user->can('webhookAlerts', [User::class])) {
                    $webhookAlerts[] = [
                        'url' => $alert->value,
                        'payload' => [
                            'embeds' => [
                                [
                                    'type' => 'rich',
                                    'title' => $this->getAlertGreeting($monitor) . $this->getAlertMessage($monitor),
                                    'description' => __('More details at :url.', ['url' => '<' . route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token]) . '>'], $monitor->user->locale),
                                    'color' => hexdec(($monitor->alert->error ? 'dc3545' : '28a745')),
                                ]
                            ]
                        ]
                    ];
                } elseif ($alert->key == 'flock' && $monitor->user->can('webhookAlerts', [User::class])) {
                    $webhookAlerts[] = [
                        'url' => $alert->value,
                        'payload' => [
                            'flockml' => '<flockml><strong style="color: #' . ($monitor->alert->error ? 'dc3545' : '28a745') . ';">' . $this->getAlertGreeting($monitor) . '</strong> ' .
                                $this->getAlertMessage($monitor) . ' ' . __('More details at :url.', ['url' => '<a href="' . route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token]) . '">' . route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token]) . '</a>'], $monitor->user->locale) . '</flockml>'
                        ]
                    ];
                } elseif ($alert->key == 'telegram' && $monitor->user->can('webhookAlerts', [User::class])) {
                    $webhookAlerts[] = [
                        'url' => 'https://api.telegram.org/bot' .  (explode(' ', $alert->value)[0] ?? null) . '/sendMessage',
                        'payload' => [
                            'chat_id' => explode(' ', $alert->value)[1] ?? null,
                            'text' => $this->getAlertGreeting($monitor) . $this->getAlertMessage($monitor) . ' ' . route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token])
                        ]
                    ];
                } elseif ($alert->key == 'webhook' && $monitor->user->can('webhookAlerts', [User::class])) {
                    $webhookAlerts[] = [
                        'url' => $alert->value,
                        'payload' => $this->getWebhookPayload($monitor),
                    ];
                }
            }
        }

        $httpClient = new GuzzleClient();
        $webhookAlertsChunks = array_chunk($webhookAlerts, config('settings.request_simultaneous_requests'));

        foreach ($webhookAlertsChunks as $webhookAlertsChunk) {
            $webhookAlertPromises = [];

            foreach ($webhookAlertsChunk as $webhookAlert) {
                // Create async requests
                $webhookAlertPromises[] = $httpClient->requestAsync('POST', $webhookAlert['url'], [
                    'body' => json_encode($webhookAlert['payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'version' => config('settings.request_http_version'),
                    'http_errors' => false,
                    'verify' => false,
                    'proxy' => [
                        'http' => getRequestProxy(),
                        'https' => getRequestProxy()
                    ],
                    'timeout' => config('settings.request_timeout'),
                    'allow_redirects' => [
                        'max'             => 6,
                        'strict'          => true,
                        'referer'         => true,
                        'protocols'       => ['http', 'https'],
                        'track_redirects' => true
                    ],
                    'headers' => [
                        'User-Agent' => config('settings.request_user_agent'),
                        'Content-Type' => 'application/json'
                    ]
                ]);
            }

            // Execute all requests asynchronously
            Utils::settle($webhookAlertPromises)->wait();
        }
    }

    /**
     * Send the sms alerts.
     */
    private function sendSmsAlerts(array $monitors): void
    {
        if (!config('settings.twilio')) {
            return;
        }

        foreach ($monitors as $monitor) {
            $monitor = (object) $monitor;

            if (!isset($monitor->alert)) {
                continue;
            }

            $httpClient = new GuzzleClient();

            foreach ($monitor->alerts as $alert) {
                if ($monitor->user->cannot('smsAlerts', [User::class])) {
                    break;
                }

                if ($alert->key !== 'sms') {
                    continue;
                }

                $bodyMessage = $this->getAlertGreeting($monitor) . $this->getAlertMessage($monitor) . ' ' . (config('settings.twilio_sms_url') ? route('monitors.overview', ['id' => $monitor->id, 'token' => $monitor->token]) : __('Sent by :name', ['name' => formatTitle(config('settings.title'))], $monitor->user->locale));

                try {
                    $response = $httpClient->request('POST', 'https://api.twilio.com/2010-04-01/Accounts/' . config('settings.twilio_sid') . '/Messages.json', [
                        'auth' => [config('settings.twilio_sid'), config('settings.twilio_token')],
                        'form_params' => [
                            'From' => config('settings.twilio_phone_number'),
                            'To' => $alert->value,
                            'Body' => $bodyMessage,
                        ],
                    ]);
                } catch (Exception $e) {
                    logger()->info($e->getMessage());
                }
            }
        }
    }

    /**
     * Send the email alerts.
     */
    private function sendEmailAlerts(array $monitors): void
    {
        foreach ($monitors as $monitor) {
            $monitor = (object) $monitor;

            if (!isset($monitor->alert)) {
                continue;
            }

            foreach ($monitor->alerts as $alert) {
                if ($monitor->user->cannot('emailAlerts', [User::class])) {
                    break;
                }

                if ($alert->key !== 'email') {
                    continue;
                }

                try {
                    Mail::to($alert->value)->locale($monitor->user->locale)->send(new MonitorStatusMail($monitor, [
                        'subject' => $this->getAlertSubject($monitor),
                        'greeting' => $this->getAlertGreeting($monitor),
                        'message' => $this->getAlertMessage($monitor)
                    ]));
                } catch (Exception) {}
            }
        }
    }

    /**
     * Get the available monitor alerts based on the user's plan limits.
     */
    public function getMonitorAlertsUserPlanLimited(Monitor $monitor): Collection
    {
        // Select the webhook alerts based on the user's plan limits
        $webhookAlerts = collect($monitor->alerts)->filter(function ($alert) {
            return isset($alert->key) && $alert->key != 'email' && $alert->key != 'sms' && $alert->key != '';
        })
        ->take($monitor->user->active_plan->features->webhook_alerts);

        // Select the SMS alerts based on the user's plan limits
        $smsAlerts = collect($monitor->alerts)->filter(function ($alert) {
            return isset($alert->key) && $alert->key == 'sms';
        })
        ->take((config('settings.twilio') ? $monitor->user->active_plan->features->sms_alerts : 0));

        // Select the email alerts based on the user's plan limits
        $emailAlerts = collect($monitor->alerts)->filter(function ($alert) {
            return isset($alert->key) && $alert->key == 'email';
        })
        ->take($monitor->user->active_plan->features->email_alerts);

        return $webhookAlerts->union($smsAlerts)->union($emailAlerts);
    }

    /**
     * Get the alert greeting.
     */
    private function getAlertGreeting(object $monitor): string
    {
        if ($monitor->alert->error) {
            return __('Alert', [], $monitor->user->locale) . '! ';
        }

        return __('Good news', [], $monitor->user->locale) . '! ';
    }

    /**
     * Get the alert message.
     */
    private function getAlertMessage(object $monitor): ?string
    {
        if ($monitor->alert->type == 'http') {
            return __('The :name monitor is now :status.', ['name' => $monitor->name, 'status' => mb_strtolower(__(Str::ucfirst($monitor->status), [], $monitor->user->locale))], $monitor->user->locale);
        } elseif ($monitor->alert->type == 'ssl') {
            return __('The SSL certificate of the :name monitor is expiring in :timeframe.', ['name' => $monitor->name, 'timeframe' => $monitor->ssl_ends_at->locale($monitor->user->locale)->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE])], $monitor->user->locale);
        } elseif ($monitor->alert->type == 'domain') {
            return __('The domain name of the :name monitor is expiring in :timeframe.', ['name' => $monitor->name, 'timeframe' => $monitor->domain_ends_at->locale($monitor->user->locale)->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE])], $monitor->user->locale);
        }

        return null;
    }

    /**
     * Get the alert subject.
     */
    private function getAlertSubject(object $monitor): ?string
    {
        if ($monitor->alert->type == 'http') {
            return __(':name is :status', ['name' => $monitor->name, 'status' => mb_strtolower(__(Str::ucfirst($monitor->status), [], $monitor->user->locale))], $monitor->user->locale);
        } elseif ($monitor->alert->type == 'ssl') {
            return __(':name SSL is expiring soon', ['name' => $monitor->name], $monitor->user->locale);
        } elseif ($monitor->alert->type == 'domain') {
            return __(':name domain name is expiring soon', ['name' => $monitor->name], $monitor->user->locale);
        }

        return null;
    }

    /**
     * Get the webhook alert payload.
     */
    private function getWebhookPayload(object $monitor): ?array
    {
        if ($monitor->alert->type == 'http') {
            return [
                'name' => $monitor->name,
                'url' => $monitor->url,
                'status' => $monitor->status,
                'started_at' => $monitor->started_at,
                'token' => $monitor->token,
                'action' => $monitor->alert->type
            ];
        } elseif ($monitor->alert->type == 'ssl') {
            return [
                'name' => $monitor->name,
                'url' => $monitor->url,
                'ssl_checked_at' => $monitor->ssl_checked_at,
                'ssl_alerted_at' => $monitor->ssl_alerted_at,
                'ssl_created_at' => $monitor->ssl_created_at,
                'ssl_ends_at' => $monitor->ssl_ends_at,
                'token' => $monitor->token,
                'action' => $monitor->alert->type
            ];
        } elseif ($monitor->alert->type == 'domain') {
            return [
                'name' => $monitor->name,
                'url' => $monitor->url,
                'domain_checked_at' => $monitor->domain_checked_at,
                'domain_alerted_at' => $monitor->domain_alerted_at,
                'domain_created_at' => $monitor->domain_created_at,
                'domain_ends_at' => $monitor->domain_ends_at,
                'token' => $monitor->token,
                'action' => $monitor->alert->type
            ];
        }

        return null;
    }
}
