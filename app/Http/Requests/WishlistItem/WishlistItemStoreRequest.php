<?php

namespace App\Http\Requests\WishlistItem;

use App\Enums\WishlistItemTypeEnum;
use App\Models\Wishlist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'type' => [
                'required',
                new Enum(WishlistItemTypeEnum::class)
            ],
            'priority' => [
                'required',
                'integer',
            ],
            'wishlist_id' => [
                'required',
                'integer',
                'exists:wishlists,id',
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if($this->wishlist_id) {
                $wishlist = Wishlist::find($this->wishlist_id);
                if(!$wishlist || $this->user()->id != $wishlist->user_id) {
                    $validator->errors()->add('wishlist_id', __('validation.custom.wishlist_item.cannot_be_created_in_wishlist'));
                }
            }
        });
    }
}
