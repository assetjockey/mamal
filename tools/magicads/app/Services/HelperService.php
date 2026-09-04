<?php

namespace App\Services;


use Illuminate\Support\Facades\DB;
use App\Models\GeneralSetting;
use App\Models\Extension;
use App\Models\ExtensionSetting;
use App\Models\Plan;
use App\Models\FeatureSetting;
use Illuminate\Support\Facades\Auth;

class HelperService 
{
    public static function checkDBStatus()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function checkField(string $key, $default = null)
    {
        $setting = GeneralSetting::query()->first();
        return $setting?->getAttribute($key) ?? $default;
    }


    // PLAN SELECTION INTENT
    // ===================================================================================
    /**
     * Resolve the post-authentication redirect target, honoring a plan the user
     * picked on the public pricing page before signing in.
     *
     * The public "Choose plan" buttons funnel through PlanSelectionController,
     * which stashes the chosen plan_id in the session as `plan_intent` for
     * guests. Once the user is authenticated (and verified, when verification is
     * enabled) the auth responses call this to consume that intent and forward
     * the user to checkout instead of the bare dashboard.
     *
     * The intent is cleared whether or not it resolves to a checkout URL so it
     * can never leak into a later, unrelated session.
     *
     * @return string|null Absolute, locale-aware checkout URL, or null when
     *                     there is no actionable intent.
     */
    public static function planIntentRedirect(): ?string
    {
        $planId = session()->pull('plan_intent');

        if (blank($planId) || ! self::extensionSaaS()) {
            return null;
        }

        $plan = Plan::where('plan_id', $planId)
            ->where('status', 'active')
            ->first();

        // Free or unavailable plans have no checkout step.
        if (! $plan || (float) $plan->price <= 0) {
            return null;
        }

        return \Mcamara\LaravelLocalization\Facades\LaravelLocalization::localizeUrl(
            route('user.billing.checkout', ['planId' => $plan->plan_id])
        );
    }
    // ===================================================================================


    // SAAS FEATURE
    // ===================================================================================
    public static function checkSaaSAccess()
    {   
        return self::extensionSaaS();
    }
    // ===================================================================================


    // SAAS EXTENSION
    // ===================================================================================
    public static function extensionCheckSaaS(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-saas')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function extensionSaaS(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-saas')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->saas_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // PROMPT MARKETPLACE EXTENSION
    // ===================================================================================
    /** Whether the Prompt Marketplace plugin is installed. */
    public static function extensionCheckPromptMarketplace(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-prompt-marketplace')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Whether the Prompt Marketplace is installed AND switched on by the admin. */
    public static function extensionPromptMarketplace(): bool
    {
        try {
            // The marketplace depends on the SaaS plugin for its payment
            // gateways and wallet/finance plumbing, so it is only available when
            // SaaS is active too. If SaaS is missing/off, the marketplace and
            // every entry point we added (sidebar, gallery "Sell" action) hide.
            if (! self::extensionSaaS()) {
                return false;
            }

            $extension = Extension::where('slug', 'magicads-prompt-marketplace')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->prompt_marketplace_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // GIFT CARDS EXTENSION
    // ===================================================================================
    /** Whether the Gift Cards plugin is installed (drives the admin section). */
    public static function extensionCheckGiftCards(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-gift-cards')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Whether Gift Cards is installed AND switched on by the admin. */
    public static function extensionGiftCards(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-gift-cards')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->gift_cards_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // COUPONS EXTENSION
    // ===================================================================================
    /** Whether the Coupons plugin is installed (drives the admin section). */
    public static function extensionCheckCoupons(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-coupons')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Coupons is installed AND switched on by the admin.
     *
     * Like the prompt marketplace, coupons only make sense alongside the SaaS
     * plugin (they discount checkout for paid plans), so they hide entirely
     * when SaaS is missing/off. When true, the checkout coupon field (one-time
     * plans only) and the user profile "shared coupons" section appear.
     */
    public static function extensionCoupons(): bool
    {
        try {
            if (! self::extensionSaaS()) {
                return false;
            }

            $extension = Extension::where('slug', 'magicads-coupons')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->coupons_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // PHOTO STUDIO EXTENSION
    // ===================================================================================
    public static function extensionCheckPhotoStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-photo-studio')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function extensionPhotoStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-photo-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->photo_studio_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // FASHION STUDIO EXTENSION
    // ===================================================================================
    public static function extensionCheckFashionStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-fashion-studio')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function extensionFashionStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-fashion-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            // Global feature must be enabled for the extension to be usable at all.
            if (! isset($settings->fashion_studio_feature)) {
                return false;
            }

            // Subscribed users: defer to the per-plan toggle.
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->fashion_studio_feature)) {
                    return (bool) $plan->fashion_studio_feature;
                }

                return false;
            }

            // Non-subscribed users: only when the global feature and free tier are on.
            if ($settings->fashion_studio_feature) {
                return (bool) $settings->fashion_studio_free_tier;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Fashion Studio is offered on the platform at all: the extension
     * is installed/activated and its global feature flag is on. Independent of
     * the current user's plan or free-tier eligibility — answers "does this
     * tool exist here?", not "can this user use it?".
     */
    public static function fashionStudioFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-fashion-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->fashion_studio_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Fashion Studio should appear as a locked, upgrade-to-unlock
     * entry: offered platform-wide but not granted to the current user by
     * their plan or free-tier eligibility.
     */
    public static function fashionStudioLocked(): bool
    {
        return self::fashionStudioFeatureEnabled() && ! self::extensionFashionStudio();
    }
    // ===================================================================================


    // AVATAR STUDIO EXTENSION
    // ===================================================================================
    public static function extensionCheckAvatarStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-avatar-studio')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function extensionAvatarStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-avatar-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            // Global feature must be enabled for the extension to be usable at all.
            if (! isset($settings->avatar_studio_feature)) {
                return false;
            }

            // Subscribed users: defer to the per-plan toggle.
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->avatar_studio_feature)) {
                    return (bool) $plan->avatar_studio_feature;
                }

                return false;
            }

            // Non-subscribed users: only when the global feature and free tier are on.
            if ($settings->avatar_studio_feature) {
                return (bool) $settings->avatar_studio_free_tier;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Avatar Studio is offered on the platform at all: the extension
     * is installed/activated and its global feature flag is on. Independent of
     * the current user's plan or free-tier eligibility.
     */
    public static function avatarStudioFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-avatar-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->avatar_studio_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Avatar Studio should appear as a locked, upgrade-to-unlock entry:
     * offered platform-wide but not granted to the current user by their plan
     * or free-tier eligibility.
     */
    public static function avatarStudioLocked(): bool
    {
        return self::avatarStudioFeatureEnabled() && ! self::extensionAvatarStudio();
    }
    // ===================================================================================

    // UGC FACTORY PLUGIN
    // ===================================================================================
    public static function extensionCheckUgcFactory(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-ugc-factory')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function extensionUgcFactory(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-ugc-factory')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            // Global feature must be enabled for the extension to be usable at all.
            if (! isset($settings->ugc_factory_feature)) {
                return false;
            }

            // Subscribed users: defer to the per-plan toggle.
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->ugc_factory_feature)) {
                    return (bool) $plan->ugc_factory_feature;
                }

                return false;
            }

            // Non-subscribed users: only when the global feature and free tier are on.
            if ($settings->ugc_factory_feature) {
                return (bool) $settings->ugc_factory_free_tier;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether UGC Factory is offered on the platform at all: the extension is
     * installed/activated and its global feature flag is on. Independent of the
     * current user's plan or free-tier eligibility.
     */
    public static function ugcFactoryFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-ugc-factory')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->ugc_factory_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether UGC Factory should appear as a locked, upgrade-to-unlock entry:
     * offered platform-wide but not granted to the current user by their plan
     * or free-tier eligibility.
     */
    public static function ugcFactoryLocked(): bool
    {
        return self::ugcFactoryFeatureEnabled() && ! self::extensionUgcFactory();
    }
    // ===================================================================================

    // TEAM PLUGIN ("magicads-team")
    // ===================================================================================
    /** Whether the Team plugin is installed (regardless of feature flags). */
    public static function extensionCheckTeam(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-team')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Teams is usable by the current user: installed, the global
     * feature switch is on, and the user is granted access by their plan
     * (subscribers) or the free-tier toggle (everyone else).
     */
    public static function extensionTeam(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-team')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            if (empty($settings?->team_feature)) {
                return false;
            }

            // Subscribed users: granted when their plan allows at least one seat.
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                return (int) ($plan?->team_members ?? 0) > 0;
            }

            // Non-subscribed users: only when the free tier is enabled and the
            // free-tier seat limit is greater than zero.
            if (! (bool) $settings->team_free_tier) {
                return false;
            }

            $freeSeats = (int) (GeneralSetting::query()->value('free_tier_team_members') ?? 0);

            return $freeSeats > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Teams is offered platform-wide (installed + global switch on),
     * independent of the current user's plan or free-tier eligibility.
     */
    public static function teamFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-team')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            return ! empty(ExtensionSetting::first()?->team_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Teams should appear as a locked, upgrade-to-unlock entry: offered
     * platform-wide but not granted to the current user.
     */
    public static function teamLocked(): bool
    {
        return self::teamFeatureEnabled() && ! self::extensionTeam();
    }
    // ===================================================================================


    // PRODUCT PHOTOSHOOT EXTENSION 
    // ===================================================================================
    public static function extensionCheckProductPhotoshoot(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-product-photoshoot')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function extensionProductPhotoshoot(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-product-photoshoot')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            // Global feature must be enabled for the extension to be usable at all.
            if (! isset($settings->product_photoshoot_feature)) {
                return false;
            }

            // Subscribed users: defer to the per-plan toggle.
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->product_photoshoot_feature)) {
                    return (bool) $plan->product_photoshoot_feature;
                }

                return false;
            }

            // Non-subscribed users: only when the global feature and free tier are on.
            if ($settings->product_photoshoot_feature) {
                return (bool) $settings->product_photoshoot_free_tier;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Product Photoshoot is offered on the platform at all: the
     * extension is installed/activated and its global feature flag is on.
     * Independent of the current user's plan or free-tier eligibility.
     */
    public static function productPhotoshootFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-product-photoshoot')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = ExtensionSetting::first();

            return ! empty($settings?->product_photoshoot_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Product Photoshoot should appear as a locked, upgrade-to-unlock
     * entry: offered platform-wide but not granted to the current user by
     * their plan or free-tier eligibility.
     */
    public static function productPhotoshootLocked(): bool
    {
        return self::productPhotoshootFeatureEnabled() && ! self::extensionProductPhotoshoot();
    }
    // ===================================================================================


    // SOCIAL MEDIA STUDIO EXTENSION
    // ===================================================================================

    /** Whether the Social Media Studio plugin is installed (drives the admin card/route). */
    public static function extensionCheckSocialMediaStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-social-media-studio')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether the current user can use Social Media Studio.
     *
     * Publishing is free; the only metered cost is AI caption generation (which
     * spends the user's shared credits). Access therefore gates on:
     *   - the plugin being installed and the global feature switch on,
     *   - subscribers (any plan) are allowed,
     *   - free-tier users only when the free-tier switch is on.
     */
    public static function extensionSocialMediaStudio(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-social-media-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = \App\Models\SocialMediaStudioSetting::first();

            if (empty($settings?->social_media_studio_feature)) {
                return false;
            }

            // Subscribers: defer to the per-plan toggle (like ugc_factory_feature).
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->social_media_studio_feature)) {
                    return (bool) $plan->social_media_studio_feature;
                }

                return false;
            }

            // Free-tier (no plan): only when free-tier access is enabled.
            return (bool) $settings->social_media_studio_free_tier;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Social Media Studio is offered on the platform at all: installed
     * and its global feature flag on. Independent of the current user's plan.
     */
    public static function socialMediaStudioFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-social-media-studio')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            return ! empty(\App\Models\SocialMediaStudioSetting::first()?->social_media_studio_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Social Media Studio should appear as a locked, upgrade-to-unlock
     * entry: offered platform-wide but not granted to the current user.
     */
    public static function socialMediaStudioLocked(): bool
    {
        return self::socialMediaStudioFeatureEnabled() && ! self::extensionSocialMediaStudio();
    }
    // ===================================================================================


    // CHANNEL BROADCAST EXTENSION
    // ===================================================================================

    /** Whether the Channel Broadcast plugin is installed (drives the admin card/route). */
    public static function extensionCheckChannelBroadcast(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-channel-broadcast')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether the current user can use Channel Broadcast.
     *
     * Broadcasting is free; the only metered cost is AI message generation
     * (which spends the user's shared credits). Access gates on:
     *   - the plugin being installed and the global feature switch on,
     *   - subscribers (any plan) are allowed,
     *   - free-tier users only when the free-tier switch is on.
     */
    public static function extensionChannelBroadcast(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-channel-broadcast')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = \App\Models\ChannelBroadcastSetting::first();

            if (empty($settings?->channel_broadcast_feature)) {
                return false;
            }

            // Subscribers: defer to the per-plan toggle (like ugc_factory_feature).
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->channel_broadcast_feature)) {
                    return (bool) $plan->channel_broadcast_feature;
                }

                return false;
            }

            return (bool) $settings->channel_broadcast_free_tier;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Channel Broadcast is offered on the platform at all: installed
     * and its global feature flag on. Independent of the current user's plan.
     */
    public static function channelBroadcastFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-channel-broadcast')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            return ! empty(\App\Models\ChannelBroadcastSetting::first()?->channel_broadcast_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Channel Broadcast should appear as a locked, upgrade-to-unlock
     * entry: offered platform-wide but not granted to the current user.
     */
    public static function channelBroadcastLocked(): bool
    {
        return self::channelBroadcastFeatureEnabled() && ! self::extensionChannelBroadcast();
    }
    // ===================================================================================


    // AD PERFORMANCE ANALYTICS EXTENSION
    // ===================================================================================

    /** Whether the Ad Performance Analytics plugin is installed (drives the admin card/route). */
    public static function extensionCheckAdAnalytics(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-ad-performance-analytics')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether the current user can use Ad Performance Analytics.
     *
     * Connecting accounts, syncing and dashboards are free; the only metered
     * cost is AI insight generation (which spends the user's shared credits).
     * Access gates on:
     *   - the plugin being installed and the global feature switch on,
     *   - subscribers (any plan) defer to the per-plan toggle,
     *   - free-tier users only when the free-tier switch is on.
     */
    public static function extensionAdAnalytics(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-ad-performance-analytics')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            $settings = \App\Models\AdAnalyticsSetting::first();

            if (empty($settings?->ad_analytics_feature)) {
                return false;
            }

            // Subscribers: defer to the per-plan toggle.
            if (! is_null(Auth::user()?->plan_id)) {
                $plan = Plan::where('id', Auth::user()->plan_id)->first();

                if (! is_null($plan?->ad_analytics_feature)) {
                    return (bool) $plan->ad_analytics_feature;
                }

                return false;
            }

            // Free-tier (no plan): only when free-tier access is enabled.
            return (bool) $settings->ad_analytics_free_tier;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Ad Performance Analytics is offered on the platform at all:
     * installed and its global feature flag on. Independent of the user's plan.
     */
    public static function adAnalyticsFeatureEnabled(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-ad-performance-analytics')->first();

            if (! $extension || ! $extension->installed) {
                return false;
            }

            return ! empty(\App\Models\AdAnalyticsSetting::first()?->ad_analytics_feature);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether Ad Performance Analytics should appear as a locked,
     * upgrade-to-unlock entry: offered platform-wide but not granted to the
     * current user.
     */
    public static function adAnalyticsLocked(): bool
    {
        return self::adAnalyticsFeatureEnabled() && ! self::extensionAdAnalytics();
    }
    // ===================================================================================


    // AMAZON S3 STORAGE EXTENSION
    // ===================================================================================

    /** Whether the Amazon S3 plugin is installed (drives the admin card/route). */
    public static function extensionCheckAmazonS3(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-amazon-s3')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // WASABI STORAGE EXTENSION
    // ===================================================================================

    /** Whether the Wasabi plugin is installed (drives the admin card/route). */
    public static function extensionCheckWasabi(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-wasabi')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // CLOUDFLARE R2 STORAGE EXTENSION
    // ===================================================================================

    /** Whether the Cloudflare R2 plugin is installed (drives the admin card/route). */
    public static function extensionCheckCloudflareR2(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-cloudflare-r2')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // GOOGLE CLOUD STORAGE EXTENSION
    // ===================================================================================

    /** Whether the Google Cloud Storage plugin is installed (drives the admin card/route). */
    public static function extensionCheckGoogleCloudStorage(): bool
    {
        try {
            $extension = Extension::where('slug', 'magicads-google-cloud-storage')->first();

            return $extension ? (bool) $extension->installed : false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    // ===================================================================================


    // CREATIVE TOOLS ACCESS (Copy / Image / Video Studio)
    // ===================================================================================

    /**
     * Resolve whether the current user can access a given Creative Tool studio.
     *
     * Process:
     *  1. The global feature flag in `feature_settings` must be on, otherwise no
     *     one gets access regardless of plan or free tier.
     *  2. Subscribers (users with a plan): access is granted only when their
     *     plan's matching `*_feature` column enables that studio.
     *  3. Free-tier users (no plan): access is granted only when the matching
     *     `*_free_tier` flag in `feature_settings` is on.
     *
     * @param  string  $key  One of: copy_studio, image_studio, video_studio
     */
    public static function studioAccess(string $key): bool
    {
        try {
            $allowed = ['copy_studio', 'image_studio', 'video_studio'];

            if (! in_array($key, $allowed, true)) {
                return false;
            }

            $settings = FeatureSetting::first();

            if (! $settings) {
                return false;
            }

            $featureColumn  = $key . '_feature';
            $freeTierColumn = $key . '_free_tier';

            // The global feature must be enabled for anyone to gain access.
            if (! $settings->{$featureColumn}) {
                return false;
            }

            $user = Auth::user();

            // Subscribed users: defer to the per-plan toggle column.
            if (! is_null($user?->plan_id)) {
                $plan = Plan::where('id', $user->plan_id)->first();

                if (! $plan) {
                    return false;
                }

                return (bool) $plan->{$featureColumn};
            }

            // Free-tier users (no plan): granted only when the free tier flag is on.
            return (bool) $settings->{$freeTierColumn};
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function accessCopyStudio(): bool
    {
        return self::studioAccess('copy_studio');
    }

    public static function accessImageStudio(): bool
    {
        return self::studioAccess('image_studio');
    }

    public static function accessVideoStudio(): bool
    {
        return self::studioAccess('video_studio');
    }

    /**
     * Whether a studio is offered on the platform at all, i.e. the global
     * `*_feature` flag in `feature_settings` is on. This is intentionally
     * independent of the current user's plan or free-tier eligibility — it
     * answers "does this tool exist here?", not "can this user use it?".
     *
     * @param  string  $key  One of: copy_studio, image_studio, video_studio
     */
    public static function studioFeatureEnabled(string $key): bool
    {
        try {
            $allowed = ['copy_studio', 'image_studio', 'video_studio'];

            if (! in_array($key, $allowed, true)) {
                return false;
            }

            $settings = FeatureSetting::first();

            if (! $settings) {
                return false;
            }

            return (bool) $settings->{$key.'_feature'};
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Whether a studio should be shown to the current user as a locked,
     * upgrade-to-unlock entry rather than hidden outright.
     *
     * True when the studio is enabled platform-wide but the current user's
     * plan (subscribers) or free-tier eligibility doesn't grant access. In
     * that case we keep the tool visible and route the user to billing so
     * they can upgrade, instead of silently hiding it.
     *
     * @param  string  $key  One of: copy_studio, image_studio, video_studio
     */
    public static function studioLocked(string $key): bool
    {
        return self::studioFeatureEnabled($key) && ! self::studioAccess($key);
    }

    public static function studioLockedCopyStudio(): bool
    {
        return self::studioLocked('copy_studio');
    }

    public static function studioLockedImageStudio(): bool
    {
        return self::studioLocked('image_studio');
    }

    public static function studioLockedVideoStudio(): bool
    {
        return self::studioLocked('video_studio');
    }

    /**
     * Where to send a user who hits a locked studio. Prefer the billing page
     * when the SaaS extension exposes it; otherwise fall back to the
     * dashboard so the redirect never points at a missing route.
     */
    public static function studioUpgradeUrl(): string
    {
        if (self::extensionSaaS() && \Illuminate\Support\Facades\Route::has('user.billing')) {
            return route('user.billing');
        }

        return route('user.dashboard');
    }

    /**
     * Whether a real upgrade destination (billing) exists for locked studios.
     * Drives messaging — "upgrade your plan" vs a plain "not available".
     */
    public static function studioUpgradeAvailable(): bool
    {
        return self::extensionSaaS() && \Illuminate\Support\Facades\Route::has('user.billing');
    }
    // ===================================================================================

}



