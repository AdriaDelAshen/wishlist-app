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

        $query = WishlistItem::query()
            ->where('wishlist_items.user_id', Auth::user()->id)
            ->join('wishlists', 'wishlist_items.wishlist_id', '=', 'wishlists.id')
            ->join('users', 'wishlists.user_id', '=', 'users.id')
            ->with('wishlist.user') // still eager load for response
            ->select('wishlist_items.*'); // important to avoid column ambiguity

        // Apply sorting conditionally
        if ($sortBy === 'wishlist_user_name') {
            $query->orderBy('users.name', $sortDirection);
        } elseif($sortBy === 'wishlist_name') {
            $query->orderBy('wishlists.name', $sortDirection);
        } else {
            $query->orderBy("wishlist_items.$sortBy", $sortDirection);
        }

        return [
            'pagination' => $query->paginate($perPage, ['*'], 'page', $page),
        ];
    }
}
