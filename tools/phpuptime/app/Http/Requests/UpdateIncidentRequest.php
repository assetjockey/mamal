<?php

namespace App\Http\Requests;

use App\Models\Incident;
use App\Rules\ValidateBannedWordsRule;
use App\Rules\ValidateIncidentEndDateRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    /**
     * The incident instance.
     */
    private Incident $incident;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->has('user_id') && !$this->user()->isAdmin()) {
            return false;
        }

        if ($this->has('user_id')) {
            $this->incident = Incident::where([['id', '=', $this->route('id')], ['user_id', '=', $this->input('user_id')]])->firstOrFail();
        } else {
            $this->incident = Incident::where([['id', '=', $this->route('id')], ['user_id', '=', $this->user()->id]])->firstOrFail();
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'monitor_id' => ['prohibited'],
            'acknowledged_at' => ['sometimes', 'nullable', 'date_format:Y-m-d\TH:i:s'],
            'ended_at' => ['sometimes', 'nullable', 'date_format:Y-m-d\TH:i:s', new ValidateIncidentEndDateRule($this->user(), $this->incident->started_at)],
            'cause' => ['sometimes', 'nullable', 'string', 'max:255', new ValidateBannedWordsRule()],
            'comment' => ['sometimes', 'nullable', 'string', 'max:1024', new ValidateBannedWordsRule()]
        ];
    }
}
