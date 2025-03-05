<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;

class WishlistUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:wishlists,id'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'expiration_date' => [
                'required',
                'date_format:Y-m-d',
                //todo validates that the date is in the future
            ],
            'is_shared' => [
                'boolean',
            ],
        ];
    }
}
