<?php

namespace App\Http\Requests\WishlistItem;

use App\Models\Wishlist;
use Illuminate\Foundation\Http\FormRequest;

class WishlistItemStoreRequest extends FormRequest
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
                'required',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:255'
            ],
            'url_link' => [
                'nullable',
                'string',
                'max:255'
            ],
            'price' => [
                'nullable',
                'decimal:2',
            ],
            'priority' => [
                'required',
                'integer',
            ],
            'wishlist_id' => [
                'required',
                'integer',
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if($this->wishlist_id) {
                if($this->user()->id != Wishlist::find($this->wishlist_id)->user_id) {
                    $validator->errors()->add('wishlist_item', __('validation.custom.wishlist_item.cannot_be_created'));
                }
            }
        });
    }
}
