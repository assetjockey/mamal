<?php

namespace App\Services;

use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IncidentService
{
    /**
     * The monitor alert service instance.
     */
    public MonitorAlertService $monitorAlertService;

    /**
     * Create a new service instance.
     */
    public function __construct(MonitorAlertService $monitorAlertService)
    {
        $this->monitorAlertService = $monitorAlertService;
    }

    /**
     * Store the incident.
     */
    public function store(array $data): Incident
    {
        $incident = new Incident;

        $now = Carbon::now();

        $incident->user_id = Auth::user()->id;
        $incident->monitor_id = $data['monitor_id'];

        $monitor = $incident->monitor;
        if (in_array($monitor->status, ['online', 'offline'])) {
            $monitor->token = Str::random(16);
            $monitor->status = 'offline';
            $monitor->checked_at = $now;
            $monitor->started_at = $now;
            $monitor->save();
        }

        $incident->alerted = $this->monitorAlertService->getMonitorAlertsUserPlanLimited($monitor)->toArray();

        if (array_key_exists('url', $data)) {
            $incident->url = $data['url'];
        } else {
            $incident->url = $monitor->url;
        }

        if (array_key_exists('monitor_token', $data)) {
            $incident->monitor_token = $data['monitor_token'];
        } else {
            $incident->monitor_token = $monitor->token;
        }

        if (array_key_exists('country', $data)) {
            $incident->country = $data['country'];
        } else {
            $incident->country = null;
        }

        if (array_key_exists('city', $data)) {
            $incident->city = $data['city'];
        } else {
            $incident->city = null;
        }

        if (array_key_exists('check_ip', $data)) {
            $incident->check_ip = $data['check_ip'];
        } else {
            $incident->check_ip = null;
        }

        if (array_key_exists('token', $data)) {
            $incident->token = $data['token'];
        } else {
            $incident->token = Str::random(16);
        }

        $incident->started_at = Carbon::parse($data['started_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));

        if (array_key_exists('acknowledged_at', $data) && $data['acknowledged_at']) {
            $incident->acknowledged_at = Carbon::parse($data['acknowledged_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } else {
            $incident->acknowledged_at = null;
        }

        if (array_key_exists('ended_at', $data) && $data['ended_at']) {
            $incident->ended_at = Carbon::parse($data['ended_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } else {
            $incident->ended_at = null;

            if (in_array($monitor->status, ['online', 'offline'])) {
                $this->monitorAlertService->process([
                    $monitor->id => [
                        'token' => $monitor->token,
                        'status' => $monitor->status,
                        'checked_at' => $monitor->checked_at,
                        'started_at' => $monitor->started_at,
                        'id' => $monitor->id,
                        'name' => $monitor->name,
                        'url' => $monitor->url,
                        'alerts' => $incident->alerted,
                        'user' => $monitor->user,
                        'alert' => (object)[
                            'error' => true,
                            'type' => 'http'
                        ]
                    ]
                ]);
            }
        }

        if (array_key_exists('cause', $data)) {
            $incident->cause = $data['cause'];
        } else {
            $incident->cause = null;
        }

        if (array_key_exists('comment', $data)) {
            $incident->comment = $data['comment'];
        } else {
            $incident->comment = null;
        }

        $incident->save();

        return $incident;
    }

    /**
     * Update the incident.
     */
    public function update(Incident $incident, array $data): Incident
    {
        $now = Carbon::now();

        if (array_key_exists('started_at', $data) && $data['started_at']) {
            $incident->started_at = Carbon::parse($data['started_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        }

        if (array_key_exists('acknowledged_at', $data) && $data['acknowledged_at']) {
            if (!$incident->acknowledged_at) {
                $incident->acknowledged_at = Carbon::parse($data['acknowledged_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
            }
        }

        if (array_key_exists('ended_at', $data) && $data['ended_at']) {
            if (!$incident->ended_at) {
                $incident->ended_at = Carbon::parse($data['ended_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));

                $monitor = $incident->monitor;
                if (in_array($monitor->status, ['online', 'offline'])) {
                    $monitor->token = Str::random(16);
                    $monitor->status = 'online';
                    $monitor->checked_at = $now;
                    $monitor->started_at = $now;
                    $monitor->save();

                    $this->monitorAlertService->process([
                        $monitor->id => [
                            'token' => $monitor->token,
                            'status' => $monitor->status,
                            'checked_at' => $monitor->checked_at,
                            'started_at' => $monitor->started_at,
                            'id' => $monitor->id,
                            'name' => $monitor->name,
                            'url' => $monitor->url,
                            'alerts' => $this->monitorAlertService->getMonitorAlertsUserPlanLimited($monitor),
                            'user' => $monitor->user,
                            'alert' => (object) [
                                'error' => false,
                                'type' => 'http'
                            ]
                        ]
                    ]);
                }
            }
        }

        if (array_key_exists('cause', $data)) {
            $incident->cause = $data['cause'];
        }

        if (array_key_exists('comment', $data)) {
            $incident->comment = $data['comment'];
        }

        $incident->save();

        return $incident;
    }
}