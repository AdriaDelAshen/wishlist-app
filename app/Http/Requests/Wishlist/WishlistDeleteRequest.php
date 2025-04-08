<?php

namespace App\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;

class WishlistDeleteRequest extends FormRequest
{
    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if(!$this->wishlist) {
                $validator->errors()->add('wishlist', __('validation.custom.generic.an_error_occurred'));
            }

            if($this->user()->id != $this->wishlist->user_id) {
                $validator->errors()->add('wishlist', __('validation.custom.wishlist.cannot_be_deleted'));
            }
        });
    }
}
