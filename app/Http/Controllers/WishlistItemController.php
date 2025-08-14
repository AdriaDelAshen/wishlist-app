<?php

namespace App\Http\Controllers;

use App\Enums\WishlistItemTypeEnum;
use App\Events\GroupMemberJoined;
use App\Events\GroupMemberLeft;
use App\Events\WishlistItemUserHasChanged;
use App\Http\Requests\WishlistItem\WishlistItemChangeStateOfItemRequest;
use App\Http\Requests\WishlistItem\WishlistItemContributionRequest;
use App\Http\Requests\WishlistItem\WishlistItemDeleteRequest;
use App\Http\Requests\WishlistItem\WishlistItemStoreRequest;
use App\Http\Requests\WishlistItem\WishlistItemUpdateRequest;
use App\Http\Requests\WishlistItem\WishlistLinkItemToUserRequest;
use App\Http\Requests\WishlistItem\WishlistUnlinkItemToUserRequest;
use App\Models\Group;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WishlistItemController extends Controller
{
    public function show(WishlistItem $wishlistItem)
    {
        return Inertia::render('WishlistItem/Show', [
            'wishlistItem' => $wishlistItem->load([
                'group' => fn ($query) => $query->select('id', 'name')
            ])->toDisplayData(),
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
        $currentUser = Auth::user();

        if (WishlistItemTypeEnum::tryFrom($wishlistItem->type->value) === WishlistItemTypeEnum::GROUP_GIFT) {
            DB::transaction(function () use ($wishlistItem, $currentUser) {
                $group = Group::firstOrCreate(
                    ['name' => '#'.$wishlistItem->id.' - '.$wishlistItem->name], // Search criteria
                    [
                        'description' => 'Cadeau de groupe pour: '.$wishlistItem->name.' / Group for gift: '.$wishlistItem->name
                    ]
                );

                // Add the current user to the group members
                // syncWithoutDetaching is good to prevent duplicate entries if this logic is hit multiple times.
                $group->members()->syncWithoutDetaching([$currentUser->id => ['contribution_amount' => 0]]);
                $wishlistItem->update([
                    'in_shopping_list' => true,
                    'user_id' => $currentUser->id,//last user that added the gift to their shopping list
                    'group_id' => $group->id,
                ]);
                if($group->members()->count() > 1) {
                    event(new GroupMemberJoined($group, $currentUser));
                }
            });

        } else {
            $wishlistItem->update([
                'in_shopping_list' => true,
                'user_id' => $currentUser->id,
            ]);
        }

        WishlistItemUserHasChanged::dispatch($wishlistItem);
        return [
            'status' => 200,
            'message' => __('messages.wishlist_item_linked_successfully'),
        ];
    }

    public function unlinkItemToUser(WishlistUnlinkItemToUserRequest $request, WishlistItem $wishlistItem): array
    {
        if ($wishlistItem->group && WishlistItemTypeEnum::tryFrom($wishlistItem->type->value) === WishlistItemTypeEnum::GROUP_GIFT) {
            $currentUser = Auth::user();
            $wishlistItem->group->members()->detach($currentUser->id);

            if($wishlistItem->group->members()->count() == 0) {
                $wishlistItem->group->delete();
                $wishlistItem->update([
                    'in_shopping_list' => false,
                    'user_id' => null,
                ]);
            } else {
                event(new GroupMemberLeft($wishlistItem->group, $currentUser));
            }
        } else {
            $wishlistItem->update([
                'in_shopping_list' => false,
                'user_id' => null,
            ]);
        }
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
        ];
    }

    public function getCurrentDataFromPage(Request $request): array
    {
        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');
        $nameFilter = $request->get('name');
        $priceGreaterThanFilter = $request->get('price_greater_than');
        $priceLesserThanFilter = $request->get('price_lesser_than');

        return [
            'pagination' => WishlistItem::query()
                ->where('wishlist_id', $request->wishlist_id) // Existing filter
                ->when($nameFilter, function ($query, $name) {
                    return $query->where('name', 'like', "%{$name}%");
                })
                ->when($priceGreaterThanFilter, function ($query, $price) {
                    return $query->where('price', '>=', $price);
                })
                ->when($priceLesserThanFilter, function ($query, $price) {
                    return $query->where('price', '<=', $price);
                })
                ->orderBy($sortBy, $sortDirection)
                ->paginate($request->perPage, ['*'], 'page', $request->page)
        ];
    }

    public function updateContribution(WishlistItemContributionRequest $request, WishlistItem $wishlistItem): JsonResponse
    {
        $user = Auth::user();

        $validatedData = $request->validated();

        $updateCount = $wishlistItem->group->members()->updateExistingPivot($user->id, [
            'contribution_amount' => $validatedData['contribution_amount']
        ]);

        if ($updateCount > 0) {
            return response()->json([
                'message' => __('messages.contribution_updated_successfully'),
                'contributed_amount' => $wishlistItem->contributedAmount
            ]);
        }

        return response()->json(['message' => __('messages.contribution_update_failed_or_no_change')], 400);
    }
}
