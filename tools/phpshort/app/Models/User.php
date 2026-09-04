<?php

namespace App\Models;

use App\Traits\SubscriptionCancellable;
use App\Traits\Webhookable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable implements MustVerifyEmail, hasLocalePreference
{
    use MustVerifyNewEmail, Notifiable, SoftDeletes, SubscriptionCancellable, Webhookable;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_created_at' => 'datetime',
        'plan_recurring_at' => 'datetime',
        'plan_ends_at' => 'datetime',
        'plan_trial_ends_at' => 'datetime',
        'tfa_code_created_at' => 'datetime',
        'authed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'billing_information' => 'object',
        'plan_subscription_information' => 'object',
    ];

    /**
     * The user's links count cache.
     */
    protected ?int $linksCountCache;

    /**
     * Scope a query to filter results by a partial match on the name.
     */
    public function scopeSearchName(Builder $query, string $value): Builder
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to filter results by a partial match on the email.
     */
    public function scopeSearchEmail(Builder $query, string $value): Builder
    {
        return $query->where('email', 'like', '%' . $value . '%');
    }

    /**
     * Scope a query to include results of a given plan.
     */
    public function scopeOfPlan(Builder $query, string|int $value): Builder
    {
        return $query->where('plan_id', '=', $value);
    }

    /**
     * Scope a query to include results of a given role.
     */
    public function scopeOfRole(Builder $query, string|int $value): Builder
    {
        return $query->where('role', '=', $value);
    }

    /**
     * Determine if the user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role == 1;
    }

    /**
     * Get the preferred locale of the entity.
     *
     * @return string|null
     */
    public function preferredLocale()
    {
        return $this->locale;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (config('settings.storage_signed_urls') && config('settings.storage_driver') == 's3') {
                return Storage::disk(config('settings.storage_driver'))->temporaryUrl('users/'. $this->id . '/' . $this->avatar, Carbon::now()->addMinutes(30));
            }

            return Storage::disk(config('settings.storage_driver'))->url('users/'. $this->id . '/' . $this->avatar);
        }

        return asset('img/user.png');
    }

    /**
     * Get the user's active plan.
     */
    public function getActivePlanAttribute(): Plan
    {
        if ($this->planIsDefault() || !$this->planIsActive()) {
            static $defaultPlan;

            if ($defaultPlan === null) {
                $defaultPlan = Plan::withTrashed()->find(1);
            }

            return $defaultPlan;
        }

        return $this->plan;
    }

    /**
     * Determine if the plan subscription is no longer active.
     */
    public function planIsCancelled(): bool
    {
        return !is_null($this->plan_ends_at);
    }

    /**
     * Determine if the plan subscription is within its trial period.
     */
    public function planOnTrial(): bool
    {
        return $this->plan_trial_ends_at && $this->plan_trial_ends_at->isFuture();
    }

    /**
     * Determine if the plan subscription is active.
     */
    public function planIsActive(): bool
    {
        if ($this->plan_payment_processor == 'paypal') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'ACTIVE';
        } elseif ($this->plan_payment_processor == 'stripe') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'active';
        } elseif ($this->plan_payment_processor == 'mollie') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'active';
        } elseif ($this->plan_payment_processor == 'paddle') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'active';
        } elseif ($this->plan_payment_processor == 'razorpay') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'active';
        } elseif ($this->plan_payment_processor == 'paystack') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'active';
        } elseif ($this->plan_payment_processor == 'xendit') {
            return $this->planOnTrial() || $this->planOnGracePeriod() || $this->plan_subscription_status == 'ACTIVE';
        } else {
            return $this->planOnTrial() || $this->planOnGracePeriod() || !$this->planIsCancelled();
        }
    }

    /**
     * Determine if the plan subscription is recurring and not on trial.
     */
    public function planIsRecurring(): bool
    {
        return !$this->planOnTrial() && !$this->planIsCancelled();
    }

    /**
     * Determine if the plan subscription is within its grace period after cancellation.
     */
    public function planOnGracePeriod(): bool
    {
        return $this->plan_ends_at && $this->plan_ends_at->isFuture();
    }

    /**
     * Determine if the user is subscribed to the default plan.
     */
    public function planIsDefault(): bool
    {
        return $this->plan_id == 1;
    }

    /**
     * Cancel the user plan subscription.
     */
    public function cancelPlanSubscription(): void
    {
        $this->cancelSubscription();
    }

    /**
     * Get the plan the user belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class)->withTrashed();
    }

    /**
     * Get the user's payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user's links.
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Get the user's space.
     */
    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    /**
     * Get the user's domains.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get the user's pixels.
     */
    public function pixels(): HasMany
    {
        return $this->hasMany(Pixel::class);
    }

    /**
     * Get the user's link pixels.
     */
    public function linksPixels(): HasManyThrough
    {
        return $this->hasManyThrough(LinkPixel::class, Pixel::class, 'user_id', 'pixel_id', 'id', 'id');
    }

    /**
     * Get user's link stats.
     */
    public function stats(): HasManyThrough
    {
        return $this->hasManyThrough(Stat::class, Link::class, 'user_id', 'link_id', 'id', 'id');
    }

    /**
     * Get the user's links count.
     */
    public function getLinksCountAttribute(): int
    {
        if (!isset($this->linksCountCache)) {
            $this->linksCountCache = $this->links()->count();
        }

        return $this->linksCountCache;
    }
}
