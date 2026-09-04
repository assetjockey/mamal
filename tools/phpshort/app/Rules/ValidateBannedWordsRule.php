<?php

namespace App\Rules;

Use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateBannedWordsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (config('settings.banned_words')) {
            $bannedWords = preg_split('/[\r\n]+/', config('settings.banned_words'), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bannedWords as $word) {
                if (mb_strpos(mb_strtolower($value), mb_strtolower($word)) !== false) {
                    $fail(__('The :attribute field contains a keyword that is banned.'));
                }
            }
        }
    }
}
