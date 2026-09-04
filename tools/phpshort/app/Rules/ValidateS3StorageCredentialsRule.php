<?php

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ValidateS3StorageCredentialsRule implements ValidationRule
{
    /**
     * The request instance.
     */
    private Request $request;

    /**
     * Create a new rule instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        config(['filesystems.disks.' . $value . '.key' => $this->request->input('storage_key')]);
        config(['filesystems.disks.' . $value . '.secret' => $this->request->input('storage_secret')]);
        config(['filesystems.disks.' . $value . '.region' => $this->request->input('storage_region')]);
        config(['filesystems.disks.' . $value . '.bucket' => $this->request->input('storage_bucket')]);
        config(['filesystems.disks.' . $value . '.endpoint' => (str_starts_with($this->request->input('storage_endpoint'), 'https://') ? $this->request->input('storage_endpoint') : 'https://' .  $this->request->input('storage_endpoint'))]);

        try {
            Storage::disk($value)->files();
        } catch (Exception $e) {
            $fail($e->getMessage());
        }
    }
}
