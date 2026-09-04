<?php

namespace App\Services;

use App\Models\StatusPage;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StatusPageService
{
    /**
     * Store the status page.
     */
    public function store(array $data): StatusPage
    {
        $statusPage = new StatusPage;

        $statusPage->name = $data['name'];
        $statusPage->slug = $data['slug'];

        if (array_key_exists('user_id', $data)) {
            $statusPage->user_id = $data['user_id'];
        } else {
            $statusPage->user_id = Auth::user()->id;
        }

        if (array_key_exists('website_url', $data)) {
            $statusPage->website_url = $data['website_url'];
        } else {
            $statusPage->website_url = null;
        }

        if (array_key_exists('contact_url', $data)) {
            $statusPage->contact_url = $data['contact_url'];
        } else {
            $statusPage->contact_url = null;
        }

        if (array_key_exists('privacy', $data)) {
            $statusPage->privacy = $data['privacy'];
        } else {
            $statusPage->privacy = 0;
        }

        if (array_key_exists('password', $data)) {
            $statusPage->password = $data['password'];
        } else {
            $statusPage->password = null;
        }

        if (array_key_exists('custom_css', $data)) {
            $statusPage->custom_css = $data['custom_css'];
        } else {
            $statusPage->custom_css = null;
        }

        if (array_key_exists('custom_js', $data)) {
            $statusPage->custom_js = $data['custom_js'];
        } else {
            $statusPage->custom_js = null;
        }

        if (array_key_exists('noindex', $data)) {
            $statusPage->noindex = $data['noindex'];
        } else {
            $statusPage->noindex = null;
        }

        if (array_key_exists('meta_title', $data)) {
            $statusPage->meta_title = $data['meta_title'];
        } else {
            $statusPage->meta_title = null;
        }

        if (array_key_exists('meta_description', $data)) {
            $statusPage->meta_description = $data['meta_description'];
        } else {
            $statusPage->meta_description = null;
        }

        if (array_key_exists('logo', $data) && $data['logo'] instanceof UploadedFile) {
            $logo = $data['logo'];

            try {
                Storage::disk(config('settings.storage_driver'))->putFile('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id, $logo, 'public');

                $statusPage->logo = $logo->hashName();
            } catch (Exception) {
                $statusPage->logo = null;
            }
        } else {
            $statusPage->logo = null;
        }

        if (array_key_exists('favicon', $data) && $data['favicon'] instanceof UploadedFile) {
            $favicon = $data['favicon'];

            try {
                Storage::disk(config('settings.storage_driver'))->putFile('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id, $favicon, 'public');

                $statusPage->favicon = $favicon->hashName();
            } catch (Exception) {
                $statusPage->favicon = null;
            }
        } else {
            $statusPage->favicon = null;
        }

        $statusPage->save();

        $this->syncMonitors($statusPage, $data);

        return $statusPage;
    }

    /**
     * Update the status page.
     */
    public function update(StatusPage $statusPage, array $data): StatusPage
    {
        if (array_key_exists('name', $data)) {
            $statusPage->name = $data['name'];
        }

        if (array_key_exists('user_id', $data)) {
            $statusPage->user_id = $data['user_id'];
        }

        if (array_key_exists('slug', $data)) {
            $statusPage->slug = $data['slug'];
        }

        if (array_key_exists('website_url', $data)) {
            $statusPage->website_url = $data['website_url'];
        }

        if (array_key_exists('contact_url', $data)) {
            $statusPage->contact_url = $data['contact_url'];
        }

        if (array_key_exists('privacy', $data)) {
            $statusPage->privacy = $data['privacy'];
        }

        if (array_key_exists('password', $data)) {
            $statusPage->password = $data['password'];
        }

        if (array_key_exists('custom_css', $data)) {
            $statusPage->custom_css = $data['custom_css'];
        }

        if (array_key_exists('custom_js', $data)) {
            $statusPage->custom_js = $data['custom_js'];
        }

        if (array_key_exists('noindex', $data)) {
            $statusPage->noindex = $data['noindex'];
        }

        if (array_key_exists('meta_title', $data)) {
            $statusPage->meta_title = $data['meta_title'];
        }

        if (array_key_exists('meta_description', $data)) {
            $statusPage->meta_description = $data['meta_description'];
        }

        if (array_key_exists('remove_logo', $data)) {
            if ($statusPage->logo) {
                Storage::disk(config('settings.storage_driver'))->delete('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id . '/' . $statusPage->logo);
            }

            $statusPage->logo = null;
        }

        if (array_key_exists('logo', $data) && $data['logo'] instanceof UploadedFile) {
            $logo = $data['logo'];

            if ($statusPage->logo) {
                Storage::disk(config('settings.storage_driver'))->delete('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id . '/' . $statusPage->logo);
            }

            try {
                Storage::disk(config('settings.storage_driver'))->putFile('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id, $logo, 'public');

                $statusPage->logo = $logo->hashName();
            } catch (Exception) {
                $statusPage->logo = null;
            }
        }

        if (array_key_exists('remove_favicon', $data)) {
            if ($statusPage->favicon) {
                Storage::disk(config('settings.storage_driver'))->delete('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id . '/' . $statusPage->favicon);
            }

            $statusPage->favicon = null;
        }

        if (array_key_exists('favicon', $data) && $data['favicon'] instanceof UploadedFile) {
            $favicon = $data['favicon'];

            if ($statusPage->favicon) {
                Storage::disk(config('settings.storage_driver'))->delete('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id . '/' . $statusPage->favicon);
            }

            try {
                Storage::disk(config('settings.storage_driver'))->putFile('users/' . $statusPage->user_id . '/status-pages/' . $statusPage->id, $favicon, 'public');

                $statusPage->favicon = $favicon->hashName();
            } catch (Exception) {
                $statusPage->favicon = null;
            }
        }

        $this->syncMonitors($statusPage, $data);

        $statusPage->save();

        return $statusPage;
    }

    /**
     * Sync the status page monitors.
     */
    private function syncMonitors(StatusPage $statusPage, array $data): void
    {
        if (array_key_exists('monitor_ids', $data)) {
            $monitorIds = [];

            foreach (array_filter($data['monitor_ids']) as $index => $monitorId) {
                $monitorIds[$monitorId] = ['order' => $index];
            }

            $statusPage->monitors()->sync($monitorIds);
        }
    }
}