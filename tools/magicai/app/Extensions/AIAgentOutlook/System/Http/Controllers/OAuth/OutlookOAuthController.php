<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Http\Controllers\OAuth;

use App\Extensions\AIAgentGmail\System\Models\AIAgentConnector;
use App\Extensions\AIAgentOutlook\System\OAuth\OutlookOAuth;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OutlookOAuthController extends Controller
{
    public function __construct(private readonly OutlookOAuth $oauth) {}

    public function redirect(): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with(['type' => 'error', 'message' => __('This feature is disabled in demo mode.')]);
        }

        return redirect()->away($this->oauth->authorizationUrl(Auth::id()));
    }

    public function callback(Request $request): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return $this->redirectWith('error', 'This feature is disabled in demo mode.');
        }

        $code = $request->get('code');
        $state = $request->get('state');

        if (! $code) {
            return $this->redirectWith('error', 'Something went wrong, please try again.');
        }

        try {
            $tokenData = $this->oauth->exchangeCode($code, (string) $state, Auth::id());
        } catch (Throwable $exception) {
            return $this->redirectWith('error', $exception->getMessage());
        }

        $accessToken = data_get($tokenData, 'access_token');

        if (! $accessToken) {
            return $this->redirectWith('error', 'Authorization failed, missing access token.');
        }

        try {
            $profile = $this->oauth->getProfile($accessToken);
        } catch (Throwable) {
            $profile = [];
        }

        $refreshToken = data_get($tokenData, 'refresh_token');
        $expiresIn = (int) data_get($tokenData, 'expires_in', 3600);
        $expiresAt = now()->addSeconds($expiresIn);

        $credentials = array_filter([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at'    => $expiresAt->toDateTimeString(),
            'token_type'    => data_get($tokenData, 'token_type'),
            'scope'         => data_get($tokenData, 'scope'),
            'email'         => data_get($profile, 'mail') ?? data_get($profile, 'userPrincipalName'),
            'name'          => data_get($profile, 'displayName'),
        ], fn ($value) => $value !== null);

        $profileEmail = data_get($credentials, 'email');

        $existing = AIAgentConnector::query()
            ->where('user_id', Auth::id())
            ->where('type', 'outlook')
            ->when($profileEmail, fn ($q) => $q->where('credentials->email', $profileEmail))
            ->first();

        if ($existing) {
            $existingCredentials = $existing->credentials ?? [];

            if (empty($credentials['refresh_token']) && isset($existingCredentials['refresh_token'])) {
                $credentials['refresh_token'] = $existingCredentials['refresh_token'];
            }

            $existing->update([
                'credentials'  => array_merge($existingCredentials, $credentials),
                'is_active'    => true,
                'connected_at' => now(),
                'expires_at'   => $expiresAt,
            ]);
        } else {
            AIAgentConnector::query()->create([
                'user_id'      => Auth::id(),
                'type'         => 'outlook',
                'credentials'  => $credentials,
                'is_active'    => true,
                'connected_at' => now(),
                'expires_at'   => $expiresAt,
            ]);
        }

        return $this->redirectWith('success', 'Outlook account connected successfully.');
    }

    private function redirectWith(string $type, string $message): RedirectResponse
    {
        return redirect()->route('dashboard.user.ai-agent.connectors.index')
            ->with(['type' => $type, 'message' => __($message)]);
    }
}
