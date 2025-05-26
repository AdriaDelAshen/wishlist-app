<?php

namespace App\Http\Controllers;

use App\Events\WishlistItemUserHasChanged;
use App\Http\Requests\WishlistItem\WishlistItemChangeStateOfItemRequest;
use App\Http\Requests\WishlistItem\WishlistItemDeleteRequest;
use App\Http\Requests\WishlistItem\WishlistItemStoreRequest;
use App\Http\Requests\WishlistItem\WishlistItemUpdateRequest;
use App\Http\Requests\WishlistItem\WishlistLinkItemToUserRequest;
use App\Http\Requests\WishlistItem\WishlistUnlinkItemToUserRequest;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WishlistItemController extends Controller
{
    public function show(WishlistItem $wishlistItem)
    {
        return Inertia::render('WishlistItem/Show', [
            'wishlistItem' => $wishlistItem->toDisplayData(),
        ]);
    }

    public function store(WishlistItemStoreRequest $request): RedirectResponse
    {
        WishlistItem::create($request->validated());

        return back();
    }

    public function update(WishlistItemUpdateRequest $request, WishlistItem $wishlistItem): RedirectResponse
    {
        $wishlistItem->update($request->validated());

        return back();
    }

    public function destroy(WishlistItemDeleteRequest $request, WishlistItem $wishlistItem): RedirectResponse
    {
        $wishlistItem->delete();

        return back();
    }

    public function linkItemToUser(WishlistLinkItemToUserRequest $request, WishlistItem $wishlistItem): array
    {
        $wishlistItem->update([
            'in_shopping_list' => true,
            'user_id' => Auth::user()->id,
        ]);
        WishlistItemUserHasChanged::dispatch($wishlistItem);
        return [
            'status' => 200,
            'message' => __('messages.wishlist_item_linked_successfully'),
        ];
    }

    public function unlinkItemToUser(WishlistUnlinkItemToUserRequest $request, WishlistItem $wishlistItem): array
    {
        $wishlistItem->update([
            'in_shopping_list' => false,
            'user_id' => null,
        ]);
        WishlistItemUserHasChanged::dispatch($wishlistItem);
        return [
            'status' => 200,
            'message' => __('messages.wishlist_item_unlinked_successfully'),
        ];
    }

    public function itemStateHasChanged(WishlistItemChangeStateOfItemRequest $request, WishlistItem $wishlistItem): array
    {
        $wishlistItem->update([
            'is_bought' => $request->is_bought,
        ]);
        return [
            'status' => 200,
//            'message' => __('messages.wishlist_item_unlinked_successfully'),
        ];
    }

    public function getCurrentDataFromPage(Request $request): array
    {
        return [
            'pagination' => WishlistItem::query()
                ->where('wishlist_id', $request->wishlist_id)
                ->paginate($request->perPage, ['*'], 'page', $request->page)
        ];
    }
}
