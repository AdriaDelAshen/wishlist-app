<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserPasswordUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required', Password::defaults(), 'confirmed'
            ]
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(!$this->user) {
                $validator->errors()->add('user', 'Account does not exist.');
            }
        });
    }
}
