<?php

namespace App\Http\Requests\WishlistItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WishlistUnlinkItemToUserRequest extends FormRequest
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
            if(Auth::user()->id != $this->wishlist_item->user_id) {
                $validator->errors()->add('wishlist_item', __('validation.custom.wishlist_item.cannot_be_removed_from_someone_else_shopping_list'));
            }
            if($this->wishlist_item->is_bought) {
                $validator->errors()->add('wishlist_item', __('validation.custom.wishlist_item.cannot_be_removed_since_it_has_been_bought'));
            }
        });
    }
}
