<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
                'sometimes',
                'string',
                'max:255'
            ],
            'email' => [
                'sometimes',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user->id),
            ],
            'preferred_locale' => [
                'sometimes',
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
            'birthday_date' => [
                'sometimes',
                'date',
                'before:today',
                'after:-120 years',
                'nullable'
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(!$this->user) {
                $validator->errors()->add('user', __('validation.custom.user.does_not_exist'));
            }
        });
    }
}
