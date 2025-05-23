<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'preferred_locale' => [
                'required',
                'string',
                'max:2'
            ],
            'wants_birthday_notifications' => [
                'sometimes',
                'boolean',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'is_admin' => [
                'sometimes',
                'boolean',
            ],
            'password' => [
                'nullable',
                Password::defaults(),
                'confirmed'
            ],
            'birthday_date' => [
                'sometimes',
                'date',
                'before:today',
                'after:-120 years',
                'nullable'
            ],
        ];
    }
}
