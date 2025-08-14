<?php

namespace App\Http\Controllers;

use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show()
    {
        return Inertia::render('Dashboard');
    }

    public function getCurrentDataFromPage(Request $request): array
    {
        $sortBy = $request->get('sortBy', 'id');
        $sortDirection = $request->get('sortDirection', 'asc');
        $perPage = $request->get('perPage', 5);
        $page = $request->get('page', 1);
        $currentUser = Auth::user();

        $query = WishlistItem::query()
            ->where(function ($query) use ($currentUser) {
                $query->where('wishlist_items.user_id', $currentUser->id)//usually single person gift
                ->orWhereHas('group.members', function ($query) use ($currentUser) {
                    $query->where('users.id', $currentUser->id);
                });
            })
            ->distinct()
            ->join('wishlists', 'wishlist_items.wishlist_id', '=', 'wishlists.id')
            ->join('users as wishlist_owner', 'wishlists.user_id', '=', 'wishlist_owner.id')
            ->with('wishlist.user')
            ->select('wishlist_items.*');

//      Define valid sortable columns
        $validWishlistItemSortColumns = ['id', 'name', 'price', 'priority', 'type', 'created_at', 'updated_at', 'is_bought'];

        // Apply sorting conditionally
        if ($sortBy === 'wishlist_user_name') {
            $query->orderBy('wishlist_owner.name', $sortDirection);
        } elseif($sortBy === 'wishlist_name') {
            $query->orderBy('wishlists.name', $sortDirection);
        } elseif (in_array($sortBy, $validWishlistItemSortColumns)) {
            $query->orderBy("wishlist_items.$sortBy", $sortDirection);
        } else {
            // Default sort
            $query->orderBy("wishlist_items.priority", $sortDirection);
        }

        return [
            'pagination' => $query->paginate($perPage, ['*'], 'page', $page),
        ];
    }
}
