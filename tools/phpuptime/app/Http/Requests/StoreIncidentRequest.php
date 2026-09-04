<?php

namespace App\Http\Requests;

use App\Rules\ValidateBannedWordsRule;
use App\Rules\ValidateIncidentEndDateRule;
use App\Rules\ValidateIncidentStartDateRule;
use App\Rules\ValidateMonitorOwnershipRule;
use App\Rules\ValidateOngoingIncidentRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
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
            'monitor_id' => ['required', new ValidateMonitorOwnershipRule($this->user()), new ValidateOngoingIncidentRule()],
            'started_at' => ['required', 'date_format:Y-m-d\TH:i:s', new ValidateIncidentStartDateRule($this->user(), $this->input('monitor_id'))],
            'acknowledged_at' => ['sometimes', 'nullable', 'date_format:Y-m-d\TH:i:s'],
            'ended_at' => ['sometimes', 'nullable', 'date_format:Y-m-d\TH:i:s', 'after:started_at', new ValidateIncidentEndDateRule($this->user(), Carbon::parse($this->input('started_at'), $this->user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone')))],
            'cause' => ['sometimes', 'nullable', 'string', 'max:255', new ValidateBannedWordsRule()],
            'comment' => ['sometimes', 'nullable', 'string', 'max:1024', new ValidateBannedWordsRule()]
        ];
    }
}
