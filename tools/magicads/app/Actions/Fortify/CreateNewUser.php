<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Concerns\ResolvesReferrals;
use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules, ResolvesReferrals;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $user_id = $this->generateUserId();

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // Resolve the referrer (if any) from the ?ref= code carried through the
        // register form. Done before creating the user so we can stamp
        // referred_by on the row in a single save.
        $referrer = $this->resolveReferrer($input['ref'] ?? null);

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'user_id' => $user_id,
            'avatar' => 'img/users/avatar.jpg',
            'referral_id' => strtoupper(Str::random(15)),
            'referred_by' => $referrer?->referral_id,
        ]);

        $defaultCredits = (int) (GeneralSetting::query()->value('default_credits') ?? 0);

        $user->forceFill([
            'group'   => 'user',
            'status'  => 'active',
            'credits' => $defaultCredits,
        ])->save();

        $user->assignRole('user');

        // Record the referral relationship so it surfaces in the affiliate
        // dashboards immediately. Commission/payment stay null until the
        // referred user actually makes a purchase.
        if ($referrer) {
            $this->recordReferral($referrer, $user);
        }

        return $user;
    }

    private function generateUserId()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        // Generate first segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $code .= '-';
        
        // Generate second segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $code .= '-';
        
        // Generate third segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return strtolower($code);
    }
}
