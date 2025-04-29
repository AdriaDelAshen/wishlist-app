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
        return [
            'pagination' => WishlistItem::query()
                ->where('user_id', Auth::user()->id)
                ->with('wishlist.user')
                ->paginate($request->perPage, ['*'], 'page', $request->page)
        ];
    }
}
