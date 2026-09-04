<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class UserService
{
    /**
     * Store the user.
     */
    public function store(array $data): User
    {
        $user = new User;

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        } else {
            $user->name = '';
        }

        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        } else {
            $user->email = '';
        }

        if (array_key_exists('password', $data) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        } else {
            $user->password = '';
        }

        if (array_key_exists('locale', $data)) {
            $user->locale = $data['locale'];
        } else {
            $user->locale = app()->getLocale();
        }

        if (array_key_exists('timezone', $data)) {
            $user->timezone = $data['timezone'];
        } else {
            $user->timezone = config('settings.timezone');
        }

        if (array_key_exists('tfa', $data)) {
            $user->tfa = $data['tfa'];
        } else {
            $user->tfa = config('settings.registration_tfa');
        }

        if (array_key_exists('plan_id', $data)) {
            $user->plan_id = $data['plan_id'];
        } else {
            $user->plan_id = 1;
        }

        if (array_key_exists('plan_ends_at', $data)) {
            $user->plan_ends_at = Carbon::createFromFormat('Y-m-d', $data['plan_ends_at'], $user->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } else {
            $user->plan_ends_at = null;
        }

        if (array_key_exists('api_token', $data)) {
            $user->api_token = $data['api_token'];
        } else {
            $user->api_token = Str::random(64);
        }

        if (array_key_exists('role', $data)) {
            $user->role = $data['role'];
        } else {
            $user->role = 0;
        }

        if (array_key_exists('mark_email_as_verified', $data)) {
            if ($data['mark_email_as_verified']) {
                $user->markEmailAsVerified();
            }
        }

        $user->save();

        return $user;
    }

    /**
     * Update the user.
     */
    public function update(User $user, array $data, ?User $adminUser = null): User
    {
        $now = Carbon::now();

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }

        if (array_key_exists('locale', $data)) {
            $user->locale = $data['locale'];
        }

        if (array_key_exists('timezone', $data)) {
            $user->timezone = $data['timezone'];
        }

        if (array_key_exists('tfa', $data)) {
            $user->tfa = $data['tfa'];
        }

        if (array_key_exists('password', $data) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (array_key_exists('email', $data)) {
            if ($data['email'] !== $user->email) {
                if (config('settings.registration_require_email_verification') && !$adminUser) {
                    $user->newEmail($data['email']);
                } else {
                    $user->email = $data['email'];
                }
            }
        }

        if (array_key_exists('remove_avatar', $data)) {
            if ($user->avatar) {
                Storage::disk(config('settings.storage_driver'))->delete('users/' . $user->id . '/' . $user->avatar);
            }

            $user->avatar = null;
        } elseif (!empty($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $avatar = $data['avatar'];

            if ($user->avatar) {
                Storage::disk(config('settings.storage_driver'))->delete('users/' . $user->id . '/' . $user->avatar);
            }

            try {
                Image::useImageDriver(config('settings.image_driver'))
                    ->loadFile($avatar->getRealPath())
                    ->fit(Fit::Contain, config('settings.user_avatar_size'), config('settings.user_avatar_size'))
                    ->format('jpg')
                    ->quality(80)
                    ->save();

                Storage::disk(config('settings.storage_driver'))->putFile('users/' . $user->id, $avatar, (config('settings.storage_signed_urls') && config('settings.storage_driver') == 's3' ? 'private' : 'public'));

                $user->avatar = $avatar->hashName();
            } catch (Exception) {
                $user->avatar = null;
            }
        }

        if (array_key_exists('api_token', $data)) {
            $user->api_token = Str::random(64);
        }

        if (array_key_exists('reset_tfa_code', $data)) {
            try {
                $user->tfa_code = random_int(100000, 999999);
            } catch (Exception) {
                $user->tfa_code = rand(100000, 999999);
            }

            $user->tfa_code_created_at = $now;
        }

        if (array_key_exists('update_authed_at', $data)) {
            $user->authed_at = $now;
        }

        if ($adminUser) {
            if (array_key_exists('mark_email_as_verified', $data)) {
                if ($data['mark_email_as_verified']) {
                    // Prevent verifying the user unintentionally
                    if (!$user->email_verified_at) {
                        $user->markEmailAsVerified();
                    }
                } else {
                    $user->email_verified_at = null;
                }
            }

            if (array_key_exists('role', $data)) {
                $user->role = $data['role'];
            }

            if (array_key_exists('plan_ends_at', $data) && $data['plan_ends_at']) {
                $planEndsAt = Carbon::createFromFormat('Y-m-d', $data['plan_ends_at'], $user->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
            } else {
                $planEndsAt = null;
            }

            if ($this->hasPlanIdChanged($user, $data) || $this->hasPlanEndsAtChanged($user, $data, $planEndsAt)) {
                $user->cancelPlanSubscription();

                if ($this->hasPlanIdChanged($user, $data)) {
                    $user->plan_id = $data['plan_id'];
                }

                if ($this->hasPlanEndsAtChanged($user, $data, $planEndsAt)) {
                    $user->plan_ends_at = $data['plan_ends_at'] ? $planEndsAt : null;
                }

                $user->plan_amount = null;
                $user->plan_currency = null;
                $user->plan_interval = null;
                $user->plan_payment_processor = null;
                $user->plan_subscription_id = null;
                $user->plan_subscription_status = null;
                $user->plan_subscription_information = null;
                $user->plan_created_at = $now;
                $user->plan_recurring_at = null;
                $user->plan_trial_ends_at = $user->plan_trial_ends_at ? $now : null;
            }
        }

        $user->save();

        return $user;
    }

    /**
     * Determine if the user's plan ID has changed.
     */
    private function hasPlanIdChanged(User $user, array $data): bool
    {
        if (array_key_exists('plan_id', $data)) {
            if ($user->active_plan->id != $data['plan_id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the user's plan ends at timestamp has changed.
     */
    private function hasPlanEndsAtChanged(User $user, array $data, ?Carbon $planEndsAt): bool
    {
        if (array_key_exists('plan_ends_at', $data)) {
            if ($user->plan_ends_at && empty($data['plan_ends_at'])) {
                return true;
            }

            if (empty($user->plan_ends_at) && $data['plan_ends_at']) {
                return true;
            }

            if ($user->plan_ends_at && $data['plan_ends_at'] && !$user->plan_ends_at->isSameDay($planEndsAt)) {
                return true;
            }
        }

        return false;
    }
}