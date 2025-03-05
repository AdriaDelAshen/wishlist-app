<?php

namespace App\Http\Requests\WishlistItem;

use Illuminate\Foundation\Http\FormRequest;

class WishlistLinkItemToUserRequest extends FormRequest
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
                'exists:wishlist_items,id'
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if($this->wishlist_item->user_id) {
                $validator->errors()->add('wishlist_item', 'This item is already in someone\'s else shopping list.');
            }
        });
    }
}
