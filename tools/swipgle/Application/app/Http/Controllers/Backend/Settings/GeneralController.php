<?php

namespace App\Http\Controllers\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Validator;

class GeneralController extends Controller
{
    public function index()
    {
        return view('backend.settings.general.index');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'website_name' => ['required', 'string', 'max:200'],
            'website_url' => ['required', 'url'],
            'website_primary_color' => ['required'],
            'website_secondary_color' => ['required'],
            'website_file_icon_dark_color' => ['required'],
            'website_file_icon_medium_color' => ['required'],
            'website_file_icon_light_color' => ['required'],
            'website_dark_logo' => ['mimes:png,jpg,jpeg,svg', 'max:2048'],
            'website_light_logo' => ['mimes:png,jpg,jpeg,svg', 'max:2048'],
            'website_favicon' => ['mimes:png,jpg,jpeg,ico', 'max:2048'],
            'website_social_image' => ['mimes:jpg,jpeg', 'image', 'max:2048'],
            'contact_email' => ['nullable', 'email'],
            'terms_of_service_link' => ['nullable', 'url'],
            'date_format' => ['required', 'integer'],
            'website_currency' => ['required', 'integer'],
            'timezone' => ['required', 'string'],
            'expired_subscriptions_data_delete' => ['required', 'integer', 'min:1', 'max:365'],
            'active_users_counter' => ['required', 'integer', 'min:0', 'max:100000000000000'],
            'transferred_files_counter' => ['required', 'integer', 'min:0', 'max:100000000000000'],
            'daily_visitors_couner' => ['required', 'integer', 'min:0', 'max:100000000000000'],
            'all_time_downloads_couner' => ['required', 'integer', 'min:0', 'max:100000000000000'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {toastr()->error($error);}
            return back();
        }
        if ($request->has('website_dark_logo')) {
            $filename = 'dark-logo';
            $darkLogo = vFileUpload($request->file('website_dark_logo'), 'images/', $filename, settings('website_dark_logo'));
            Settings::updateSettings('website_dark_logo', $darkLogo);
        }
        if ($request->has('website_light_logo')) {
            $filename = 'light-logo';
            $lightLogo = vFileUpload($request->file('website_light_logo'), 'images/', $filename, settings('website_light_logo'));
            Settings::updateSettings('website_light_logo', $lightLogo);
        }
        if ($request->has('website_favicon')) {
            $filename = 'favicon';
            $favicon = vFileUpload($request->file('website_favicon'), 'images/', $filename, settings('website_favicon'));
            Settings::updateSettings('website_favicon', $favicon);
        }
        if ($request->has('website_social_image')) {
            $filename = 'social-image';
            $ogImage = vImageUpload($request->file('website_social_image'), 'images/', '600x315', $filename, settings('website_social_image'));
            Settings::updateSettings('website_social_image', $ogImage);
        }

        if ($request->has('website_email_verify_status') && !settings('mail_status')) {
            toastr()->error(__('SMTP is not enabled'));
            return back()->withInput();
        }

        if ($request->has('website_contact_form_status') && !settings('mail_status')) {
            toastr()->error(__('SMTP is not enabled'));
            return back()->withInput();
        }

        if ($request->has('website_contact_form_status') && !settings('contact_email')) {
            toastr()->error(__('Contact form require contact email'));
            return back()->withInput();
        }

        if (!$request->has('website_tickets_status')) {
            $supportTickets = SupportTicket::all();
            if ($supportTickets->count() > 0) {
                toastr()->error(__('Support tickets has a content exists it cannot be disabled'));
                return back()->withInput();
            }
        }

        $request->website_email_verify_status = ($request->has('website_email_verify_status')) ? 1 : 0;
        $request->website_registration_status = ($request->has('website_registration_status')) ? 1 : 0;
        $request->website_force_ssl_status = ($request->has('website_force_ssl_status')) ? 1 : 0;
        $request->website_cookie = ($request->has('website_cookie')) ? 1 : 0;
        $request->website_blog_status = ($request->has('website_blog_status')) ? 1 : 0;
        $request->website_tickets_status = ($request->has('website_tickets_status')) ? 1 : 0;
        $request->website_faq_status = ($request->has('website_faq_status')) ? 1 : 0;
        $request->website_contact_form_status = ($request->has('website_contact_form_status')) ? 1 : 0;
        $request->counter_status = ($request->has('counter_status')) ? 1 : 0;

        if (!array_key_exists($request->date_format, dateFormatsArray())) {
            toastr()->error(__('Invalid date format'));
            return back();
        }

        if (!array_key_exists($request->website_currency, currencies())) {
            toastr()->error(__('Invalid currency'));
            return back();
        }

        if (!array_key_exists($request->timezone, timezonesArray())) {
            toastr()->error(__('Invalid timezone'));
            return back();
        }

        if (!array_key_exists($request->expired_subscriptions_data_delete, timesArr())) {
            toastr()->error(__('Invalid expired subscriptions data delete time'));
            return back();
        }

        if ($request->has('statistics_action')) {
            $request->statistics_download_files = null;
            $request->statistics_send_files = null;
            $request->statistics_total_transfers = null;
        }

        $settings = Settings::whereIn('key', [
            'website_name',
            'website_url',
            'website_primary_color',
            'website_secondary_color',
            'website_file_icon_dark_color',
            'website_file_icon_medium_color',
            'website_file_icon_light_color',
            'website_email_verify_status',
            'website_registration_status',
            'website_force_ssl_status',
            'website_blog_status',
            'website_tickets_status',
            'website_faq_status',
            'website_contact_form_status',
            'contact_email',
            'terms_of_service_link',
            'website_cookie',
            'date_format',
            'website_currency',
            'timezone',
            'expired_subscriptions_data_delete',
            'counter_status',
            'active_users_counter',
            'transferred_files_counter',
            'daily_visitors_couner',
            'all_time_downloads_couner',
        ])->get();
        foreach ($settings as $setting) {
            $key = $setting->key;
            $setting->value = $request->$key;
            $setting->save();
        }
        setEnv('APP_URL', $request->website_url);
        setEnv('APP_TIMEZONE', '"' . $request->timezone . '"');
        $colorsFile = 'assets/css/extra/colors.css';
        if (!file_exists($colorsFile)) {
            fopen($colorsFile, "w");
        }
        $colors = "
        :root {
            --primaryColor: " . $request->website_primary_color . ";
            --secondaryColor: " . $request->website_secondary_color . ";
            --iconColor: " . $request->website_file_icon_dark_color . ";
            --iconColorOp: " . $request->website_file_icon_medium_color . ";
            --iconColorOp2: " . $request->website_file_icon_light_color . ";
        }
        ";
        file_put_contents($colorsFile, $colors);
        toastr()->success(__('Updated Successfully'));
        return back();
    }
}
