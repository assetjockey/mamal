<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Support\Facades\Auth;

class DomainService
{
    /**
     * Store a new domain.
     */
    public function store(array $data): Domain
    {
        $domain = new Domain;

        $domain->name = $data['name'];

        if (array_key_exists('user_id', $data)) {
            $domain->user_id = $data['user_id'];
        } else {
            $domain->user_id = Auth::user()->id;
        }

        if (array_key_exists('homepage_url', $data)) {
            $domain->homepage_url = $data['homepage_url'];
        } else {
            $domain->homepage_url = null;
        }

        if (array_key_exists('not_found_url', $data)) {
            $domain->not_found_url = $data['not_found_url'];
        } else {
            $domain->not_found_url = null;
        }

        $domain->save();

        return $domain;
    }

    /**
     * Update an existing domain.
     */
    public function update(Domain $domain, array $data): Domain
    {
        if (array_key_exists('homepage_url', $data)) {
            $domain->homepage_url = $data['homepage_url'];
        }

        if (array_key_exists('not_found_url', $data)) {
            $domain->not_found_url = $data['not_found_url'];
        }

        $domain->save();

        return $domain;
    }
}
