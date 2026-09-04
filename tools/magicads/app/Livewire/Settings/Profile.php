<?php

namespace App\Livewire\Settings;

use App\Models\AdCreative;
use App\Models\AdCopy;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

#[Title('Profile')]
class Profile extends Component
{
    use WithFileUploads;

    // ── Identity ──────────────────────────────────────────────
    public string $name = '';
    public string $email = '';

    // ── Contact / company ─────────────────────────────────────
    public string $company = '';
    public string $website = '';
    public string $phone_number = '';

    // ── Address ───────────────────────────────────────────────
    public string $address = '';
    public string $city = '';
    public string $postal_code = '';
    public string $country = '';

    // ── Avatar upload ─────────────────────────────────────────
    public $avatar = null;

    // ── Bank transfer proof upload ────────────────────────────
    /** The order_id of the pending bank-transfer order being acted on. */
    public string $proofOrderId = '';

    /** Temporary uploaded proof file (validated on submit). */
    public $proof = null;

    /** Controls the proof-upload modal visibility. */
    public bool $showProofModal = false;

    /** Placeholder shipped with new accounts — treated as "no custom avatar". */
    private const DEFAULT_AVATAR = 'img/users/avatar.jpg';

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        $user = Auth::user();

        return $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
            && ! $user->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return true;
    }

    /**
     * Resolve the current avatar to a displayable URL.
     *
     * Social logins store a full http(s) URL, uploads store a public-disk
     * relative path, and brand-new accounts carry a placeholder we'd rather
     * fall back to initials for. Returns null whenever we should render the
     * branded initials chip instead.
     */
    #[Computed]
    public function avatarUrl(): ?string
    {
        $avatar = Auth::user()->avatar;

        if (blank($avatar) || $avatar === self::DEFAULT_AVATAR) {
            return null;
        }

        if (Str::startsWith($avatar, ['http://', 'https://'])) {
            return $avatar;
        }

        return URL::asset($avatar);
    }

    #[Computed]
    public function initials(): string
    {
        return Auth::user()->initials();
    }

    /**
     * Live subscription + plan snapshot for the sidebar card.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function subscription(): array
    {
        $user = Auth::user();
        $sub = $user->activeSubscription()->with('plan')->first();

        $currencies = config('currencies', []);
        $currencyCode = strtoupper((string) ($sub?->currency ?: ($sub?->plan?->currency ?: 'USD')));
        $symbol = $currencies[$currencyCode]['symbol'] ?? '$';

        $activeUntil = $sub?->active_until;
        $daysLeft = $activeUntil ? max(0, now()->diffInDays($activeUntil, false)) : null;

        return [
            'active'       => (bool) $sub,
            'planName'     => $sub?->plan?->name ?? __('Free'),
            'planType'     => $sub?->plan_type,
            'status'       => $sub?->status ?? 'free',
            'amount'       => $sub ? (float) $sub->amount_due : 0.0,
            'symbol'       => $symbol,
            'currency'     => $currencyCode,
            'activeUntil'  => $activeUntil,
            'daysLeft'     => $daysLeft !== null ? (int) ceil($daysLeft) : null,
            'planCredits'  => (int) ($sub?->credits ?? 0),
            'isLifetime'   => $sub?->plan_type === 'lifetime',
        ];
    }

    /**
     * Aggregate customer metrics for the stat strip + usage card.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function metrics(): array
    {
        $user = Auth::user();

        $images = AdCreative::where('user_id', $user->id)->images()->completed()->count();
        $videos = AdCreative::where('user_id', $user->id)->videos()->completed()->count();
        $copies = AdCopy::where('user_id', $user->id)->where('status', 'completed')->count();

        $completedOrders = Order::where('user_id', $user->id)->where('status', 'completed');
        $totalSpent = (float) (clone $completedOrders)->sum('price');
        $orderCount = (clone $completedOrders)->count();

        return [
            'credits'        => (int) $user->credits,
            'creditsPrepaid' => (int) $user->credits_prepaid,
            'creditsTotal'   => $user->creditBalance(),
            'images'         => $images,
            'videos'         => $videos,
            'copies'         => $copies,
            'generations'    => $images + $videos + $copies,
            'totalSpent'     => $totalSpent,
            'orderCount'     => $orderCount,
            'memberSince'    => $user->created_at,
            'lastSeen'       => $user->last_seen,
        ];
    }

    /**
     * Most recent completed billing history rows.
     */
    #[Computed]
    public function recentOrders()
    {
        return Order::where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'pending', 'cancelled', 'failed', 'declined'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->company = $user->company ?? '';
        $this->website = $user->website ?? '';
        $this->phone_number = $user->phone_number ?? '';
        $this->address = $user->address ?? '';
        $this->city = $user->city ?? '';
        $this->postal_code = $user->postal_code ?? '';
        $this->country = $user->country ?? '';
    }

    /**
     * Validate the avatar the instant it's selected so users get fast feedback.
     */
    public function updatedAvatar(): void
    {
        $this->validateOnly('avatar', [
            'avatar' => ['image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
        ]);
    }

    /**
     * Persist a freshly uploaded avatar to the public disk, replacing any
     * previously uploaded file (but never touching social/remote URLs).
     */
    public function saveAvatar(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
        ]);

        $user = Auth::user();
        $previous = $user->avatar;

        // Stored on the `public` disk (root = public_path()), so this resolves
        // to /public/uploads/profile/. A hashed filename keeps every user's
        // upload unique within the shared folder.
        $path = $this->avatar->store('uploads/profile', 'public');

        if (
            filled($previous)
            && $previous !== self::DEFAULT_AVATAR
            && ! Str::startsWith($previous, ['http://', 'https://'])
            && Storage::disk('public')->exists($previous)
        ) {
            Storage::disk('public')->delete($previous);
        }

        $user->forceFill(['avatar' => $path])->save();

        $this->avatar = null;
        unset($this->avatarUrl);

        $this->dispatch('profile-updated', name: $user->name);
        Toaster::success(__('Profile photo updated.'));
    }

    /**
     * Open the proof-of-payment upload modal for a specific pending bank
     * transfer order.
     */
    public function openProofModal(string $orderId): void
    {
        $order = Order::where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->where('gateway', 'banktransfer')
            ->first();

        if (! $order || $order->status !== 'pending') {
            Toaster::error(__('This order can no longer be updated.'));

            return;
        }

        $this->reset('proof');
        $this->resetErrorBag('proof');
        $this->proofOrderId = $orderId;
        $this->showProofModal = true;
    }

    /**
     * Store (or replace) the proof of payment for the selected pending order.
     *
     * Proofs are sensitive financial documents, so they go on the private
     * `local` disk and are served only through the gated download route.
     */
    public function uploadProof(): void
    {
        $order = Order::where('order_id', $this->proofOrderId)
            ->where('user_id', Auth::id())
            ->where('gateway', 'banktransfer')
            ->first();

        if (! $order) {
            Toaster::error(__('Order not found.'));

            return;
        }

        if ($order->status !== 'pending') {
            Toaster::error(__('This order can no longer be updated.'));
            $this->showProofModal = false;

            return;
        }

        $this->validate([
            'proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'proof.mimes' => __('The proof must be a PDF or an image (JPG, PNG, WEBP).'),
            'proof.max'   => __('The proof may not be larger than 8 MB.'),
        ]);

        // Remove a previously uploaded proof so we don't orphan files.
        if ($order->payment_proof_path && Storage::disk('local')->exists($order->payment_proof_path)) {
            Storage::disk('local')->delete($order->payment_proof_path);
        }

        $path = $this->proof->store('payment-proofs/'.Auth::id(), 'local');

        $order->forceFill([
            'payment_proof_path'        => $path,
            'payment_proof_uploaded_at' => now(),
        ])->save();

        $this->reset('proof', 'proofOrderId');
        $this->showProofModal = false;

        unset($this->recentOrders);

        Toaster::success(__('Your proof of payment was uploaded. We will review it shortly.'));
    }

    /**
     * Update the full profile for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'company'      => ['nullable', 'string', 'max:100'],
            'website'      => ['nullable', 'url', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'postal_code'  => ['nullable', 'string', 'max:20'],
            'country'      => ['nullable', 'string', 'size:2', Rule::in(array_keys(config('countries', [])))],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        unset($this->hasUnverifiedEmail);

        $this->dispatch('profile-updated', name: $user->name);
        Toaster::success(__('Profile updated successfully.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Render the component using the active theme's view finder.
     *
     * Livewire's default view resolution falls back to the literal
     * `resources/views/livewire` path, which bypasses the igaster/laravel-theme
     * hierarchy and breaks when the views live under a theme folder
     * (e.g. `resources/views/default/livewire/...`). Resolving the view by
     * name lets Laravel's view finder honor the active theme.
     */
    public function render(): View
    {
        return view('livewire.settings.profile');
    }
}
