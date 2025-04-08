<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserDeleteRequest extends FormRequest
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
                'required',
                'current_password'
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(!$this->user) {
                $validator->errors()->add('user', __('validation.custom.user.does_not_exist'));
            }

            if($this->user()->id == $this->user->id) {
                $validator->errors()->add('user', __('validation.custom.user.cannot_delete_own_account'));
            }
        });
    }
}
