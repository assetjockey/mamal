<?php

namespace App\Http\Methods;

use App\Models\Plan;
use App\Models\TransferFile;
use App\Models\User;
use Auth;
use Carbon\Carbon;

class SubscriptionManager
{
    protected static function countReminingTimeByDate($date)
    {
        $remining_days = Carbon::now()->diffInDays(Carbon::parse($date), false);
        if ($remining_days > 1) {
            $remining_days = $remining_days . ' ' . lang('Days left', 'user');
        } elseif ($remining_days == 1) {
            $remining_days = $remining_days . ' ' . lang('Day left', 'user');
        } elseif ($remining_days == 0 && $date > Carbon::now()) {
            $remining_days = lang('Less than one day left', 'user');
        } elseif (Carbon::now() > $date) {
            $remining_days = lang('Expired', 'user');
        }
        return $remining_days;
    }

    protected static function getClientUsedSpace()
    {
        if (Auth::user()) {
            $usedSpace = TransferFile::where('user_id', Auth::user()->id)->sum('size');
        } else {
            $usedSpace = TransferFile::where([['ip', vIpInfo()->ip], ['user_id', null]])->sum('size');
        }
        return $usedSpace;
    }

    protected static function getSubscriptionIntervalName($interval)
    {
        if ($interval == 0) {
            $interval = lang('monthly', 'plans');
        } elseif ($interval == 1) {
            $interval = lang('yearly', 'plans');
        } elseif ($interval == 2) {
            $interval = lang('lifetime', 'plans');
        }
        return $interval;
    }

    public static function registredUserSubscriptionDetails($id)
    {
        $user = User::where('id', $id)->with('subscription')->first();
        $data = (object) [
            'is_subscribed' => ($user->subscription) ? true : false,
            'is_lifetime' => ($user->subscription->plan->interval == 2) ? true : false,
            'is_canceled' => (!$user->subscription->status) ? true : false,
            'expired_date' => $user->subscription->expiry_at,
            'remining_days' => ($user->subscription->plan->interval == 2) ? Carbon::now()->diffInDays(Carbon::parse(Carbon::now()->addDays(30)), false) : Carbon::now()->diffInDays(Carbon::parse($user->subscription->expiry_at), false),
            'expired_at' => ($user->subscription->plan->interval == 2) ? null : static::countReminingTimeByDate($user->subscription->expiry_at),
            'is_expired' => isExpiry($user->subscription->expiry_at),
            "plan" => (object) [
                'id' => $user->subscription->plan->id,
                'name' => $user->subscription->plan->name,
                'interval' => $user->subscription->plan->interval,
                'interval_name' => static::getSubscriptionIntervalName($user->subscription->plan->interval),
                'price_symbol' => priceSymbol($user->subscription->plan->price),
                'is_free' => ($user->subscription->plan->price != 0) ? false : true,
                'storage_space_number' => $user->subscription->plan->storage_space,
                'storage_space' => ($user->subscription->plan->storage_space) ? formatBytes($user->subscription->plan->storage_space) : lang('Unlimited', 'user'),
                'transfer_size_number' => $user->subscription->plan->transfer_size,
                'transfer_size' => ($user->subscription->plan->transfer_size) ? formatBytes($user->subscription->plan->transfer_size) : lang('Unlimited', 'user'),
                'transfer_interval_number' => $user->subscription->plan->transfer_interval,
                'transfer_interval_days' => ($user->subscription->plan->transfer_interval) ? ($user->subscription->plan->transfer_interval > 1) ? $user->subscription->plan->transfer_interval . ' ' . lang('days') : $user->subscription->plan->transfer_interval . ' ' . lang('day') : lang('Unlimited time', 'user'),
                'transfer_password' => ($user->subscription->plan->transfer_password) ? true : false,
                'transfer_notify' => ($user->subscription->plan->transfer_notify) ? true : false,
                'transfer_expiry' => ($user->subscription->plan->transfer_expiry) ? true : false,
                'transfer_link' => ($user->subscription->plan->transfer_link) ? true : false,
                'advertisements' => ($user->subscription->plan->advertisements) ? true : false,
            ],
            "storage" => (object) [
                'used_space_number' => static::getClientUsedSpace(),
                'used_space' => formatBytes(static::getClientUsedSpace()),
                'used_percentage' => ($user->subscription->plan->storage_space) ? (static::getClientUsedSpace() * 100) / $user->subscription->plan->storage_space : 0,
                'remaining_space_number' => ($user->subscription->plan->storage_space) ? ($user->subscription->plan->storage_space-static::getClientUsedSpace()) : null,
                'remaining_space' => ($user->subscription->plan->storage_space) ? formatBytes(($user->subscription->plan->storage_space-static::getClientUsedSpace())) : null,
            ],
        ];
        return $data;
    }

    public static function unregistredUserSubscriptionDetails()
    {
        $plan = Plan::where([['price', 0], ['auth', 0]])->first();
        if (!is_null($plan)) {
            $data = (object) [
                'is_subscribed' => true,
                'is_lifetime' => ($plan->interval == 2) ? true : false,
                'is_canceled' => false,
                'expired_date' => Carbon::now()->addDays(30),
                'remining_days' => Carbon::now()->diffInDays(Carbon::parse(Carbon::now()->addDays(30)), false),
                'expired_at' => static::countReminingTimeByDate(Carbon::now()->addDays(30)),
                'is_expired' => false,
                "plan" => (object) [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'interval' => $plan->interval,
                    'interval_name' => static::getSubscriptionIntervalName($plan->interval),
                    'price_symbol' => priceSymbol($plan->price),
                    'is_free' => ($plan->price != 0) ? false : true,
                    'storage_space_number' => $plan->storage_space,
                    'storage_space' => ($plan->storage_space) ? formatBytes($plan->storage_space) : lang('Unlimited', 'user'),
                    'transfer_size_number' => $plan->transfer_size,
                    'transfer_size' => ($plan->transfer_size) ? formatBytes($plan->transfer_size) : lang('Unlimited', 'user'),
                    'transfer_interval_number' => $plan->transfer_interval,
                    'transfer_interval_days' => ($plan->transfer_interval) ? ($plan->transfer_interval > 1) ? $plan->transfer_interval . ' ' . lang('days') : $plan->transfer_interval . ' ' . lang('day') : lang('Unlimited time', 'user'),
                    'transfer_password' => ($plan->transfer_password) ? true : false,
                    'transfer_notify' => ($plan->transfer_notify) ? true : false,
                    'transfer_expiry' => ($plan->transfer_expiry) ? true : false,
                    'transfer_link' => ($plan->transfer_link) ? true : false,
                    'advertisements' => ($plan->advertisements) ? true : false,
                ],
                "storage" => (object) [
                    'used_space_number' => static::getClientUsedSpace(),
                    'used_space' => formatBytes(static::getClientUsedSpace()),
                    'used_percentage' => ($plan->storage_space) ? (static::getClientUsedSpace() * 100) / $plan->storage_space : 0,
                    'remaining_space_number' => ($plan->storage_space) ? ($plan->storage_space-static::getClientUsedSpace()) : null,
                    'remaining_space' => ($plan->storage_space) ? formatBytes(($plan->storage_space-static::getClientUsedSpace())) : null,
                ],
            ];
        } else {
            $data = (object) [
                'is_subscribed' => false,
                'is_lifetime' => false,
                'is_canceled' => false,
                'expired_date' => Carbon::now()->addDays(30),
                'remining_days' => Carbon::now()->diffInDays(Carbon::parse(Carbon::now()->addDays(30)), false),
                'expired_at' => static::countReminingTimeByDate(Carbon::now()->addDays(30)),
                'is_expired' => false,
                "plan" => (object) [
                    'id' => null,
                    'name' => null,
                    'interval' => null,
                    'interval_name' => null,
                    'price_symbol' => null,
                    'is_free' => false,
                    'storage_space_number' => 0,
                    'storage_space' => 0,
                    'transfer_size_number' => 0,
                    'transfer_size' => 0,
                    'transfer_interval_number' => 0,
                    'transfer_interval_days' => 0,
                    'transfer_password' => false,
                    'transfer_notify' => false,
                    'transfer_expiry' => false,
                    'transfer_link' => false,
                    'advertisements' => true,
                ],
            ];
        }
        return $data;
    }
}
