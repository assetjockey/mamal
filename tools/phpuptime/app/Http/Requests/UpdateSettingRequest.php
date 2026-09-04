<?php

namespace App\Http\Requests;

use App\Rules\ValidateExtendedLicenseRule;
use App\Rules\ValidateS3StorageCredentialsRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
            'announcement_guest_content' => ['sometimes', 'nullable', 'string', 'max:20480'],
            'announcement_user_content' => ['sometimes', 'nullable', 'string', 'max:20480'],
            'license_key' => ['sometimes', 'required'],
            'homepage_redirect_url' => ['sometimes', 'nullable', 'url'],
            'logo' => ['sometimes', 'max:2000'],
            'logo_dark' => ['sometimes', 'max:2000'],
            'favicon' => ['sometimes', 'image', 'max:2000'],
            'theme' => ['sometimes', 'integer', 'between:0,1'],
            'stripe' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'stripe_key' => ['sometimes', 'required_if:stripe,1'],
            'stripe_secret' => ['sometimes', 'required_if:stripe,1'],
            'stripe_wh_secret' => ['sometimes', 'required_if:stripe,1'],
            'mollie' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'mollie_key' => ['sometimes', 'required_if:mollie,1'],
            'paddle' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'paddle_mode' => ['sometimes', 'required_if:paddle,1'],
            'paddle_api_key' => ['sometimes', 'required_if:paddle,1'],
            'paddle_client_token' => ['sometimes', 'required_if:paddle,1'],
            'paddle_wh_secret' => ['sometimes', 'required_if:paddle,1'],
            'paypal' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'paypal_mode' => ['sometimes', 'required_if:paypal,1'],
            'paypal_client_id' => ['sometimes', 'required_if:paypal,1'],
            'paypal_secret' => ['sometimes', 'required_if:paypal,1'],
            'paypal_webhook_id' => ['sometimes', 'required_if:paypal,1'],
            'paystack' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'paystack_key' => ['sometimes', 'required_if:paystack,1'],
            'paystack_secret' => ['sometimes', 'required_if:paystack,1'],
            'razorpay' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'razorpay_key' => ['sometimes', 'required_if:razorpay,1'],
            'razorpay_secret' => ['sometimes', 'required_if:razorpay,1'],
            'razorpay_wh_secret' => ['sometimes', 'required_if:razorpay,1'],
            'coinbase' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'coinbase_key' => ['sometimes', 'required_if:coinbase,1'],
            'coinbase_wh_secret' => ['sometimes', 'required_if:coinbase,1'],
            'cryptocom' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'cryptocom_key' => ['sometimes', 'required_if:cryptocom,1'],
            'cryptocom_secret' => ['sometimes', 'required_if:cryptocom,1'],
            'cryptocom_wh_secret' => ['sometimes', 'required_if:cryptocom,1'],
            'bank' => ['sometimes', 'required', 'integer', 'between:0,1', new ValidateExtendedLicenseRule()],
            'storage_driver' => ['sometimes', 'in:public,s3', new ValidateS3StorageCredentialsRule()],
            'storage_key' => ['sometimes', 'required_if:storage_driver,s3'],
            'storage_secret' => ['sometimes', 'required_if:storage_driver,s3'],
            'storage_region' => ['sometimes', 'required_if:storage_driver,s3'],
            'storage_bucket' => ['sometimes', 'required_if:storage_driver,s3'],
            'storage_endpoint' => ['sometimes', 'required_if:storage_driver,s3'],
            'social_discord' => ['sometimes', 'nullable', 'url'],
            'social_facebook' => ['sometimes', 'nullable', 'url'],
            'social_github' => ['sometimes', 'nullable', 'url'],
            'social_instagram' => ['sometimes', 'nullable', 'url'],
            'social_linkedin' => ['sometimes', 'nullable', 'url'],
            'social_pinterest' => ['sometimes', 'nullable', 'url'],
            'social_reddit' => ['sometimes', 'nullable', 'url'],
            'social_threads' => ['sometimes', 'nullable', 'url'],
            'social_tiktok' => ['sometimes', 'nullable', 'url'],
            'social_tumblr' => ['sometimes', 'nullable', 'url'],
            'social_x' => ['sometimes', 'nullable', 'url'],
            'social_youtube' => ['sometimes', 'nullable', 'url'],
            'webhook_user_created' => ['sometimes', 'nullable', 'url'],
            'webhook_user_updated' => ['sometimes', 'nullable', 'url'],
            'webhook_user_deleted' => ['sometimes', 'nullable', 'url'],
            'webhook_payment_created' => ['sometimes', 'nullable', 'url'],
            'webhook_payment_updated' => ['sometimes', 'nullable', 'url'],
            'contact_email' => ['sometimes', 'nullable', 'email', 'required_if:contact_form,1'],
            'pwa_logo' => ['sometimes', 'image', 'dimensions:width=512,height=512'],
            'pwa_logo_maskable' => ['sometimes', 'image', 'dimensions:width=512,height=512'],
            'pwa_logo_monochrome' => ['sometimes', 'image', 'dimensions:width=512,height=512'],
            'demo_url' => ['sometimes', 'nullable', 'url'],
        ];
    }
}
