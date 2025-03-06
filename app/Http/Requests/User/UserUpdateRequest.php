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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user->id),
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'is_admin' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(!$this->user) {
                $validator->errors()->add('user', 'Account does not exist.');
            }

//            if($this->user()->id == $this->user->id) {
//                $validator->errors()->add('user', 'You cannot delete your own account.');
//            }
        });
    }
}
