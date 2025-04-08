<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserIsActive implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //The message contains 'approved or does not exist' to be more secured and avoid giving out more information.
        if(User::query()->where('email', $value)->where('is_active', 0)->exists()) {
            $fail(__('validation.custom.user.must_be_approved_or_does_not_exist'));
        }
    }
}
