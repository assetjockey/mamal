<?php

namespace App\Http\Requests;

use App\Models\Domain;
use App\Rules\LinkAliasGateRule;
use App\Rules\LinkDomainGateRule;
use App\Rules\LinkExpirationGateRule;
use App\Rules\LinkPixelGateRule;
use App\Rules\LinkStatsGateRule;
use App\Rules\LinkTargetingGateRule;
use App\Rules\LinkRedirectPasswordGateRule;
use App\Rules\LinkSpaceGateRule;
use App\Rules\ValidateAliasRule;
use App\Rules\ValidateBannedWordsRule;
use App\Rules\ValidateTargetKeyRule;
use App\Rules\ValidateDeepLinkRule;
use App\Rules\ValidateDomainOwnershipRule;
use App\Rules\LinkLimitGateRule;
use App\Rules\ValidateGoogleSafeBrowsingRule;
use App\Rules\ValidateGuestDomainRule;
use App\Rules\ValidatePixelsOwnershipRule;
use App\Rules\ValidateSpaceOwnershipRule;
use App\Rules\ValidateUrlRule;
use App\Rules\ValidateUrlsCountRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreLinkRequest extends FormRequest
{
    /**
     * The domain instance.
     */
    private Domain $domain;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->input('multiple_links') && Auth::guest()) {
            abort(403);
        }

        $this->domain = Domain::where('id', '=', $this->input('domain_id'))->firstOrFail();

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // If the user is logged-in
        if (Auth::check()) {
            $rules = [
                ($this->input('multiple_links') ? 'urls' : 'url') => ['bail', 'required', 'max:' . ($this->input('multiple_links') ? (2048 * config('settings.short_max_multi_links')) : 2048), new ValidateUrlRule(), new ValidateBannedWordsRule(), new LinkLimitGateRule($this->user()), new ValidateUrlsCountRule(), new ValidateDeepLinkRule($this->user()), new ValidateGoogleSafeBrowsingRule()],
                'alias' => ['nullable', 'alpha_dash', 'max:255', new ValidateAliasRule($this), new ValidateBannedWordsRule(), new LinkAliasGateRule($this->user())],
                'redirect_password' => ['nullable', 'string', 'max:128', new LinkRedirectPasswordGateRule($this->user())],
                'space_id' => ['nullable', 'integer', new ValidateSpaceOwnershipRule($this->user()->id), new LinkSpaceGateRule($this->user())],
                'domain_id' => ['required', 'integer', new ValidateDomainOwnershipRule($this->user(), $this->domain), new LinkDomainGateRule($this->user(), $this->domain)],
                'pixel_ids' => ['nullable', new ValidatePixelsOwnershipRule($this->user()->id), new LinkPixelGateRule($this->user())],
                'sensitive_content' => ['nullable', 'boolean'],
                'privacy' => ['nullable', 'integer', 'between:0,2', new LinkStatsGateRule($this->user())],
                'password' => [(in_array($this->input('privacy'), [0, 1]) ? 'nullable' : 'sometimes'), 'string', 'min:1', 'max:128', new LinkStatsGateRule($this->user())],
                'active_period_start_at' => ['nullable', 'required_with:active_period_end_at', 'date_format:Y-m-d\TH:i:s', 'before:active_period_end_at', new LinkExpirationGateRule($this->user())],
                'active_period_end_at' => ['nullable', 'required_with:active_period_start_at', 'date_format:Y-m-d\TH:i:s', 'after:active_period_start_at', new LinkExpirationGateRule($this->user())],
                'clicks_limit' => ['nullable', 'integer', 'min:0', 'digits_between:0,9', new LinkExpirationGateRule($this->user())],
                'expiration_url' => ['bail', 'nullable', new ValidateUrlRule(), 'max:2048', new ValidateBannedWordsRule(), new LinkExpirationGateRule($this->user()), new ValidateGoogleSafeBrowsingRule()],
                'targets_type' => ['nullable', 'in:' . implode(',', array_keys(config('targets')))],
                'targets' => ['nullable', 'array', 'max:100'],
            ];
        }
        // If the user is not logged in
        else {
            $rules = [
                'url' => ['bail', 'required', 'max:2048', new ValidateUrlRule(), new ValidateDeepLinkRule(null), new ValidateBannedWordsRule(), new ValidateGoogleSafeBrowsingRule()],
                'alias' => ['prohibited'],
                'redirect_password' => ['prohibited'],
                'space_id' => ['prohibited'],
                'domain_id' => [new ValidateGuestDomainRule()],
                'pixel_ids' => ['prohibited'],
                'sensitive_content' => ['prohibited'],
                'privacy' => ['prohibited'],
                'password' => ['prohibited'],
                'expiration_url' => ['prohibited'],
                'active_period_start_at' => ['prohibited'],
                'active_period_end_at' => ['prohibited'],
                'clicks_limit' => ['prohibited'],
                'targets_type' => ['prohibited'],
                'targets.*.key' => ['prohibited'],
                'targets.*.value' => ['prohibited'],
                captchaFieldName() => config('settings.captcha_driver') ? ['required', 'captcha'] : [],
            ];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        if (Auth::check()) {
            $validator->sometimes('targets.*.key', ['nullable', 'required_with:targets.*.value', 'max:2048', new ValidateTargetKeyRule($this->input('targets_type')), new LinkTargetingGateRule($this->user())], function () {
                return $this->input('targets_type') && $this->input('targets_type') != 'rotations';
            });

            $validator->sometimes('targets.*.value', ['bail', 'nullable', 'required_with:targets.*.key', 'max:2048', new ValidateUrlRule(), new ValidateBannedWordsRule(), new ValidateDeepLinkRule($this->user())], function () {
                return $this->input('targets_type') && $this->input('targets_type') != 'rotations';
            });

            $validator->sometimes('targets.*.value', ['bail', 'nullable', 'max:2048', new ValidateUrlRule(), new ValidateBannedWordsRule(), new ValidateDeepLinkRule($this->user())], function () {
                return $this->input('targets_type') && $this->input('targets_type') == 'rotations';
            });
        } else {
            $validator->addRules([
                'targets.*.key' => ['prohibited'],
                'targets.*.value' => ['prohibited'],
            ]);
        }
    }
}
