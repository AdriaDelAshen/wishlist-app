<?php

namespace App\Http\Requests\WishlistItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WishlistItemChangeStateOfItemRequest extends FormRequest
{
    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(Auth::user()->id != $this->wishlist_item->user_id) {
                $validator->errors()->add('wishlist_item', __('validation.custom.wishlist_item.cannot_change_state_of_item_if_you_are_not_the_buyer'));
            }
        });
    }
}
