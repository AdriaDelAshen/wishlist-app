<?php

namespace App\Http\Requests\WishlistItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WishlistItemContributionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contribution_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if (!$this->wishlist_item->group->members()->where('users.id', Auth::user()->id)->exists()) {
                $validator->errors()->add('wishlist_item', __('validation.custom.wishlist_item.unauthorized_or_not_member'));
            }
        });
    }
}
