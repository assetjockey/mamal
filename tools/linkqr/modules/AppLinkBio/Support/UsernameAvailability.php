<?php

namespace Modules\AppLinkBio\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;

class UsernameAvailability
{
    public function check(string $value): array
    {
        $username = $this->normalize($value);

        if ($username === '') {
            return $this->result(false, $username, __('Enter a username to check.'));
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            return $this->result(false, $username, __('Use 3 to 50 characters.'));
        }

        if (! preg_match('/^[a-z0-9][a-z0-9_-]*[a-z0-9]$/', $username)) {
            return $this->result(false, $username, __('Use letters, numbers, hyphens, or underscores. Start and end with a letter or number.'));
        }

        if (in_array($username, $this->reservedSlugs(), true)) {
            return $this->result(false, $username, __('This URL is reserved.'));
        }

        if (User::query()->whereRaw('LOWER(username) = ?', [$username])->exists()) {
            return $this->result(false, $username, __('This username is already taken.'));
        }

        if ($this->existsInTable('link_bio_pages', 'slug', $username)) {
            return $this->result(false, $username, __('This bio link is already taken.'));
        }

        if ($this->existsInTable('app_short_links', 'short_code', $username)) {
            return $this->result(false, $username, __('This short link is already taken.'));
        }

        if ($this->existsInTable('link_bio_short_links', 'short_code', $username)) {
            return $this->result(false, $username, __('This short link is already taken.'));
        }

        return $this->result(true, $username, __('This username is available.'));
    }

    public function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = preg_replace('/[^a-z0-9_-]+/', '-', $value) ?: '';
        $value = trim($value, '-_');

        return Str::limit($value, 50, '');
    }

    protected function existsInTable(string $table, string $column, string $value): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        return DB::table($table)
            ->whereRaw('LOWER('.$column.') = ?', [$value])
            ->exists();
    }

    protected function result(bool $available, string $username, string $message): array
    {
        return [
            'available' => $available,
            'username' => $username,
            'message' => $message,
            'profile_url' => $username !== '' ? url('/'.$username) : null,
            'signup_url' => $username !== '' ? url('/register?username='.$username) : url('/register'),
        ];
    }

    protected function reservedSlugs(): array
    {
        return [
            'admin',
            'api',
            'app',
            'auth',
            'b',
            'billing',
            'blog',
            'blogs',
            'contact',
            'dashboard',
            'download',
            'faqs',
            'home',
            'lang',
            'login',
            'logout',
            'livewire',
            'media',
            'password',
            'payment',
            'portal',
            'pricing',
            'privacy-policy',
            'register',
            'reset-password',
            's',
            'settings',
            'signup',
            'social-pages',
            'storage',
            'terms',
            'terms-of-use',
            'username',
            'user',
            'users',
            'verify-email',
            'webhook',
            'webhooks',
        ];
    }
}
