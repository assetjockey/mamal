<?php

namespace App\Models;

use App\Services\HelperService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_id',
        'avatar',
        'provider',
        'provider_id',
        'company',
        'website',
        'email',
        'workbook',
        'password',
        'phone_number',
        'address',
        'city',
        'postal_code',
        'country',
        'last_seen',
        'referral_id',
        'referred_by',
        'referral_paypal',
        'referral_bank',
        'personal_api_key',
        'hidden_plan',
        'used_free_tier',
        'theme',
        'subscription_required',
        'verification_code',
        'email_opt_in',
        'onboarding_completed',
        'onboarding_completed_at',
        'onboarding_current_step',
        'onboarding_total_steps',
        'onboarding_skipped',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

     /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'group',
        'plan_id',
        'status',
        'wallet',
        'balance',
        'google2fa_enabled',
        'google2fa_secret',
        'credits',
        'credits_prepaid',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen'=> 'datetime',
        ];
    }

    /* -----------------------------------------------------------------
     | Credit balance (usage credits)
     |
     | A user's spendable balance is the sum of two pools:
     |   - `credits`         : credits from plans, top-ups and admin grants.
     |   - `credits_prepaid` : prepaid / gift-card credits.
     |
     | Both are spendable on generations but neither is withdrawable (only the
     | wallet can be cashed out). Spending always draws down `credits` first and
     | falls back to `credits_prepaid`, so subscription/plan credits are used
     | before the never-expiring prepaid balance.
     | ----------------------------------------------------------------- */

    /** Total spendable usage credits across both pools. */
    public function creditBalance(): int
    {
        return (int) $this->credits + (int) $this->credits_prepaid;
    }

    /** Whether the user can cover a cost from their combined credit balance. */
    public function hasCredits(int $cost): bool
    {
        return $this->creditBalance() >= (int) $cost;
    }

    /**
     * Atomically spend `$cost` credits, drawing from `credits` first and then
     * from `credits_prepaid`. Row is locked so concurrent generations can't
     * overdraw. Returns true when the full cost was charged, false when the
     * combined balance was insufficient (no deduction made).
     */
    public function spendCredits(int $cost): bool
    {
        $cost = (int) $cost;

        if ($cost <= 0) {
            return true;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($cost) {
            /** @var static|null $fresh */
            $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (! $fresh) {
                return false;
            }

            $credits = (int) $fresh->credits;
            $prepaid = (int) $fresh->credits_prepaid;

            if ($credits + $prepaid < $cost) {
                return false;
            }

            $fromCredits = min($credits, $cost);
            $fromPrepaid = $cost - $fromCredits;

            $fresh->forceFill([
                'credits'         => $credits - $fromCredits,
                'credits_prepaid' => $prepaid - $fromPrepaid,
            ])->save();

            // Keep the in-memory instance in sync with the persisted values.
            $this->credits = $fresh->credits;
            $this->credits_prepaid = $fresh->credits_prepaid;

            return true;
        });
    }

    /**
     * Determine if the user has verified their email address.
     *
     * The model implements {@see MustVerifyEmail} so Laravel's verification
     * machinery (the `verified` middleware and the
     * SendEmailVerificationNotification listener) is active. Whether that
     * machinery actually holds a user back is gated on the admin-managed
     * `general_settings.email_verification` toggle:
     *
     *   • Toggle ON  → defer to the real, column-based check. New users are
     *     unverified, so the listener emails them and the middleware keeps
     *     them on the verification notice until they confirm.
     *   • Toggle OFF → report "verified" unconditionally, so the listener
     *     skips sending and the middleware lets everyone through. This makes
     *     the switch live: flipping it off also unblocks any users who were
     *     still mid-verification.
     *   • No settings row (console / install / tests) → fall back to the real
     *     column value so default framework behavior is preserved.
     *
     * Reading the toggle here keeps a single switch authoritative across the
     * listener, the middleware, and the {@see \App\Livewire\Settings\Profile}
     * "resend verification" UI, which all funnel through this method.
     */
    public function hasVerifiedEmail(): bool
    {
        if ($this->emailVerificationExplicitlyDisabled()) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Whether the site owner has an explicit "email verification OFF" setting.
     *
     * Returns false (i.e. "not explicitly disabled, use the real check") when
     * the settings row is absent or the DB isn't reachable, so install/console
     * contexts and the test suite behave like a stock Laravel app.
     */
    protected function emailVerificationExplicitlyDisabled(): bool
    {
        try {
            if (! HelperService::checkDBStatus() || ! Schema::hasTable('general_settings')) {
                return false;
            }

            $settings = GeneralSetting::query()->first();

            return $settings !== null && ! (bool) $settings->email_verification;
        } catch (\Throwable $e) {
            // Never let a settings lookup failure break authentication.
            return false;
        }
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscriber::class)
                    ->where('status', 'active')
                    ->where('active_until', '>', now());
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function latestSession()
    {
        return $this->hasOne(Session::class)->latestOfMany('last_activity');
    }

    public function adCreatives()
    {
        return $this->hasMany(AdCreative::class);
    }

    public function adCopies()
    {
        return $this->hasMany(AdCopy::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function brandKit()
    {
        return $this->hasOne(BrandKit::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class)->orderByDesc('is_default')->latest();
    }

    public function defaultBrand()
    {
        return $this->hasOne(Brand::class)->where('is_default', true);
    }

    // ===================================================================================
    // TEAM PLUGIN ("magicads-team")
    // ===================================================================================

    /** The team this user owns (a user owns at most one team). */
    public function ownedTeam()
    {
        return $this->hasOne(Team::class, 'owner_id');
    }

    /** This user's single membership row (owner or member), if any. */
    public function teamMembership()
    {
        return $this->hasOne(TeamMember::class);
    }

    /** Projects shared with this user by a team owner (excludes owned projects). */
    public function sharedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot(['access', 'shared_by'])
            ->withTimestamps();
    }

    /** Resolve the team this user currently belongs to (as owner or member). */
    public function currentTeam(): ?Team
    {
        return $this->teamMembership()->with('team')->first()?->team;
    }

    /** Whether this user owns the team they belong to. */
    public function ownsCurrentTeam(): bool
    {
        return (bool) $this->teamMembership()->where('role', 'owner')->exists();
    }
}
