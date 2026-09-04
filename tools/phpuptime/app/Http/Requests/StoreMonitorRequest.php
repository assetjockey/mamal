<?php

namespace App\Http\Requests;

use App\Rules\MonitorLimitGateRule;
use App\Rules\ValidateBannedWordsRule;
use App\Rules\ValidateMonitorIntervalGateRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMonitorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:128', 'unique:monitors,name,null,id,user_id,'.$this->user()->id, new ValidateBannedWordsRule(), new MonitorLimitGateRule($this->user())],
            'url' => ['required', 'url', 'max:2048', new ValidateBannedWordsRule()],
            'interval' => ['required', 'integer', new ValidateMonitorIntervalGateRule($this->user())],
            'cache_buster' => ['nullable', 'integer', 'between:0,1'],
            'ssl_alert_days' => ['nullable', 'integer', 'in:' . implode(',', config('intervals.ssl'))],
            'domain_alert_days' => ['nullable', 'integer', 'in:' . implode(',', config('intervals.domain'))],
            'maintenance_start_at' => ['nullable', 'required_with:maintenance_end_at', 'date_format:Y-m-d\TH:i:s', 'before:maintenance_end_at'],
            'maintenance_end_at' => ['nullable', 'required_with:maintenance_start_at', 'date_format:Y-m-d\TH:i:s', 'after:maintenance_start_at'],
            'request_method' => ['required', 'in:' . implode(',', config('request.methods'))],
            'request_auth_username' => ['nullable', 'string', 'required_with:request_auth_password', 'max:255'],
            'request_auth_password' => ['nullable', 'string', 'required_with:request_auth_username', 'max:1024'],
            'request_headers' => ['nullable', 'array', 'max:100'],
            'request_headers.*.key' => ['nullable', 'string', 'required_with:request_headers.*.value', 'max:128'],
            'request_headers.*.value' => ['nullable', 'string', 'required_with:request_headers.*.key', 'max:256'],
            'alert_condition' => ['required', 'in:' . implode(',', array_keys(config('alert.conditions')))],
            'alert_text_lookup' => ['nullable', 'required_unless:alert_condition,url_unavailable', 'string', 'max:255'],
            'alerts' => ['nullable', 'array', 'max:100'],
            'alerts.*.key' => ['nullable', 'string', 'required_with:alerts.*.value', 'in:' . implode(',', array_keys(config('alert.channels')))],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('alerts.*.value', ['bail', 'nullable', 'required_with:alerts.*.key', 'email', 'max:256', new ValidateBannedWordsRule()], function ($input, $item) {
            return isset($item->key) && $item->key == 'email';
        });

        $validator->sometimes('alerts.*.value', ['bail', 'nullable', 'required_with:alerts.*.key', 'regex:/[0-9+]/', 'max:16', new ValidateBannedWordsRule()], function ($input, $item) {
            return isset($item->key) && $item->key == 'sms';
        });

        $validator->sometimes('alerts.*.value', ['bail', 'nullable', 'required_with:alerts.*.key', 'regex:/\s+/', 'max:128', new ValidateBannedWordsRule()], function ($input, $item) {
            return isset($item->key) && $item->key == 'telegram';
        });

        $validator->sometimes('alerts.*.value', ['bail', 'nullable', 'required_with:alerts.*.key', 'url', 'max:256', new ValidateBannedWordsRule()], function ($input, $item) {
            return isset($item->key) && $item->key != 'telegram' && $item->key != 'email' && $item->key != 'sms' && $item->key != '';
        });

        $validator->after(function ($validator) {
            $alerts = collect($this->input('alerts', []));

            $emailAlerts = $alerts->filter(function ($alert) {
                return isset($alert['key']) && $alert['key'] == 'email';
            })->keys();

            if ($emailAlerts->count() > $this->user()->active_plan->features->email_alerts && $this->user()->active_plan->features->email_alerts != -1) {
                $emailAlerts->slice($this->user()->active_plan->features->email_alerts)->each(function ($index) use ($validator) {
                    $validator->errors()->add('alerts.' . $index . '.key', __('The number of email alerts must not exceed :number.', ['number' => $this->user()->active_plan->features->email_alerts]));
                });
            }

            $smsAlerts = $alerts->filter(function ($alert) {
                return isset($alert['key']) && $alert['key'] == 'sms';
            })->keys();

            if ($smsAlerts->count() > $this->user()->active_plan->features->sms_alerts && $this->user()->active_plan->features->sms_alerts != -1) {
                $smsAlerts->slice($this->user()->active_plan->features->sms_alerts)->each(function ($index) use ($validator) {
                    $validator->errors()->add('alerts.' . $index . '.key', __('The number of SMS alerts must not exceed :number.', ['number' => $this->user()->active_plan->features->sms_alerts]));
                });
            }

            $webhookAlerts = $alerts->filter(function ($alert) {
                return isset($alert['key']) && $alert['key'] != 'email' && $alert['key'] != 'sms' && $alert['key'] != '';
            })->keys();

            if ($webhookAlerts->count() > $this->user()->active_plan->features->webhook_alerts && $this->user()->active_plan->features->webhook_alerts != -1) {
                $webhookAlerts->slice($this->user()->active_plan->features->webhook_alerts)->each(function ($index) use ($validator) {
                    $validator->errors()->add('alerts.' . $index . '.key', __('The number of webhook alerts must not exceed :number.', ['number' => $this->user()->active_plan->features->webhook_alerts]));
                });
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'request_headers' => mb_strtolower(__('Headers')),
            'request_headers.*.key' => mb_strtolower(__('Name')),
            'request_headers.*.value' => mb_strtolower(__('Value')),
            'alerts' => mb_strtolower(__('Alerts')),
            'alerts.*.key' => mb_strtolower(__('Channel')),
            'alerts.*.value' => mb_strtolower(__('Value')),
        ];
    }
}
