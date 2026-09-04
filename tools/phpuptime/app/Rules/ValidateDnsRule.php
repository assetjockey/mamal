<?php

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateDnsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If the domain is not the same with the installation URL
        if ($value != parse_url(config('app.url'), PHP_URL_HOST)) {
            $dnsRecords = [];
            try {
                $dnsRecords = dns_get_record($value, DNS_A + DNS_CNAME);
            } catch (Exception $e) {
                $fail(__($e->getMessage()));
            }

            $isValid = false;
            foreach ($dnsRecords as $record) {
                if ($record['type'] === 'A') {
                    if ($record['ip'] == getHostIp()) {
                        $isValid = true;
                    }
                } elseif ($record['type'] === 'CNAME') {
                    if ($record['target'] == getHostIp()) {
                        $isValid = true;
                    }
                }
            }

            if (!$isValid) {
                $fail(__('The DNS records either do not point to our server or have not yet propagated, which can take up to 24 hours.'));
            }
        }
    }
}
