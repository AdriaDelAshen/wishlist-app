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
            'item_name' => [
                'required',
                'string',
                'max:255'
            ],
            'item_description' => [
                'nullable',
                'string',
                'max:255'
            ],
            'item_url_link' => [
                'nullable',
                'string',
                'max:255'
            ],
            'item_price' => [
                'nullable',
                'decimal:2',
            ],
            'item_priority' => [
                'required',
                'integer',
            ],
            'item_wishlist_id' => [
                'required',
                'integer',
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if($this->item_wishlist_id) {
                if($this->user()->id != Wishlist::find($this->item_wishlist_id)->user_id) {
                    $validator->errors()->add('wishlist_item', 'You cannot create this item.');
                }
            }
        });
    }
}
