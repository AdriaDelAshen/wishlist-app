<?php

namespace App\Http\Requests\WishlistItem;

use App\Models\Wishlist;
use Illuminate\Foundation\Http\FormRequest;

class WishlistItemDeleteRequest extends FormRequest
{
    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(!$this->wishlist_item) {
                $validator->errors()->add('wishlist_item', __('validation.custom.generic.an_error_occurred'));
            }

            if($this->user()->id != Wishlist::find($this->wishlist_item->wishlist_id)->user_id) {
                $validator->errors()->add('wishlist_item', __('validation.custom.wishlist_item.cannot_be_deleted'));
            }
        });
    }
}
