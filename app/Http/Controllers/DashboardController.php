<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(Wishlist $wishlist)
    {
        return Inertia::render('Wishlist/Show', [
            'wishlistItems' => $wishlist->wishlistItems->load('wishlist'),
        ]);
    }
}
