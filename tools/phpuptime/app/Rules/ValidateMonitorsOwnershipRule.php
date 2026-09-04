<?php

namespace App\Rules;

use App\Models\Monitor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class ValidateMonitorsOwnershipRule implements ValidationRule
{
    /**
     * The request instance.
     */
    private Request $request;

    /**
     * The ID of the user who must own the monitor.
     */
    private string $userId;

    /**
     * Create a new rule instance.
     */
    public function __construct(Request $request, string $userId)
    {
        $this->request = $request;
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail(__('The :attribute field is invalid.'));
        }

        if (is_array($value) && !empty(array_filter($value))) {
            // Get any of user's existing monitors
            if (Monitor::where('user_id', '=', $this->userId)->whereIn('id', array_filter($value))->exists()) {
                // Get the user's monitors
                $monitors = Monitor::where('user_id', '=', $this->userId)->whereIn('id', array_filter($value))->get()->pluck('id')->toArray();
                // Store the user's monitors
                $this->request->merge(['monitor_ids' => array_filter($value, function ($element) use ($monitors){
                    return in_array($element, $monitors);
                })]);
            } else {
                $fail(__('The :attribute field is invalid.'));
            }
        }
    }
}
